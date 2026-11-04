<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentManufacturer;
use App\Models\KplcEquipmentType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class MigrateEquipmentConfiguration extends Command
{
    /**
     * Use --commit to save changes.
     * Without --commit, the command performs a dry run.
     */
    protected $signature =
        'equipment:migrate-configuration
        {--commit : Save matched configuration IDs}
        {--limit= : Maximum number of equipment records to process}';

    protected $description =
        'Match legacy equipment category, manufacturer and type values to Laboratory Configuration records';

    private int $processed = 0;

    private int $updated = 0;

    private int $unmatchedCategories = 0;

    private int $unmatchedManufacturers = 0;

    private int $unmatchedTypes = 0;
    private $categories;

private $manufacturers;

private $equipmentTypes;

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $this->categories = EquipmentCategory::query()->get();

$this->manufacturers =
    EquipmentManufacturer::query()->get();

$this->equipmentTypes =
    KplcEquipmentType::query()->get();
        $commit = (bool) $this->option('commit');

        $limit = $this->option('limit');

        $limit = is_numeric($limit)
            ? max((int) $limit, 1)
            : null;

        $this->newLine();

        $this->info(
            $commit
                ? 'Running in COMMIT mode.'
                : 'Running in DRY-RUN mode. No changes will be saved.'
        );

        $this->newLine();

        $query = Equipment::query()
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $query->chunkById(
            100,
            function ($equipmentRecords) use ($commit) {
                foreach ($equipmentRecords as $equipment) {
                    $this->processEquipment(
                        $equipment,
                        $commit
                    );
                }
            }
        );

        $this->newLine();

        $this->table(
            ['Result', 'Count'],
            [
                ['Processed', $this->processed],
                ['Records requiring update', $this->updated],
                ['Unmatched categories', $this->unmatchedCategories],
                ['Unmatched manufacturers', $this->unmatchedManufacturers],
                ['Unmatched equipment types', $this->unmatchedTypes],
            ]
        );

        if (!$commit) {
            $this->newLine();

            $this->warn(
                'Dry run completed. Run again with --commit after reviewing the results.'
            );
        }

        return self::SUCCESS;
    }

    /**
     * Process one equipment record.
     */
    private function processEquipment(
        Equipment $equipment,
        bool $commit
    ): void {
        $this->processed++;

        $updates = [];

        /*
         * Resolve category only when category_id is currently empty.
         */
        if (empty($equipment->category_id)) {
            $legacyCategory = $this->legacyValue(
                $equipment,
                [
                    'category',
                    'equipment_category',
                ]
            );

            if ($legacyCategory !== null) {
                $category = $this->findCategory(
                    $legacyCategory
                );

                if ($category) {
                    $updates['category_id'] = $category->id;
                } else {
                    $this->unmatchedCategories++;

                    $this->warn(
                        "Equipment #{$equipment->id}: category not matched [{$legacyCategory}]"
                    );
                }
            }
        }

        /*
         * Resolve manufacturer only when manufacturer_id is empty.
         */
        if (empty($equipment->manufacturer_id)) {
            $legacyManufacturer = $this->legacyValue(
                $equipment,
                [
                    'manufacturer',
                    'manufacturer_name',
                ]
            );

            if ($legacyManufacturer !== null) {
                $manufacturer = $this->findManufacturer(
                    $legacyManufacturer
                );

                if ($manufacturer) {
                    $updates['manufacturer_id'] =
                        $manufacturer->id;
                } else {
                    $this->unmatchedManufacturers++;

                    $this->warn(
                        "Equipment #{$equipment->id}: manufacturer not matched [{$legacyManufacturer}]"
                    );
                }
            }
        }

        /*
         * Resolve pricing/equipment type only when type_id is empty.
         */
        if (empty($equipment->type_id)) {
            $legacyType = $this->legacyValue(
                $equipment,
                [
                    'type',
                    'equipment_type',
                ]
            );

            if ($legacyType !== null) {
                $equipmentType = $this->findEquipmentType(
                    $legacyType
                );

                if ($equipmentType) {
                    $updates['type_id'] =
                        $equipmentType->id;
                } else {
                    $this->unmatchedTypes++;

                    $this->warn(
                        "Equipment #{$equipment->id}: type not matched [{$legacyType}]"
                    );
                }
            }
        }

        if ($updates === []) {
            return;
        }

        $this->updated++;

        $summary = collect($updates)
            ->map(
                fn ($value, $field) =>
                    "{$field}={$value}"
            )
            ->implode(', ');

        $this->line(
            "Equipment #{$equipment->id}: {$summary}"
        );

        if (!$commit) {
            return;
        }

        try {
            DB::transaction(
                function () use (
                    $equipment,
                    $updates
                ) {
                    $equipment->forceFill($updates);
                    $equipment->save();
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->error(
                "Equipment #{$equipment->id} could not be updated: " .
                $exception->getMessage()
            );
        }
    }

    /**
     * Read a legacy text field safely.
     */
    private function legacyValue(
        Equipment $equipment,
        array $columns
    ): ?string {
        foreach ($columns as $column) {
            $value = $equipment->getRawOriginal($column);

            if (
                is_string($value)
                && trim($value) !== ''
            ) {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * Find a matching category by name or code.
     */
    private function findCategory(
        string $legacyValue
    ): ?EquipmentCategory {
        $normalized = $this->normalize(
            $legacyValue
        );

        return $this->categories->first(
    function (EquipmentCategory $category) use (
        $normalized
    ) {
        return $this->normalize(
            $category->name
        ) === $normalized
            || $this->normalize(
                $category->code
            ) === $normalized;
    }
);
    }

    /**
     * Find a matching manufacturer by name or code.
     */
    private function findManufacturer(
        string $legacyValue
    ): ?EquipmentManufacturer {
        $normalized = $this->normalize(
            $legacyValue
        );

       return $this->manufacturers->first(
    function (
        EquipmentManufacturer $manufacturer
    ) use ($normalized) {
        return $this->normalize(
            $manufacturer->name
        ) === $normalized
            || $this->normalize(
                $manufacturer->code
            ) === $normalized;
    }
);
    }

    /**
     * Find a matching equipment/pricing type.
     */
    private function findEquipmentType(
        string $legacyValue
    ): ?KplcEquipmentType {
        $normalized = $this->normalize(
            $legacyValue
        );

        return $this->equipmentTypes->first(
    fn (KplcEquipmentType $equipmentType) =>
        $this->normalize(
            $equipmentType->name
        ) === $normalized
);
    }

    /**
     * Normalize values before comparison.
     */
    private function normalize(
        ?string $value
    ): string {
        return Str::of((string) $value)
            ->lower()
            ->replace(['&', '/'], ['and', ' '])
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }
}
