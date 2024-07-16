<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class ConfigureDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'configure:database-connection {connection?} {database?} {username?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure database connection';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $connection = $this->argument('connection') ?: $this->choice('Select database connection', ['mysql', 'pgsql'], 0);
        $database = $this->argument('database') ?: $this->ask('Enter database name');
        $username = $this->argument('username') ?: $this->ask('Enter database username');
        $password = $this->argument('password') ?: $this->secret('Enter database password');

        $this->configureDatabase($connection, $database, $username, $password);

        $this->info("Database connection [$connection] configured successfully!");
    }

    private function configureDatabase($connection, $database, $username, $password)
    {
        \Log::info('Configuring database: ' . $connection);
    DB::purge($connection);

    $config = $this->getDatabaseConfig($connection, $database, $username, $password);

    config(['database.connections.' . $connection => $config]);
    config(['database.default' => $connection]);

    // Purge the connection pool
    DB::purge($connection);

    \Log::info('Running migrations for: ' . $connection);

    // Run migrations for the specified connection
   // Artisan::call('migrate', ['--database' => $connection]);
    }
    

    private function getDatabaseConfig($connection, $database, $username, $password)
    {
        // Generate and return the database configuration array based on the connection type
        if ($connection == 'pgsql') {
            return [
                'driver' => 'pgsql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => 5432, // Hard-code the port for PostgreSQL
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8',
                'prefix' => '',
                'prefix_indexes' => true,
                'search_path' => 'public',
                'sslmode' => 'prefer',
            ];
        } elseif ($connection == 'mysql') {
            return [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
                'options' => extension_loaded('pdo_mysql') ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ]) : [],
            ];
        }

        return [];
    }

    // private function getDatabaseConfig($connection, $database, $username, $password)
    // {
    //     // Default configuration for MySQL
    //     $config = [
    //         'driver' => 'mysql',
    //         'host' => env('DB_HOST', '127.0.0.1'),
    //         'port' => env('DB_PORT', '3306'),
    //         'database' => $database,
    //         'username' => $username,
    //         'password' => $password,
    //         'unix_socket' => env('DB_SOCKET', ''),
    //         'charset' => 'utf8mb4',
    //         'collation' => 'utf8mb4_unicode_ci',
    //         'prefix' => '',
    //         'prefix_indexes' => true,
    //         'strict' => true,
    //         'engine' => null,
    //         'options' => extension_loaded('pdo_mysql') ? array_filter([
    //             PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    //         ]) : [],
    //     ];

    //     // Override the configuration if the user selected PostgreSQL
    //     if ($connection == 'pgsql') {
    //         $config = [
    //             'driver' => 'pgsql',
    //             'host' => env('DB_HOST', '127.0.0.1'),
    //             'port' => env('DB_PORT', '5432'),
    //             'database' => $database,
    //             'username' => $username,
    //             'password' => $password,
    //             'charset' => 'utf8',
    //             'prefix' => '',
    //             'prefix_indexes' => true,
    //             'search_path' => 'public',
    //             'sslmode' => 'prefer',
    //         ];
    //     }

    //     return $config;
    // }
}
