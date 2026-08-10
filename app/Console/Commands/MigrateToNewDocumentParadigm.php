<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Console\Command;

class MigrateToNewDocumentParadigm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-to-new-document-paradigm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing data to the new group/company-isolated document paradigm';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data migration to new document paradigm...');

        // 1. Create a default group/company
        $defaultGroup = Group::firstOrCreate(
            ['name' => 'Empresa Principal']
        );
        $this->info("Default company (group) '{$defaultGroup->name}' ensured.");

        // 2. Assign all existing folders to the default group
        $foldersUpdated = Folder::whereNull('group_id')->update(['group_id' => $defaultGroup->id]);
        $this->info("Updated {$foldersUpdated} orphan folders to belong to the default company (group).");

        // 3. Assign all existing users to the default group
        $usersUpdated = User::whereNull('group_id')->update(['group_id' => $defaultGroup->id]);
        $this->info("Assigned {$usersUpdated} orphan users to the default company (group).");

        // 4. Assign roles
        $users = User::orderBy('id', 'asc')->get();
        if ($users->isNotEmpty()) {
            $firstUser = $users->first();
            $firstUser->role = 'admin';
            $firstUser->save();
            $this->info("Assigned 'admin' role to user ID {$firstUser->id} ({$firstUser->email}).");

            $otherUsersCount = User::where('id', '!=', $firstUser->id)->update(['role' => 'reader']);
            $this->info("Assigned 'reader' role to {$otherUsersCount} other users.");
        }

        $this->info('Migration to new paradigm completed successfully!');
    }
}
