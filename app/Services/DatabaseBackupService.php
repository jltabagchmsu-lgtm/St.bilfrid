<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use PDO;
use Exception;

class DatabaseBackupService
{
    /**
     * Backup the current active database to SQL dump and SQLite backup.
     */
    public static function createBackup(): bool
    {
        try {
            $connection = config('database.default', 'mysql');
            $dbConn = DB::connection($connection);
            $pdo = $dbConn->getPdo();
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            $dumpPath = database_path('mysql_full_dump.sql');
            $sqlitePath = database_path('database.sqlite');

            if ($driver === 'mysql') {
                self::dumpMySqlToSqlFile($pdo, $dumpPath);
                self::syncMySqlToSqlite($pdo, $sqlitePath);
            } elseif ($driver === 'sqlite') {
                self::dumpSqliteToSqlFile($pdo, $dumpPath);
            }

            return true;
        } catch (Exception $e) {
            Log::warning('Database auto-backup notice: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Dump MySQL database content into portable SQL format.
     */
    protected static function dumpMySqlToSqlFile(PDO $pdo, string $outputPath): void
    {
        $dbName = config('database.connections.mysql.database', 'new_construction_firm');

        $sql = "-- ========================================================\n";
        $sql .= "-- St. Bilfrid Development Corporation - Master Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "START TRANSACTION;\n";
        $sql .= "SET time_zone = '+08:00';\n\n";
        $sql .= "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
        $sql .= "USE `{$dbName}`;\n\n";

        $tablesStmt = $pdo->query("SHOW TABLES");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Get table create statement
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $createSql = $createStmt['Create Table'] ?? null;
            if ($createSql) {
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $createSql . ";\n\n";
            }

            // Get table data
            $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);
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
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "COMMIT;\n";

        file_put_contents($outputPath, $sql);
    }

    /**
     * Mirror active MySQL data to local SQLite for instant offline dual-backup.
     */
    protected static function syncMySqlToSqlite(PDO $mysqlPdo, string $sqlitePath): void
    {
        try {
            $sqlitePdo = new PDO("sqlite:{$sqlitePath}");
            $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $tablesStmt = $mysqlPdo->query("SHOW TABLES");
            $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

            $sqlitePdo->exec("PRAGMA foreign_keys = OFF;");

            foreach ($tables as $table) {
                if ($table === 'migrations') {
                    continue;
                }

                // Check if table exists in SQLite
                $check = $sqlitePdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'")->fetchColumn();
                if (!$check) {
                    continue;
                }

                // Clear sqlite table
                $sqlitePdo->exec("DELETE FROM `{$table}`");

                // Copy from MySQL to SQLite
                $rows = $mysqlPdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                if (count($rows) > 0) {
                    $columns = array_keys($rows[0]);
                    $colList = implode('`, `', $columns);
                    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                    $insertSql = "INSERT INTO `{$table}` (`{$colList}`) VALUES ({$placeholders})";
                    $stmt = $sqlitePdo->prepare($insertSql);

                    foreach ($rows as $row) {
                        $stmt->execute(array_values($row));
                    }
                }
            }

            $sqlitePdo->exec("PRAGMA foreign_keys = ON;");
        } catch (Exception $e) {
            Log::warning('SQLite dual-sync notice: ' . $e->getMessage());
        }
    }

    /**
     * Dump SQLite database to MySQL-compatible SQL dump file.
     */
    protected static function dumpSqliteToSqlFile(PDO $sqlitePdo, string $outputPath): void
    {
        $dbName = config('database.connections.mysql.database', 'new_construction_firm');

        $sql = "-- ========================================================\n";
        $sql .= "-- St. Bilfrid Development Corporation - Master Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "START TRANSACTION;\n";
        $sql .= "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
        $sql .= "USE `{$dbName}`;\n\n";

        $tablesStmt = $sqlitePdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC;");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $rows = $sqlitePdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
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
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "COMMIT;\n";

        file_put_contents($outputPath, $sql);
    }
}
