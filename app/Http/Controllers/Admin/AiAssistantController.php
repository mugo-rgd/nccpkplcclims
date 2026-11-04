<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\Ai\AiMarkdownService;
use App\Services\Ai\OpenAiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AiAssistantController extends Controller
{
    public function __construct(
        private readonly OpenAiAssistantService $assistantService,
        private readonly AiMarkdownService $markdownService
    ) {
    }

    /**
     * Display the AI Assistant interface.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        abort_unless($user, 401);

        $conversations = AiConversation::query()
            ->ownedBy($user->id)
            ->withCount('messages')
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $activeConversation = null;

        if ($request->filled('conversation')) {
            $activeConversation = AiConversation::query()
                ->ownedBy($user->id)
                ->with([
                    'messages' => function ($query) {
                        $query
                            ->orderBy('created_at')
                            ->orderBy('id');
                    },
                ])
                ->findOrFail(
                    (int) $request->input('conversation')
                );
        }

        return view(
            'admin.ai-assistant.index',
            compact(
                'conversations',
                'activeConversation'
            )
        );
    }

    /**
     * Create a new blank conversation.
     */
    public function store(Request $request): RedirectResponse
    {
        $conversation = AiConversation::query()->create([
            'user_id' => Auth::id(),
            'title' => 'New Conversation',
            'status' => 'active',
        ]);

        return redirect()->route(
            'ai-assistant.index',
            [
                'conversation' => $conversation->id,
            ]
        );
    }

    /**
     * Return one conversation and its messages.
     */
    public function show(
        AiConversation $conversation
    ): JsonResponse {
        $this->authorizeConversation($conversation);

        $conversation->load([
            'messages' => function ($query) {
                $query
                    ->orderBy('created_at')
                    ->orderBy('id');
            },
        ]);

        return response()->json([
            'success' => true,

            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'status' => $conversation->status,

                'created_at' =>
                    $conversation->created_at
                        ?->toIso8601String(),

                'updated_at' =>
                    $conversation->updated_at
                        ?->toIso8601String(),
            ],

            'messages' => $conversation->messages
                ->map(function (AiMessage $message) {
                    return [
                        'id' => $message->id,
                        'role' => $message->role,
                        'content' => $message->content,

                        'rendered_html' =>
                            $message->role === 'assistant'
                                ? $this->markdownService->render(
                                    $message->content
                                )
                                : nl2br(
                                    e($message->content)
                                ),

                        'created_at' =>
                            $message->created_at
                                ?->toIso8601String(),
                    ];
                })
                ->values(),
        ]);
    }

    /**
     * Send a message to an existing conversation.
     */
    public function send(
        Request $request,
        AiConversation $conversation
    ): JsonResponse {
        $this->authorizeConversation($conversation);

        $validated = $request->validate(
            [
                'message' => [
                    'required',
                    'string',
                    'min:2',
                    'max:10000',
                ],
            ],
            [
                'message.required' =>
                    'Enter a question for the AI assistant.',

                'message.min' =>
                    'The question must contain at least two characters.',

                'message.max' =>
                    'The question may not exceed 10,000 characters.',
            ]
        );

        try {
            $result = $this->assistantService->send(
                $conversation,
                trim($validated['message'])
            );

            $conversation->refresh();

            $answer = trim(
                (string) ($result['answer'] ?? '')
            );

            if ($answer === '') {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'The AI assistant returned an empty response.',
                ], 500);
            }

            return response()->json([
                'success' => true,

                'conversation' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title,

                    'updated_at' =>
                        $conversation->updated_at
                            ?->toIso8601String(),
                ],

                'message' => [
                    'role' => 'assistant',
                    'content' => $answer,

                    'rendered_html' =>
                        $this->markdownService->render(
                            $answer
                        ),
                ],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'The AI assistant could not process your request. Please try again.',
            ], 500);
        }
    }

    /**
     * Rename a conversation.
     */
    public function update(
        Request $request,
        AiConversation $conversation
    ): JsonResponse {
        $this->authorizeConversation($conversation);

        $validated = $request->validate(
            [
                'title' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ],
            [
                'title.required' =>
                    'Enter a conversation title.',

                'title.max' =>
                    'The conversation title may not exceed 100 characters.',
            ]
        );

        $conversation->update([
            'title' => Str::limit(
                trim($validated['title']),
                100,
                ''
            ),
        ]);

        return response()->json([
            'success' => true,

            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,

                'updated_at' =>
                    $conversation->updated_at
                        ?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Delete one conversation and its related records.
     */
    public function destroy(
        AiConversation $conversation
    ): JsonResponse {
        $this->authorizeConversation($conversation);

        DB::transaction(
            function () use ($conversation) {
                /*
                 * These deletes are only required if the database
                 * foreign keys do not already use cascadeOnDelete().
                 */
                $conversation->messages()->delete();
                $conversation->toolLogs()->delete();
                $conversation->delete();
            }
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Conversation deleted successfully.',
        ]);
    }

    /**
     * Create a conversation and send its first message.
     */
    public function startAndSend(
        Request $request
    ): JsonResponse {
        $validated = $request->validate(
            [
                'message' => [
                    'required',
                    'string',
                    'min:2',
                    'max:10000',
                ],
            ],
            [
                'message.required' =>
                    'Enter a question for the AI assistant.',

                'message.min' =>
                    'The question must contain at least two characters.',

                'message.max' =>
                    'The question may not exceed 10,000 characters.',
            ]
        );

        $message = trim($validated['message']);

        $conversation = AiConversation::query()->create([
            'user_id' => Auth::id(),

            'title' => Str::limit(
                $message,
                60,
                ''
            ),

            'status' => 'active',
        ]);

        try {
            $result = $this->assistantService->send(
                $conversation,
                $message
            );

            $conversation->refresh();

            $answer = trim(
                (string) ($result['answer'] ?? '')
            );

            if ($answer === '') {
                return response()->json([
                    'success' => false,
                    'conversation_id' =>
                        $conversation->id,

                    'message' =>
                        'The AI assistant returned an empty response.',
                ], 500);
            }

            return response()->json([
                'success' => true,

                'conversation' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title,

                    'updated_at' =>
                        $conversation->updated_at
                            ?->toIso8601String(),
                ],

                'message' => [
                    'role' => 'assistant',
                    'content' => $answer,

                    'rendered_html' =>
                        $this->markdownService->render(
                            $answer
                        ),
                ],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'conversation_id' =>
                    $conversation->id,

                'message' =>
                    'The AI assistant could not process your request. Please try again.',
            ], 500);
        }
    }

    /**
     * Ensure users only access their own conversations.
     */
    private function authorizeConversation(
        AiConversation $conversation
    ): void {
        $user = Auth::user();

        abort_unless(
            $user
            && $conversation->belongsToUser($user),
            403,
            'You are not authorized to access this conversation.'
        );
    }
}
