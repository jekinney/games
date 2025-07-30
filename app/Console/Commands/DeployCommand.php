<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy {--skip-migrations : Skip running migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run deployment tasks: migrations, seeders, and cache optimization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting application deployment...');

        // Step 1: Run migrations if not skipped
        if (!$this->option('skip-migrations')) {
            $this->info('📊 Running database migrations...');
            Artisan::call('migrate', ['--force' => true]);
            $this->info('✅ Migrations completed');
        } else {
            $this->info('⏭️ Skipping migrations as requested');
        }

        // Step 2: Seed essential data
        $this->info('🌱 Seeding essential data...');
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\DeploymentSeeder',
            '--force' => true
        ]);
        $this->info('✅ Essential data seeded');

        // Step 3: Clear caches
        $this->info('🧹 Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        $this->info('✅ Caches cleared');

        // Step 4: Optimize for production
        $this->info('⚡ Optimizing application...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        $this->info('✅ Application optimized');

        // Step 5: Create storage link
        $this->info('🔗 Creating storage link...');
        try {
            Artisan::call('storage:link');
            $this->info('✅ Storage link created');
        } catch (\Exception $e) {
            $this->warn('⚠️ Storage link already exists or failed to create');
        }

        $this->info('🎉 Deployment completed successfully!');
        
        return Command::SUCCESS;
    }
}
