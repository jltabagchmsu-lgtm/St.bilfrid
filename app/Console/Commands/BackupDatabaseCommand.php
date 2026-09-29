<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup current database to database/mysql_full_dump.sql and dual-sync SQLite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup and sync...');
        $success = DatabaseBackupService::createBackup();
        if ($success) {
            $this->info('✓ Database backup generated successfully at: database/mysql_full_dump.sql');
            $this->info('✓ Dual SQLite backup synced at: database/database.sqlite');
            return 0;
        } else {
            $this->error('Failed to create database backup.');
            return 1;
        }
    }
}
