<?php

namespace App\Console\Commands;

use App\Models\SystemDocument;
use App\Models\SystemDocumentAcknowledgement;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSystemDocumentCompliance extends Command
{
    protected $signature =
        'documents:check-compliance';

    protected $description =
        'Check document reviews and outstanding acknowledgements';


    public function handle(): int
    {
        $this->info(
            'Checking System Documentation compliance...'
        );


        /*
        |--------------------------------------------------------------------------
        | Review overdue
        |--------------------------------------------------------------------------
        */

        $overdue =
            SystemDocument::query()
                ->where(
                    'status',
                    'approved'
                )
                ->where(
                    'is_active',
                    true
                )
                ->whereNotNull(
                    'review_date'
                )
                ->where(
                    'review_date',
                    '<',
                    now()->startOfDay()
                )
                ->get();


        $this->newLine();

        $this->info(
            'Overdue document reviews: '
            . $overdue->count()
        );


        foreach ($overdue as $document) {

            $this->warn(
                $document->document_code
                . ' | '
                . $document->title
                . ' | Review date: '
                . $document
                    ->review_date
                    ->format(
                        'Y-m-d'
                    )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Due within 30 days
        |--------------------------------------------------------------------------
        */

        $dueSoon =
            SystemDocument::query()
                ->where(
                    'status',
                    'approved'
                )
                ->where(
                    'is_active',
                    true
                )
                ->whereNotNull(
                    'review_date'
                )
                ->whereBetween(
                    'review_date',
                    [
                        now()->startOfDay(),
                        now()
                            ->addDays(30)
                            ->endOfDay(),
                    ]
                )
                ->get();


        $this->newLine();

        $this->info(
            'Reviews due within 30 days: '
            . $dueSoon->count()
        );


        foreach ($dueSoon as $document) {

            $this->line(
                $document->document_code
                . ' | '
                . $document->title
                . ' | '
                . $document
                    ->review_date
                    ->format(
                        'Y-m-d'
                    )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Outstanding acknowledgements
        |--------------------------------------------------------------------------
        */

        $documents =
            SystemDocument::query()
                ->where(
                    'status',
                    'approved'
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    'requires_acknowledgement',
                    true
                )
                ->with('audiences')
                ->get();


        $totalOutstanding = 0;


        foreach ($documents as $document) {

            $roleIds =
                $document
                    ->audiences
                    ->whereNotNull(
                        'role_id'
                    )
                    ->pluck(
                        'role_id'
                    )
                    ->unique();


            $directUserIds =
                $document
                    ->audiences
                    ->whereNotNull(
                        'user_id'
                    )
                    ->pluck(
                        'user_id'
                    );


            $roleUsers = collect();


            if ($roleIds->isNotEmpty()) {

                $roleUsers =
                    DB::table(
                        'model_has_roles'
                    )
                        ->whereIn(
                            'role_id',
                            $roleIds
                        )
                        ->where(
                            'model_type',
                            User::class
                        )
                        ->pluck(
                            'model_id'
                        );
            }


            $expected =
                $directUserIds
                    ->merge(
                        $roleUsers
                    )
                    ->map(
                        fn ($id)
                            => (int) $id
                    )
                    ->unique()
                    ->values();


            $acknowledged =
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
                        $expected
                    )
                    ->pluck(
                        'user_id'
                    );


            $outstanding =
                $expected
                    ->diff(
                        $acknowledged
                    );


            $totalOutstanding +=
                $outstanding->count();


            if ($outstanding->isNotEmpty()) {

                $this->warn(
                    $document->document_code
                    . ' | '
                    . $document->title
                    . ' | Outstanding: '
                    . $outstanding->count()
                );
            }
        }


        $this->newLine();

        $this->info(
            'Total outstanding acknowledgements: '
            . $totalOutstanding
        );


        $this->newLine();

        $this->info(
            'Compliance check complete.'
        );


        return self::SUCCESS;
    }
}