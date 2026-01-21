<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkRemovedMigrationsAsExecuted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrations:mark-removed-as-executed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marque les migrations supprimées lors de l\'optimisation comme déjà exécutées';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Liste des migrations supprimées qui doivent être marquées comme exécutées
        $removedMigrations = [
            '2026_01_08_101417_add_status_to_shootings_table',
            '2026_01_08_101432_add_status_to_publications_table',
            '2026_01_08_105205_remove_client_id_from_content_ideas_table',
            '2026_01_08_140820_add_description_to_shootings_table',
            '2026_01_08_140830_add_description_to_publications_table',
            '2026_01_09_141011_add_client_id_to_users_table',
            '2026_01_15_000000_add_team_role_to_users_table',
            '2026_01_16_104115_update_publications_status_and_add_reason',
            '2026_01_16_115841_update_shootings_status_and_add_reason',
            '2026_01_16_142908_add_report_date_to_client_reports_table',
            '2026_01_16_163207_add_time_to_shootings_and_publications',
        ];

        $this->info('Marquage des migrations supprimées comme exécutées...');
        $this->newLine();

        $marked = 0;
        $alreadyMarked = 0;

        foreach ($removedMigrations as $migration) {
            $exists = DB::table('migrations')->where('migration', $migration)->exists();
            
            if (!$exists) {
                $maxBatch = DB::table('migrations')->max('batch') ?? 1;
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $maxBatch
                ]);
                $this->line("✓ {$migration}");
                $marked++;
            } else {
                $this->line("→ {$migration} (déjà marquée)");
                $alreadyMarked++;
            }
        }

        $this->newLine();
        $this->info("✓ {$marked} migration(s) marquée(s) comme exécutée(s)");
        if ($alreadyMarked > 0) {
            $this->info("→ {$alreadyMarked} migration(s) déjà marquée(s)");
        }
        
        return Command::SUCCESS;
    }
}
