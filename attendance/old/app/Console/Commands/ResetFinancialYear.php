<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetFinancialYear extends Command
{
    protected $signature = 'financial-year:reset';
    protected $description = 'Reset data for new financial year';

    public function handle()
    {
        $newFinancialYear = active_fy_label();
        $newDatabase = "hrms_leave_system_$newFinancialYear";

        // Create new database
        DB::statement("CREATE DATABASE IF NOT EXISTS `$newDatabase`");

        // Update .env to use new database
        $this->updateEnvDatabase($newDatabase);

        // Run migrations on new database
        $this->call('migrate', ['--database' => 'mysql']);

        // Truncate old data in previous database
        Schema::connection('mysql')->dropIfExists('leave_applications');
        Schema::connection('mysql')->dropIfExists('public_holidays');

        $this->info("Financial year reset to $newFinancialYear completed.");
    }



    private function updateEnvDatabase($database)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        $envContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE=$database", $envContent);
        file_put_contents($envPath, $envContent);
    }
}