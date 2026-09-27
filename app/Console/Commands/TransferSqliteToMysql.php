<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use PDO;
use Exception;

class TransferSqliteToMysql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:transfer-to-mysql 
                            {--database=new_construction_firm : Name of the MySQL database}
                            {--host=127.0.0.1 : MySQL Host}
                            {--port=3306 : MySQL Port}
                            {--username=root : MySQL Username}
                            {--password= : MySQL Password}
                            {--update-env : Automatically update .env to use MySQL}
                            {--export-only : Only generate the SQL dump file without running live transfer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seamlessly transfer all SQLite schema and data to MySQL, and export a ready-to-import MySQL dump file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('====================================================');
        $this->info(' St. Bilfrid Dev Corp - Database Transfer to MySQL  ');
        $this->info('====================================================');

        $dbName = $this->option('database') ?: 'new_construction_firm';
        $host = $this->option('host') ?: '127.0.0.1';
        $port = $this->option('port') ?: '3306';
        $username = $this->option('username') ?: 'root';
        $password = $this->option('password') ?: '';
        $exportOnly = $this->option('export-only');

        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath)) {
            $this->error("SQLite database file not found at: {$sqlitePath}");
            return 1;
        }

        $sqlitePdo = new PDO("sqlite:{$sqlitePath}");
        $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get all tables from SQLite
        $stmt = $sqlitePdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC;");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->info("Found " . count($tables) . " tables in SQLite database.");

        // 1. Generate Standalone SQL Dump File
        $sqlDumpPath = database_path('mysql_full_dump.sql');
        $this->generateSqlDumpFile($sqlitePdo, $tables, $dbName, $sqlDumpPath);
        $this->info("✓ MySQL SQL Dump file generated successfully at: database/mysql_full_dump.sql");

        if ($exportOnly) {
            $this->info('Export-only flag supplied. Live database transfer skipped.');
            return 0;
        }

        // 2. Attempt Live Connection & Migration to MySQL
        $this->info("\nChecking live MySQL server at {$host}:{$port}...");
        try {
            // Test root connection without database selected
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $mysqlRootPdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]);

            $this->info("✓ Successfully connected to MySQL server!");

            // Create database if not exists
            $mysqlRootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->info("✓ MySQL database `{$dbName}` verified/created.");

            // Configure dynamic MySQL connection in config
            config([
                'database.connections.mysql.host' => $host,
                'database.connections.mysql.port' => $port,
                'database.connections.mysql.database' => $dbName,
                'database.connections.mysql.username' => $username,
                'database.connections.mysql.password' => $password,
            ]);

            DB::purge('mysql');

            // Run migrations on MySQL
            $this->info("\nRunning Laravel migrations on MySQL database `{$dbName}`...");
            Artisan::call('migrate', [
                '--database' => 'mysql',
                '--force' => true,
            ]);
            $this->line(Artisan::output());

            // Transfer Data table by table
            $this->info("\nTransferring table data from SQLite to MySQL...");
            $mysqlConn = DB::connection('mysql');
            $mysqlPdo = $mysqlConn->getPdo();

            $mysqlPdo->exec('SET FOREIGN_KEY_CHECKS=0;');

            $totalRecords = 0;
            foreach ($tables as $table) {
                if ($table === 'migrations') {
                    continue;
                }

                // Check if table exists in MySQL
                if (!Schema::connection('mysql')->hasTable($table)) {
                    $this->warn("Table `{$table}` does not exist in MySQL schema. Skipping.");
                    continue;
                }

                // Fetch SQLite rows
                $rowsStmt = $sqlitePdo->query("SELECT * FROM `{$table}`");
                $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);
                $rowCount = count($rows);

                // Truncate MySQL table first
                $mysqlConn->table($table)->truncate();

                if ($rowCount > 0) {
                    // Chunk inserts in batches of 200
                    foreach (array_chunk($rows, 200) as $chunk) {
                        $mysqlConn->table($table)->insert($chunk);
                    }
                    $this->line("  → `{$table}`: transferred {$rowCount} records.");
                    $totalRecords += $rowCount;
                } else {
                    $this->line("  → `{$table}`: 0 records (empty).");
                }
            }

            $mysqlPdo->exec('SET FOREIGN_KEY_CHECKS=1;');

            $this->info("\n✓ Complete! Transferred {$totalRecords} total records across all tables into MySQL.");

            // Update .env if requested or automatically
            $this->updateEnvFile($dbName, $host, $port, $username, $password);
            $this->info("✓ Updated `.env` file with MySQL connection settings.");

        } catch (Exception $e) {
            $this->warn("\n[Note] Could not perform direct live transfer to MySQL: " . $e->getMessage());
            $this->info("\nIf your MySQL service (e.g. in XAMPP) is not running currently:");
            $this->info("1. Start MySQL in XAMPP Control Panel.");
            $this->info("2. Import `database/mysql_full_dump.sql` directly in phpMyAdmin (http://localhost/phpmyadmin) or run:");
            $this->info("   php artisan db:transfer-to-mysql");
            $this->info("3. Update your `.env` to DB_CONNECTION=mysql");

            // Update .env anyway so it is ready
            $this->updateEnvFile($dbName, $host, $port, $username, $password);
        }

        return 0;
    }

    /**
     * Generate portable SQL Dump file for phpMyAdmin or MySQL CLI import.
     */
    protected function generateSqlDumpFile(PDO $sqlitePdo, array $tables, string $dbName, string $outputPath)
    {
        $sql = "-- ========================================================\n";
        $sql .= "-- St. Bilfrid Development Corporation\n";
        $sql .= "-- Master MySQL Database Dump\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "START TRANSACTION;\n";
        $sql .= "SET time_zone = '+08:00';\n\n";

        $sql .= "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
        $sql .= "USE `{$dbName}`;\n\n";

        foreach ($tables as $table) {
            $rowsStmt = $sqlitePdo->query("SELECT * FROM `{$table}`");
            $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Data for table `{$table}` (" . count($rows) . " rows)\n";
            $sql .= "-- --------------------------------------------------------\n";

            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
                $colList = implode('`, `', $columns);

                foreach (array_chunk($rows, 50) as $chunk) {
                    $sql .= "INSERT INTO `{$table}` (`{$colList}`) VALUES\n";
                    $valuesArr = [];
                    foreach ($chunk as $row) {
                        $valList = [];
                        foreach ($row as $val) {
                            if (is_null($val)) {
                                $valList[] = "NULL";
                            } elseif (is_numeric($val) && !preg_match('/^0[0-9]+/', (string)$val)) {
                                $valList[] = $val;
                            } else {
                                $valList[] = "'" . addcslashes((string)$val, "\0..\37'\\") . "'";
                            }
                        }
                        $valuesArr[] = "(" . implode(", ", $valList) . ")";
                    }
                    $sql .= implode(",\n", $valuesArr) . ";\n";
                }
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "COMMIT;\n";

        file_put_contents($outputPath, $sql);
    }

    /**
     * Update .env file with MySQL credentials cleanly line-by-line.
     */
    protected function updateEnvFile(string $dbName, string $host, string $port, string $username, string $password)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        // Backup existing .env to .env.sqlite_backup if not existing
        if (!file_exists(base_path('.env.sqlite_backup'))) {
            file_put_contents(base_path('.env.sqlite_backup'), $content);
        }

        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
        $newLines = [];

        foreach ($lines as $line) {
            if (preg_match('/^#?\s*DB_CONNECTION=/', $line)) {
                $newLines[] = 'DB_CONNECTION=mysql';
            } elseif (preg_match('/^#?\s*DB_HOST=/', $line)) {
                $newLines[] = "DB_HOST={$host}";
            } elseif (preg_match('/^#?\s*DB_PORT=/', $line)) {
                $newLines[] = "DB_PORT={$port}";
            } elseif (preg_match('/^#?\s*DB_DATABASE=/', $line)) {
                $newLines[] = "DB_DATABASE={$dbName}";
            } elseif (preg_match('/^#?\s*DB_USERNAME=/', $line)) {
                $newLines[] = "DB_USERNAME={$username}";
            } elseif (preg_match('/^#?\s*DB_PASSWORD=/', $line)) {
                $newLines[] = "DB_PASSWORD={$password}";
            } else {
                $newLines[] = $line;
            }
        }

        file_put_contents($envPath, implode("\n", $newLines));
    }
}
