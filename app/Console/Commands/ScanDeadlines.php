<?php

namespace App\Console\Commands;

use App\Services\DeadlineScanner;
use Illuminate\Console\Command;

class ScanDeadlines extends Command
{
    protected $signature = 'autosur:scan-deadlines';

    protected $description = "Crée les rappels d'échéances (assurance, visite technique, documents) et envoie les notifications";

    public function handle(DeadlineScanner $scanner): int
    {
        $count = $scanner->run();
        $this->info("{$count} rappel(s) créé(s).");

        return self::SUCCESS;
    }
}