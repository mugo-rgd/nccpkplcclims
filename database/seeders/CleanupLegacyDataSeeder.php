<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CleanupLegacyDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🧹 Starting data cleanup...');

        // 1. Fix duplicate users
        $this->fixDuplicateUsers();

        // 2. Create missing customer-user relationships
        $this->createMissingCustomerRelations();

        // 3. Clean up phone numbers
        $this->cleanPhoneNumbers();

        // 4. Update customer notes
        $this->updateCustomerNotes();

        $this->command->info('✅ Data cleanup completed!');
    }

    private function fixDuplicateUsers()
    {
        $this->command->info('Fixing duplicate users...');

        $duplicates = DB::table('users')
            ->select('email')
            ->whereNotNull('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $users = User::where('email', $duplicate->email)->orderBy('id')->get();
            $keep = $users->first();

            foreach ($users->skip(1) as $user) {
                // Reassign customer relationships
                Customer::where('user_id', $user->id)->update(['user_id' => $keep->id]);
                // Delete duplicate
                $user->delete();
                $this->command->info("Deleted duplicate user: {$user->email}");
            }
        }
    }

    private function createMissingCustomerRelations()
    {
        $this->command->info('Creating missing customer-user relations...');

        $customers = Customer::whereNull('user_id')
            ->whereNotNull('email')
            ->get();

        foreach ($customers as $customer) {
            $user = User::where('email', $customer->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $customer->contact_person,
                    'email' => $customer->email,
                    'password' => Hash::make('password123'),
                    'role' => 'customer',
                    'is_active' => true,
                ]);
                $user->assignRole('customer');
                $this->command->info("Created user for: {$customer->email}");
            }

            $customer->update(['user_id' => $user->id]);
            $this->command->info("Linked customer {$customer->company_name} to user {$user->name}");
        }
    }

    private function cleanPhoneNumbers()
    {
        $this->command->info('Cleaning phone numbers...');

        $users = User::whereNotNull('phone_number')->get();
        foreach ($users as $user) {
            $phone = preg_replace('/[^0-9]/', '', (string)$user->phone_number);
            if (strlen($phone) >= 9) {
                if (substr($phone, 0, 1) === '0') {
                    $phone = '254' . substr($phone, 1);
                } elseif (strlen($phone) === 9) {
                    $phone = '254' . $phone;
                }
                $user->phone_number = $phone;
                $user->save();
            }
        }
        $this->command->info("Cleaned phone numbers for {$users->count()} users");
    }

    private function updateCustomerNotes()
    {
        $this->command->info('Updating customer notes...');

        $customers = Customer::whereNotNull('notes')
            ->whereRaw('notes NOT LIKE \'%{%\'')
            ->get();

        foreach ($customers as $customer) {
            $notes = $customer->notes;
            if (is_string($notes) && !empty($notes)) {
                $newNotes = [
                    'legacy_note' => $notes,
                    'converted_at' => now()->toDateTimeString(),
                ];
                $customer->notes = json_encode($newNotes);
                $customer->save();
            }
        }
        $this->command->info("Updated notes for {$customers->count()} customers");
    }
}
