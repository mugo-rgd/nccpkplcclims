<?php

namespace App\Http\Controllers;

use App\Models\SystemDocument;
use App\Models\SystemDocumentVersion;
use App\Models\SystemDocumentAcknowledgement;
use App\Models\SystemDocumentAudience;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Models\SystemDocumentReview;

class SystemDocumentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Document Library
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = SystemDocument::query()
            ->with([
                'owner',
                'uploadedBy',
                'approvedBy',
            ]);
/*
|--------------------------------------------------------------------------
| Document visibility
|--------------------------------------------------------------------------
|
| Normal users:
|   - approved
|   - active
|
| Document workflow users such as Lab Manager:
|   - can see pending documents so they can approve/review/manage workflow
|
| Restricted documents:
|   - only users with view_restricted_system_documents
|--------------------------------------------------------------------------
*/

if (!$this->canAccessDocumentWorkflow()) {

    $query->where(
        'status',
        'approved'
    )
    ->where(
        'is_active',
        true
    );
}


/*
|--------------------------------------------------------------------------
| Restricted Documents
|--------------------------------------------------------------------------
*/

if (!$this->canAccessRestrictedDocuments()) {

    $query->where(
        'classification',
        '!=',
        'restricted'
    );
}
        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere(
                        'document_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'category',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->category
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Classification
        |--------------------------------------------------------------------------
        */

        if ($request->filled('classification')) {
            $query->where(
                'classification',
                $request->classification
            );
        }

        if ($request->filled('review_status')) {

    match ($request->review_status) {

        'due' =>
            $query
                ->whereNotNull('review_date')
                ->whereBetween(
                    'review_date',
                    [
                        now()->startOfDay(),
                        now()
                            ->addDays(30)
                            ->endOfDay()
                    ]
                ),

        'overdue' =>
            $query
                ->whereNotNull('review_date')
                ->where(
                    'review_date',
                    '<',
                    now()->startOfDay()
                ),

        'current' =>
            $query->where(
                function ($q) {

                    $q->whereNull('review_date')
                        ->orWhere(
                            'review_date',
                            '>',
                            now()
                                ->addDays(30)
                                ->endOfDay()
                        );
                }
            ),

        default => null,
    };
}

        $documents = $query
            ->orderBy('sort_order')
            ->orderBy('category')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $categories = SystemDocument::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

            $stats = [
    'total' =>
        SystemDocument::query()
            ->where('is_active', true)
            ->count(),

    'approved' =>
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->count(),

    'pending' =>
        SystemDocument::query()
            ->where(
                'status',
                'pending_approval'
            )
            ->count(),

    'acknowledgement_required' =>
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(
                'requires_acknowledgement',
                true
            )
            ->count(),

    'review_due' =>
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('review_date')
            ->whereBetween(
                'review_date',
                [
                    now()->startOfDay(),
                    now()
                        ->addDays(30)
                        ->endOfDay()
                ]
            )
            ->count(),

    'review_overdue' =>
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('review_date')
            ->where(
                'review_date',
                '<',
                now()->startOfDay()
            )
            ->count(),
            'revision_required' =>
    SystemDocument::query()
        ->where(
            'review_state',
            'revision_required'
        )
        ->where('is_active', true)
        ->count(),
];

        return view(
    'system-documents.index',
    compact(
        'documents',
        'categories',
        'stats'
    )
);
    }

    public function dashboard()
{
    abort_unless(
        Auth::user()->can(
            'manage_system_documents'
        ),
        403
    );

    $totalDocuments =
        SystemDocument::query()
            ->count();

    $approvedDocuments =
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->count();

    $pendingApproval =
        SystemDocument::query()
            ->where(
                'status',
                'pending_approval'
            )
            ->count();

    $archivedDocuments =
        SystemDocument::query()
            ->where('status', 'archived')
            ->count();

    $obsoleteDocuments =
        SystemDocument::query()
            ->where('status', 'obsolete')
            ->count();

    $reviewDue =
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('review_date')
            ->whereBetween(
                'review_date',
                [
                    now()->startOfDay(),
                    now()
                        ->addDays(30)
                        ->endOfDay()
                ]
            )
            ->count();

    $reviewOverdue =
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('review_date')
            ->where(
                'review_date',
                '<',
                now()->startOfDay()
            )
            ->count();

    $revisionRequired =
        SystemDocument::query()
            ->where(
                'review_state',
                'revision_required'
            )
            ->where('is_active', true)
            ->count();


    /*
    |--------------------------------------------------------------------------
    | Current-version acknowledgement compliance
    |--------------------------------------------------------------------------
    */

    $ackDocuments =
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(
                'requires_acknowledgement',
                true
            )
            ->with('audiences')
            ->get();


    $requiredAcknowledgements = 0;
    $completedAcknowledgements = 0;


    foreach ($ackDocuments as $document) {

        $expectedUserIds =
            $this->getExpectedAudienceUserIds(
                $document
            );

        $requiredAcknowledgements +=
            $expectedUserIds->count();


        if ($expectedUserIds->isNotEmpty()) {

            $completedAcknowledgements +=
                SystemDocumentAcknowledgement::query()
                    ->where(
                        'system_document_id',
                        $document->id
                    )
                    ->where(
                        'document_version',
                        $document->current_version
                    )
                    ->whereIn(
                        'user_id',
                        $expectedUserIds
                    )
                    ->count();
        }
    }


    $outstandingAcknowledgements =
        max(
            0,
            $requiredAcknowledgements
            - $completedAcknowledgements
        );


    $acknowledgementCompliance =
        $requiredAcknowledgements > 0
            ? round(
                (
                    $completedAcknowledgements
                    / $requiredAcknowledgements
                ) * 100,
                1
            )
            : 100;


    /*
    |--------------------------------------------------------------------------
    | Documents requiring management attention
    |--------------------------------------------------------------------------
    */

    $documentsDueForReview =
        SystemDocument::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('review_date')
            ->where(
                'review_date',
                '<=',
                now()
                    ->addDays(30)
                    ->endOfDay()
            )
            ->orderBy('review_date')
            ->limit(10)
            ->get();


    $recentReviews =
        SystemDocumentReview::query()
            ->with([
                'document',
                'reviewedBy',
            ])
            ->latest('reviewed_at')
            ->limit(10)
            ->get();


    $recentDocuments =
        SystemDocument::query()
            ->with('uploadedBy')
            ->latest()
            ->limit(10)
            ->get();


    return view(
        'system-documents.dashboard',
        compact(
            'totalDocuments',
            'approvedDocuments',
            'pendingApproval',
            'archivedDocuments',
            'obsoleteDocuments',
            'reviewDue',
            'reviewOverdue',
            'revisionRequired',
            'requiredAcknowledgements',
            'completedAcknowledgements',
            'outstandingAcknowledgements',
            'acknowledgementCompliance',
            'documentsDueForReview',
            'recentReviews',
            'recentDocuments'
        )
    );
}

private function getExpectedAudienceUserIds(
    SystemDocument $document
) {
    $document->loadMissing(
        'audiences'
    );

    $roleIds =
        $document->audiences
            ->whereNotNull('role_id')
            ->pluck('role_id')
            ->unique()
            ->values();

    $directUserIds =
        $document->audiences
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();


    $roleUserIds = collect();


    if ($roleIds->isNotEmpty()) {

        $roleUserIds =
            DB::table('model_has_roles')
                ->whereIn(
                    'role_id',
                    $roleIds
                )
                ->where(
                    'model_type',
                    User::class
                )
                ->pluck('model_id');
    }


    return $directUserIds
        ->merge($roleUserIds)
        ->map(
            fn ($id) => (int) $id
        )
        ->unique()
        ->values();
}

    /*
    |--------------------------------------------------------------------------
    | Create Form
    |--------------------------------------------------------------------------
    */

   public function create()
{
    abort_unless(
        Auth::user()->can(
            'upload_system_documents'
        ),
        403
    );

    $roles = Role::query()
        ->where('guard_name', 'web')
        ->orderBy('name')
        ->get();

    $users = User::query()
        ->orderBy('name')
        ->get();

    return view(
        'system-documents.create',
        compact(
            'roles',
            'users'
        )
    );
}

    /*
    |--------------------------------------------------------------------------
    | Store Document
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        abort_unless(
            Auth::user()->can('upload_system_documents'),
            403
        );

        $validated = $request->validate([
            'document_code' => [
                'required',
                'string',
                'max:100',
                'unique:system_documents,document_code',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'category' => [
                'required',
                'string',
                'max:100',
            ],

            'classification' => [
                'required',
                Rule::in([
                    'general',
                    'internal',
                    'confidential',
                    'restricted',
                ]),
            ],

            'version' => [
                'required',
                'string',
                'max:30',
            ],

            'file' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv',
            ],

            'effective_date' => [
                'nullable',
                'date',
            ],

            'review_date' => [
                'nullable',
                'date',
                'after_or_equal:effective_date',
            ],

            'expiry_date' => [
                'nullable',
                'date',
                'after_or_equal:effective_date',
            ],

            'requires_acknowledgement' => [
                'nullable',
                'boolean',
            ],

            'is_downloadable' => [
                'nullable',
                'boolean',
            ],

            'required_roles' => [
    'nullable',
    'array',
],

'required_roles.*' => [
    'integer',
    'exists:roles,id',
],

'required_users' => [
    'nullable',
    'array',
],

'required_users.*' => [
    'integer',
    'exists:users,id',
],
        ]);

        $file = $request->file('file');

        $categoryFolder = Str::slug(
            $validated['category']
        );

        $storedFilename =
            Str::uuid()->toString()
            . '.'
            . $file->getClientOriginalExtension();

        /*
        |--------------------------------------------------------------------------
        | Laravel local disk = private storage.
        |--------------------------------------------------------------------------
        |
        | We deliberately do NOT store this under public/.
        |
        */

        $filePath = $file->storeAs(
            "system-documents/{$categoryFolder}",
            $storedFilename,
            'local'
        );

        try {
            DB::beginTransaction();

            $document = SystemDocument::create([
                'document_code' =>
                    strtoupper(
                        trim(
                            $validated['document_code']
                        )
                    ),

                'title' =>
                    $validated['title'],

                'slug' =>
                    $this->generateUniqueSlug(
                        $validated['title']
                    ),

                'description' =>
                    $validated['description'] ?? null,

                'category' =>
                    $validated['category'],

                'classification' =>
                    $validated['classification'],

                'current_version' =>
                    $validated['version'],

                'file_path' =>
                    $filePath,

                'original_filename' =>
                    $file->getClientOriginalName(),

                'stored_filename' =>
                    $storedFilename,

                'mime_type' =>
                    $file->getMimeType(),

                'file_size' =>
                    $file->getSize(),

                'file_extension' =>
                    strtolower(
                        $file->getClientOriginalExtension()
                    ),

                /*
                 * New document enters approval workflow.
                 */
                'status' =>
                    'pending_approval',

                'effective_date' =>
                    $validated['effective_date'] ?? null,

                'review_date' =>
                    $validated['review_date'] ?? null,

                'expiry_date' =>
                    $validated['expiry_date'] ?? null,

                'owner_id' =>
                    Auth::id(),

                'uploaded_by' =>
                    Auth::id(),

                'requires_acknowledgement' =>
                    $request->boolean(
                        'requires_acknowledgement'
                    ),

                'is_downloadable' =>
                    $request->has('is_downloadable')
                        ? $request->boolean(
                            'is_downloadable'
                        )
                        : true,

                'is_active' =>
                    true,
            ]);
            
            $this->syncDocumentAudiences(
    $document,
    $request
);
            /*
            |--------------------------------------------------------------------------
            | Initial Version
            |--------------------------------------------------------------------------
            */

            SystemDocumentVersion::create([
                'system_document_id' =>
                    $document->id,

                'version' =>
                    $validated['version'],

                'change_summary' =>
                    'Initial document upload.',

                'file_path' =>
                    $filePath,

                'original_filename' =>
                    $file->getClientOriginalName(),

                'stored_filename' =>
                    $storedFilename,

                'mime_type' =>
                    $file->getMimeType(),

                'file_size' =>
                    $file->getSize(),

                'file_extension' =>
                    strtolower(
                        $file->getClientOriginalExtension()
                    ),

                'status' =>
                    'pending_approval',

                'uploaded_by' =>
                    Auth::id(),

                'effective_date' =>
                    $validated['effective_date'] ?? null,

                'review_date' =>
                    $validated['review_date'] ?? null,

                'is_current' =>
                    true,
            ]);

            DB::commit();

            return redirect()
                ->route('system-documents.index')
                ->with(
                    'success',
                    'Document uploaded successfully and is awaiting approval.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            if (
                $filePath
                && Storage::disk('local')
                    ->exists($filePath)
            ) {
                Storage::disk('local')
                    ->delete($filePath);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The document could not be uploaded.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show Document Details
    |--------------------------------------------------------------------------
    */
    public function show(SystemDocument $document)
    {
        abort_unless(
            Auth::user()->can('view_system_documents'),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Document visibility
        |--------------------------------------------------------------------------
        |
        | Ordinary readers may only open approved, active documents.
        | Workflow users (for example Lab Managers with approval/review
        | permissions) may open pending documents so they can act on them.
        |
        */

        if (
            !$this->canAccessDocumentWorkflow()
            && (
                !$document->is_active
                || $document->status !== 'approved'
            )
        ) {
            abort(404);
        }

        $this->enforceRestrictedDocumentAccess($document);

        $document->increment('view_count');

        $document->load([
            'owner',
            'uploadedBy',
            'approvedBy',
            'versions.uploadedBy',
            'versions.approvedBy',
            'audiences',
        ]);

        $acknowledgementRequiredForUser = false;
        $acknowledged = false;

        if ($document->requires_acknowledgement) {
            $acknowledgementRequiredForUser =
                $this->userIsRequiredAudience(
                    $document,
                    Auth::user()
                );

            if ($acknowledgementRequiredForUser) {
                $acknowledged =
                    $document->hasBeenAcknowledgedBy(
                        Auth::user()
                    );
            }
        }

        return view(
            'system-documents.show',
            compact(
                'document',
                'acknowledged',
                'acknowledgementRequiredForUser'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Secure Download
    |--------------------------------------------------------------------------
    */

    public function download(SystemDocument $document)
    {
        abort_unless(
            Auth::user()->can('download_system_documents'),
            403
        );

        abort_unless(
            $document->is_active,
            404
        );

        abort_unless(
            $document->status === 'approved',
            404
        );

        $this->enforceRestrictedDocumentAccess($document);

        abort_unless(
            $document->is_downloadable,
            403,
            'This document is view-only.'
        );

        abort_unless(
            Storage::disk('local')
                ->exists($document->file_path),
            404,
            'Document file not found.'
        );

        $document->increment(
            'download_count'
        );

        return Storage::disk('local')
            ->download(
                $document->file_path,
                $document->original_filename
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */
    public function approve(
        Request $request,
        SystemDocument $document
    ) {
        abort_unless(
            Auth::user()->can('approve_system_documents'),
            403
        );

        $this->enforceRestrictedDocumentAccess($document);

        if ($document->status !== 'pending_approval') {
            return back()->with(
                'error',
                'Only documents awaiting approval can be approved.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Segregation of duties
        |--------------------------------------------------------------------------
        |
        | The user who uploaded the current document/version must not approve
        | their own work. This allows System Admin and Lab Manager to approve
        | one another's uploads while preventing self-approval.
        |
        */

        $currentVersion = $document->versions()
            ->where('is_current', true)
            ->first();

        $currentUploaderId =
            $currentVersion?->uploaded_by
            ?? $document->uploaded_by;

        if (
            $currentUploaderId
            && (int) $currentUploaderId === (int) Auth::id()
        ) {
            return back()->with(
                'error',
                'You cannot approve a document or version that you uploaded.'
            );
        }

        $validated = $request->validate([
            'approval_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use (
            $document,
            $validated
        ) {
            $document->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' =>
                    $validated['approval_notes'] ?? null,
                'is_active' => true,
            ]);

            SystemDocumentVersion::query()
                ->where(
                    'system_document_id',
                    $document->id
                )
                ->where('is_current', true)
                ->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
        });

        return back()->with(
            'success',
            'Document approved successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Acknowledge
    |--------------------------------------------------------------------------
    */
    public function acknowledge(
        Request $request,
        SystemDocument $document
    ) {
        abort_unless(
            Auth::user()->can('view_system_documents'),
            403
        );

        abort_unless(
            $document->status === 'approved'
            && $document->is_active,
            404
        );

        $this->enforceRestrictedDocumentAccess($document);

        abort_unless(
            $document->requires_acknowledgement,
            400,
            'This document does not require acknowledgement.'
        );

        abort_unless(
            $this->userIsRequiredAudience(
                $document,
                Auth::user()
            ),
            403,
            'This document does not require your acknowledgement.'
        );

        SystemDocumentAcknowledgement::updateOrCreate(
            [
                'system_document_id' =>
                    $document->id,

                'user_id' =>
                    Auth::id(),

                'document_version' =>
                    $document->current_version,
            ],
            [
                'acknowledged_at' =>
                    now(),

                'ip_address' =>
                    $request->ip(),

                'user_agent' =>
                    Str::limit(
                        (string) $request->userAgent(),
                        1000,
                        ''
                    ),
            ]
        );

        return back()->with(
            'success',
            'Document acknowledgement recorded successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Archive
    |--------------------------------------------------------------------------
    */

    public function archive(
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can(
            'archive_system_documents'
        ),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);

    if ($document->status === 'archived') {
        return back()->with(
            'error',
            'This document is already archived.'
        );
    }

    $document->update([
        'status' =>
            'archived',

        'is_active' =>
            false,
    ]);

    return redirect()
        ->route(
            'system-documents.index'
        )
        ->with(
            'success',
            'Document archived successfully.'
        );
}

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        SystemDocument $document
    ) {
        abort_unless(
            Auth::user()->can(
                'delete_system_documents'
            ),
            403
        );

        /*
         * Soft-delete record.
         *
         * We deliberately do NOT immediately delete the physical
         * document because controlled documents may still be required
         * for audit/history.
         */

        $document->delete();

        return redirect()
            ->route('system-documents.index')
            ->with(
                'success',
                'Document removed from the active library.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(
        string $title
    ): string {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'document';
        }

        $slug = $base;
        $counter = 1;

        while (
            SystemDocument::withTrashed()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug =
                $base
                . '-'
                . $counter++;

        }

        return $slug;
    }

    /*
|--------------------------------------------------------------------------
| Edit Document
|--------------------------------------------------------------------------
*/

public function edit(
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can(
            'edit_system_documents'
        ),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);

    $document->load([
        'owner',
        'uploadedBy',
        'approvedBy',
        'audiences',
    ]);

    $roles = Role::query()
        ->where('guard_name', 'web')
        ->orderBy('name')
        ->get();

    $users = User::query()
        ->orderBy('name')
        ->get();

    $selectedRoles =
        $document->audiences
            ->whereNotNull('role_id')
            ->pluck('role_id')
            ->map(fn ($id) => (int) $id)
            ->all();

    $selectedUsers =
        $document->audiences
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();


    return view(
        'system-documents.edit',
        compact(
            'document',
            'roles',
            'users',
            'selectedRoles',
            'selectedUsers'
        )
    );
}

/*
|--------------------------------------------------------------------------
| Update Document Metadata
|--------------------------------------------------------------------------
*/

public function update(
    Request $request,
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can('edit_system_documents'),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);

    $validated = $request->validate([
        'document_code' => [
            'required',
            'string',
            'max:100',

            Rule::unique(
                'system_documents',
                'document_code'
            )->ignore($document->id),
        ],

        'title' => [
            'required',
            'string',
            'max:255',
        ],

        'description' => [
            'nullable',
            'string',
        ],

        'category' => [
            'required',
            'string',
            'max:100',
        ],

        'classification' => [
            'required',
            Rule::in([
                'general',
                'internal',
                'confidential',
                'restricted',
            ]),
        ],

        'effective_date' => [
            'nullable',
            'date',
        ],

        'review_date' => [
            'nullable',
            'date',
            'after_or_equal:effective_date',
        ],

        'expiry_date' => [
            'nullable',
            'date',
            'after_or_equal:effective_date',
        ],

        'requires_acknowledgement' => [
            'nullable',
            'boolean',
        ],

        'is_downloadable' => [
            'nullable',
            'boolean',
        ],

        'sort_order' => [
            'nullable',
            'integer',
            'min:0',
        ],
        'required_roles' => [
    'nullable',
    'array',
],

'required_roles.*' => [
    'integer',
    'exists:roles,id',
],

'required_users' => [
    'nullable',
    'array',
],

'required_users.*' => [
    'integer',
    'exists:users,id',
],
    ]);

    $document->update([
        'document_code' => strtoupper(
            trim($validated['document_code'])
        ),

        'title' =>
            $validated['title'],

        'description' =>
            $validated['description'] ?? null,

        'category' =>
            $validated['category'],

        'classification' =>
            $validated['classification'],

        'effective_date' =>
            $validated['effective_date'] ?? null,

        'review_date' =>
            $validated['review_date'] ?? null,

        'expiry_date' =>
            $validated['expiry_date'] ?? null,

        'requires_acknowledgement' =>
            $request->boolean(
                'requires_acknowledgement'
            ),

        'is_downloadable' =>
            $request->boolean(
                'is_downloadable'
            ),

        'sort_order' =>
            $validated['sort_order'] ?? 0,
    ]);

    $this->syncDocumentAudiences(
    $document,
    $request
);

    return redirect()
        ->route(
            'system-documents.show',
            $document
        )
        ->with(
            'success',
            'Document information updated successfully.'
        );
}

/*
|--------------------------------------------------------------------------
| New Version Form
|--------------------------------------------------------------------------
*/

public function createVersion(
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can(
            'edit_system_documents'
        ),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);

    $document->load([
        'versions' => function ($query) {
            $query->orderByDesc('created_at');
        },
    ]);

    return view(
        'system-documents.versions.create',
        compact('document')
    );
}

/*
|--------------------------------------------------------------------------
| Store New Version
|--------------------------------------------------------------------------
*/

public function storeVersion(
    Request $request,
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can(
            'edit_system_documents'
        ),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);

    $validated = $request->validate([
        'version' => [
            'required',
            'string',
            'max:30',

            Rule::unique(
                'system_document_versions',
                'version'
            )->where(
                fn ($query) =>
                    $query->where(
                        'system_document_id',
                        $document->id
                    )
            ),
        ],

        'change_summary' => [
            'required',
            'string',
            'max:2000',
        ],

        'revision_notes' => [
            'nullable',
            'string',
        ],

        'file' => [
            'required',
            'file',
            'max:20480',
            'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv',
        ],

        'effective_date' => [
            'nullable',
            'date',
        ],

        'review_date' => [
            'nullable',
            'date',
            'after_or_equal:effective_date',
        ],
    ]);

    $file = $request->file('file');

    $categoryFolder = Str::slug(
        $document->category
    );

    $storedFilename =
        Str::uuid()->toString()
        . '.'
        . strtolower(
            $file->getClientOriginalExtension()
        );

    $filePath = $file->storeAs(
        "system-documents/{$categoryFolder}",
        $storedFilename,
        'local'
    );

    try {

        DB::transaction(function () use (
            $validated,
            $document,
            $file,
            $filePath,
            $storedFilename
        ) {

            /*
            |--------------------------------------------------------------------------
            | Old versions remain stored but stop being current.
            |--------------------------------------------------------------------------
            */

            SystemDocumentVersion::where(
                'system_document_id',
                $document->id
            )->update([
                'is_current' => false,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create the replacement version.
            |--------------------------------------------------------------------------
            */

            SystemDocumentVersion::create([
                'system_document_id' =>
                    $document->id,

                'version' =>
                    $validated['version'],

                'change_summary' =>
                    $validated['change_summary'],

                'revision_notes' =>
                    $validated['revision_notes']
                    ?? null,

                'file_path' =>
                    $filePath,

                'original_filename' =>
                    $file->getClientOriginalName(),

                'stored_filename' =>
                    $storedFilename,

                'mime_type' =>
                    $file->getMimeType(),

                'file_size' =>
                    $file->getSize(),

                'file_extension' =>
                    strtolower(
                        $file
                            ->getClientOriginalExtension()
                    ),

                /*
                 * Every new revision must be independently
                 * approved.
                 */
                'status' =>
                    'pending_approval',

                'uploaded_by' =>
                    Auth::id(),

                'effective_date' =>
                    $validated['effective_date']
                    ?? null,

                'review_date' =>
                    $validated['review_date']
                    ?? null,

                'is_current' =>
                    true,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Parent document now points to the newly uploaded version.
            |--------------------------------------------------------------------------
            */

            $document->update([
    'current_version' =>
        $validated['version'],

    'file_path' =>
        $filePath,

    'original_filename' =>
        $file->getClientOriginalName(),

    'stored_filename' =>
        $storedFilename,

    'mime_type' =>
        $file->getMimeType(),

    'file_size' =>
        $file->getSize(),

    'file_extension' =>
        strtolower(
            $file->getClientOriginalExtension()
        ),

    'status' =>
        'pending_approval',

    'approved_by' =>
        null,

    'approved_at' =>
        null,

    'approval_notes' =>
        null,

    'review_state' =>
        'current',

    'effective_date' =>
        $validated['effective_date']
        ?? $document->effective_date,

    'review_date' =>
        $validated['review_date']
        ?? $document->review_date,

    'is_active' =>
        true,
]);
        });

    } catch (\Throwable $e) {

        if (
            Storage::disk('local')
                ->exists($filePath)
        ) {
            Storage::disk('local')
                ->delete($filePath);
        }

        report($e);

        return back()
            ->withInput()
            ->with(
                'error',
                'The new document version could not be created.'
            );
    }

    return redirect()
        ->route(
            'system-documents.show',
            $document
        )
        ->with(
            'success',
            'Version '
            . $validated['version']
            . ' uploaded successfully and is awaiting approval.'
        );
}
/*
|--------------------------------------------------------------------------
| Download Historical Version
|--------------------------------------------------------------------------
*/

public function downloadVersion(
    SystemDocument $document,
    SystemDocumentVersion $version
) {
    abort_unless(
        Auth::user()->can(
            'download_system_documents'
        ),
        403
    );

    /*
     * Prevent somebody manipulating the URL and downloading
     * a version belonging to another document.
     */

    abort_unless(
        (int) $version->system_document_id
            === (int) $document->id,
        404
    );

        $this->enforceRestrictedDocumentAccess($document);

    abort_unless(
        Storage::disk('local')
            ->exists($version->file_path),
        404,
        'Document version file not found.'
    );

    return Storage::disk('local')
        ->download(
            $version->file_path,
            $version->original_filename
        );
}

/*
|--------------------------------------------------------------------------
| Restore Archived Document
|--------------------------------------------------------------------------
*/

public function restore(
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can(
            'archive_system_documents'
        ),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);

    if ($document->status !== 'archived') {
        return back()->with(
            'error',
            'Only archived documents can be restored.'
        );
    }

    /*
     * Restoring does not silently approve anything.
     *
     * If the current version was previously approved,
     * restore it as approved.
     */

    $currentVersion =
        $document->versions()
            ->where('is_current', true)
            ->first();

    $status =
        $currentVersion?->status
        === 'approved'
            ? 'approved'
            : 'pending_approval';

    $document->update([
        'status' =>
            $status,

        'is_active' =>
            true,
    ]);

    return back()->with(
        'success',
        'Document restored successfully.'
    );
}

/*
|--------------------------------------------------------------------------
| Acknowledgement Compliance
|--------------------------------------------------------------------------
*/
    public function acknowledgements(
        SystemDocument $document
    ) {
        /*
        |--------------------------------------------------------------------------
        | Compliance report authorization
        |--------------------------------------------------------------------------
        |
        | System Admins can manage the module. Lab Managers can approve
        | documents, so they are also allowed to view acknowledgement
        | compliance for documents they are permitted to access.
        |
        */

        abort_unless(
            Auth::user()->can('manage_system_documents')
            || Auth::user()->can('approve_system_documents'),
            403
        );

        $this->enforceRestrictedDocumentAccess($document);

        $document->load([
            'audiences.role',
            'audiences.user',
        ]);

        $expectedUserIds =
            $this->getExpectedAudienceUserIds(
                $document
            );

        $users = User::query()
            ->whereIn(
                'id',
                $expectedUserIds
            )
            ->orderBy('name')
            ->get();

        $acknowledgements =
            SystemDocumentAcknowledgement::query()
                ->where(
                    'system_document_id',
                    $document->id
                )
                ->where(
                    'document_version',
                    $document->current_version
                )
                ->whereIn(
                    'user_id',
                    $expectedUserIds
                )
                ->get()
                ->keyBy('user_id');

        $totalUsers = $users->count();

        $acknowledgedCount =
            $acknowledgements->count();

        $outstandingCount =
            max(
                0,
                $totalUsers - $acknowledgedCount
            );

        $compliancePercentage =
            $totalUsers > 0
                ? round(
                    (
                        $acknowledgedCount
                        / $totalUsers
                    ) * 100,
                    1
                )
                : 0;

        return view(
            'system-documents.acknowledgements',
            compact(
                'document',
                'users',
                'acknowledgements',
                'totalUsers',
                'acknowledgedCount',
                'outstandingCount',
                'compliancePercentage'
            )
        );
    }
private function syncDocumentAudiences(
    SystemDocument $document,
    Request $request
): void {

    /*
     * If acknowledgement is disabled, there is no
     * acknowledgement audience.
     */

    if (
        !$request->boolean(
            'requires_acknowledgement'
        )
    ) {
        $document->audiences()->delete();

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Start clean.
    |--------------------------------------------------------------------------
    */

    $document->audiences()->delete();


    /*
    |--------------------------------------------------------------------------
    | Role audiences
    |--------------------------------------------------------------------------
    */

    foreach (
        array_unique(
            $request->input(
                'required_roles',
                []
            )
        )
        as $roleId
    ) {

        SystemDocumentAudience::create([
            'system_document_id' =>
                $document->id,

            'role_id' =>
                $roleId,

            'user_id' =>
                null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Specific users
    |--------------------------------------------------------------------------
    */

    foreach (
        array_unique(
            $request->input(
                'required_users',
                []
            )
        )
        as $userId
    ) {

        SystemDocumentAudience::create([
            'system_document_id' =>
                $document->id,

            'role_id' =>
                null,

            'user_id' =>
                $userId,
        ]);
    }
}
private function userIsRequiredAudience(
    SystemDocument $document,
    User $user
): bool {

    $document->loadMissing(
        'audiences'
    );


    /*
     * Explicit user assignment.
     */

    if (
        $document->audiences
            ->where(
                'user_id',
                $user->id
            )
            ->isNotEmpty()
    ) {
        return true;
    }


    /*
     * Role assignment.
     */

    $roleIds =
        $document->audiences
            ->whereNotNull('role_id')
            ->pluck('role_id')
            ->map(fn ($id) => (int) $id)
            ->all();


    if (empty($roleIds)) {
        return false;
    }


    return DB::table('model_has_roles')
        ->where(
            'model_id',
            $user->id
        )
        ->where(
            'model_type',
            User::class
        )
        ->whereIn(
            'role_id',
            $roleIds
        )
        ->exists();
}
/*
|--------------------------------------------------------------------------
| Document Review Form
|--------------------------------------------------------------------------
*/

public function reviewForm(
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can(
            'review_system_documents'
        ),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);

    if (
        $document->status !== 'approved'
        || !$document->is_active
    ) {
        return back()->with(
            'error',
            'Only active approved documents can be reviewed.'
        );
    }

    $document->load([
        'owner',
        'approvedBy',
        'lastReviewedBy',
        'reviews.reviewedBy',
    ]);

    return view(
        'system-documents.review',
        compact('document')
    );
}
/*
|--------------------------------------------------------------------------
| Store Document Review
|--------------------------------------------------------------------------
*/

public function storeReview(
    Request $request,
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can(
            'review_system_documents'
        ),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);

    if (
        $document->status !== 'approved'
        || !$document->is_active
    ) {
        return back()->with(
            'error',
            'Only active approved documents can be reviewed.'
        );
    }


    $validated = $request->validate([
        'review_type' => [
            'required',
            Rule::in([
                'scheduled',
                'ad_hoc',
                'post_incident',
                'regulatory',
            ]),
        ],

        'outcome' => [
            'required',
            Rule::in([
                'no_change',
                'revision_required',
                'obsolete',
            ]),
        ],

        'review_notes' => [
            'required',
            'string',
            'max:5000',
        ],

        'recommended_changes' => [
            'nullable',
            'string',
            'max:5000',
        ],

        'next_review_date' => [
            'nullable',
            'date',
            'after:today',
        ],
    ]);


    /*
    |--------------------------------------------------------------------------
    | No-change reviews should normally have a future review date.
    |--------------------------------------------------------------------------
    */

    if (
        $validated['outcome']
            === 'no_change'
        && empty(
            $validated['next_review_date']
        )
    ) {
        return back()
            ->withInput()
            ->with(
                'error',
                'Please specify the next review date when the document remains current.'
            );
    }


    DB::transaction(
        function () use (
            $document,
            $validated
        ) {

            SystemDocumentReview::create([
                'system_document_id' =>
                    $document->id,

                'document_version' =>
                    $document->current_version,

                'review_type' =>
                    $validated['review_type'],

                'outcome' =>
                    $validated['outcome'],

                'review_notes' =>
                    $validated['review_notes'],

                'recommended_changes' =>
                    $validated[
                        'recommended_changes'
                    ] ?? null,

                'reviewed_by' =>
                    Auth::id(),

                'reviewed_at' =>
                    now(),

                'next_review_date' =>
                    $validated[
                        'next_review_date'
                    ] ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Outcome: no changes required
            |--------------------------------------------------------------------------
            */

            if (
                $validated['outcome']
                    === 'no_change'
            ) {

                $document->update([
                    'review_state' =>
                        'current',

                    'last_reviewed_at' =>
                        now(),

                    'last_reviewed_by' =>
                        Auth::id(),

                    'review_date' =>
                        $validated[
                            'next_review_date'
                        ],
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Outcome: revision required
            |--------------------------------------------------------------------------
            */

            if (
                $validated['outcome']
                    === 'revision_required'
            ) {

                $document->update([
                    'review_state' =>
                        'revision_required',

                    'last_reviewed_at' =>
                        now(),

                    'last_reviewed_by' =>
                        Auth::id(),

                    /*
                     * Keep review date as-is so the issue
                     * remains visible until revised.
                     */
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Outcome: obsolete
            |--------------------------------------------------------------------------
            */

            if (
                $validated['outcome']
                    === 'obsolete'
            ) {

                $document->update([
                    'status' =>
                        'obsolete',

                    'is_active' =>
                        false,

                    'review_state' =>
                        'current',

                    'last_reviewed_at' =>
                        now(),

                    'last_reviewed_by' =>
                        Auth::id(),
                ]);

                /*
                 * Preserve versions and files for audit.
                 * Nothing is physically deleted.
                 */
            }
        }
    );


    if (
        $validated['outcome']
            === 'revision_required'
    ) {
        return redirect()
            ->route(
                'system-documents.versions.create',
                $document
            )
            ->with(
                'success',
                'Review completed. A revised version is now required.'
            );
    }


    return redirect()
        ->route(
            'system-documents.show',
            $document
        )
        ->with(
            'success',
            'Document review recorded successfully.'
        );
}

/*
|--------------------------------------------------------------------------
| Review History
|--------------------------------------------------------------------------
*/

public function reviewHistory(
    SystemDocument $document
) {
    abort_unless(
        Auth::user()->can(
            'view_system_documents'
        ),
        403
    );

        $this->enforceRestrictedDocumentAccess($document);


    $document->load([
        'reviews.reviewedBy',
        'lastReviewedBy',
    ]);


    return view(
        'system-documents.reviews',
        compact('document')
    );
}
public function reports(Request $request)
{
    abort_unless(
        Auth::user()->can('manage_system_documents'),
        403
    );

    $query = SystemDocument::query()
        ->with([
            'owner',
            'uploadedBy',
            'approvedBy',
        ]);

    if ($request->filled('category')) {
        $query->where(
            'category',
            $request->category
        );
    }

    if ($request->filled('status')) {
        $query->where(
            'status',
            $request->status
        );
    }

    if ($request->filled('classification')) {
        $query->where(
            'classification',
            $request->classification
        );
    }

    if ($request->filled('review_status')) {

        match ($request->review_status) {

            'due' =>
                $query
                    ->whereNotNull('review_date')
                    ->whereBetween(
                        'review_date',
                        [
                            now()->startOfDay(),
                            now()
                                ->addDays(30)
                                ->endOfDay()
                        ]
                    ),

            'overdue' =>
                $query
                    ->whereNotNull('review_date')
                    ->where(
                        'review_date',
                        '<',
                        now()->startOfDay()
                    ),

            'revision_required' =>
                $query->where(
                    'review_state',
                    'revision_required'
                ),

            default => null,
        };
    }

    $documents = $query
        ->orderBy('category')
        ->orderBy('title')
        ->paginate(25)
        ->withQueryString();

    $categories = SystemDocument::query()
        ->select('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    return view(
        'system-documents.reports.index',
        compact(
            'documents',
            'categories'
        )
    );
}
public function exportRegister()
{
    abort_unless(
        Auth::user()->can(
            'manage_system_documents'
        ),
        403
    );

    $documents =
        SystemDocument::query()
            ->with([
                'owner',
                'uploadedBy',
                'approvedBy',
            ])
            ->orderBy('category')
            ->orderBy('title')
            ->get();


    $filename =
        'system-document-register-'
        . now()->format('Ymd-His')
        . '.csv';


    return response()->streamDownload(
        function () use ($documents) {

            $handle = fopen(
                'php://output',
                'w'
            );


            fputcsv($handle, [
                'Document Code',
                'Title',
                'Category',
                'Classification',
                'Version',
                'Status',
                'Review State',
                'Effective Date',
                'Review Date',
                'Expiry Date',
                'Owner',
                'Uploaded By',
                'Approved By',
                'Approved At',
                'Acknowledgement Required',
                'Downloads',
                'Views',
            ]);


            foreach ($documents as $document) {

                fputcsv($handle, [
                    $document->document_code,
                    $document->title,
                    $document->category,
                    $document->classification,
                    $document->current_version,
                    $document->status,
                    $document->review_state,
                    $document
                        ->effective_date
                        ?->format('Y-m-d'),
                    $document
                        ->review_date
                        ?->format('Y-m-d'),
                    $document
                        ->expiry_date
                        ?->format('Y-m-d'),
                    $document
                        ->owner
                        ?->name,
                    $document
                        ->uploadedBy
                        ?->name,
                    $document
                        ->approvedBy
                        ?->name,
                    $document
                        ->approved_at
                        ?->format(
                            'Y-m-d H:i:s'
                        ),
                    $document
                        ->requires_acknowledgement
                        ? 'Yes'
                        : 'No',
                    $document
                        ->download_count,
                    $document
                        ->view_count,
                ]);
            }


            fclose($handle);
        },
        $filename,
        [
            'Content-Type' =>
                'text/csv; charset=UTF-8',
        ]
    );
}
public function exportReviews()
{
    abort_unless(
        Auth::user()->can(
            'manage_system_documents'
        ),
        403
    );


    $reviews =
        SystemDocumentReview::query()
            ->with([
                'document',
                'reviewedBy',
            ])
            ->orderByDesc(
                'reviewed_at'
            )
            ->get();


    $filename =
        'system-document-review-history-'
        . now()->format('Ymd-His')
        . '.csv';


    return response()->streamDownload(
        function () use ($reviews) {

            $handle =
                fopen(
                    'php://output',
                    'w'
                );


            fputcsv($handle, [
                'Document Code',
                'Document',
                'Version',
                'Review Type',
                'Outcome',
                'Review Notes',
                'Recommended Changes',
                'Reviewed By',
                'Reviewed At',
                'Next Review Date',
            ]);


            foreach ($reviews as $review) {

                fputcsv($handle, [
                    $review
                        ->document
                        ?->document_code,

                    $review
                        ->document
                        ?->title,

                    $review
                        ->document_version,

                    $review
                        ->review_type,

                    $review
                        ->outcome,

                    $review
                        ->review_notes,

                    $review
                        ->recommended_changes,

                    $review
                        ->reviewedBy
                        ?->name,

                    $review
                        ->reviewed_at
                        ?->format(
                            'Y-m-d H:i:s'
                        ),

                    $review
                        ->next_review_date
                        ?->format(
                            'Y-m-d'
                        ),
                ]);
            }


            fclose($handle);
        },
        $filename,
        [
            'Content-Type'
                => 'text/csv; charset=UTF-8',
        ]
    );
}
public function exportAcknowledgements()
{
    abort_unless(
        Auth::user()->can(
            'manage_system_documents'
        ),
        403
    );


    $documents =
        SystemDocument::query()
            ->where(
                'requires_acknowledgement',
                true
            )
            ->where(
                'status',
                'approved'
            )
            ->where(
                'is_active',
                true
            )
            ->with('audiences')
            ->orderBy('title')
            ->get();


    $filename =
        'system-document-acknowledgement-compliance-'
        . now()->format('Ymd-His')
        . '.csv';


    return response()->streamDownload(
        function () use ($documents) {

            $handle =
                fopen(
                    'php://output',
                    'w'
                );


            fputcsv($handle, [
                'Document Code',
                'Document',
                'Version',
                'User',
                'Email',
                'Acknowledgement Status',
                'Acknowledged At',
            ]);


            foreach (
                $documents
                as $document
            ) {

                $userIds =
                    $this
                        ->getExpectedAudienceUserIds(
                            $document
                        );


                $users =
                    User::query()
                        ->whereIn(
                            'id',
                            $userIds
                        )
                        ->orderBy('name')
                        ->get();


                $acks =
                    SystemDocumentAcknowledgement::query()
                        ->where(
                            'system_document_id',
                            $document->id
                        )
                        ->where(
                            'document_version',
                            $document
                                ->current_version
                        )
                        ->whereIn(
                            'user_id',
                            $userIds
                        )
                        ->get()
                        ->keyBy(
                            'user_id'
                        );


                foreach ($users as $user) {

                    $ack =
                        $acks->get(
                            $user->id
                        );


                    fputcsv($handle, [
                        $document
                            ->document_code,

                        $document
                            ->title,

                        $document
                            ->current_version,

                        $user->name,

                        $user->email,

                        $ack
                            ? 'Acknowledged'
                            : 'Outstanding',

                        $ack
                            ?->acknowledged_at
                            ?->format(
                                'Y-m-d H:i:s'
                            ),
                    ]);
                }
            }


            fclose($handle);
        },
        $filename,
        [
            'Content-Type'
                => 'text/csv; charset=UTF-8',
        ]
    );
}
/*
|--------------------------------------------------------------------------
| Document Access Helpers
|--------------------------------------------------------------------------
*/

/**
 * Determine whether the logged-in user is part of the
 * controlled-document management/workflow team.
 *
 * This allows Lab Managers to see pending documents for approval
 * without giving them the powerful manage_system_documents permission.
 */
private function canAccessDocumentWorkflow(): bool
{
    $user = Auth::user();

    if (!$user) {
        return false;
    }

    return $user->can('manage_system_documents')
        || $user->can('approve_system_documents')
        || $user->can('edit_system_documents')
        || $user->can('archive_system_documents')
        || $user->can('review_system_documents');
}


/**
 * Determine whether the current user may access a restricted document.
 */
private function canAccessRestrictedDocuments(): bool
{
    return Auth::check()
        && Auth::user()->can(
            'view_restricted_system_documents'
        );
}


    /**
     * Enforce restricted-document access consistently for direct URLs,
     * downloads and workflow actions.
     */
    private function enforceRestrictedDocumentAccess(
        SystemDocument $document
    ): void {
        if (
            $document->classification === 'restricted'
            && !$this->canAccessRestrictedDocuments()
        ) {
            abort(403);
        }
    }
}