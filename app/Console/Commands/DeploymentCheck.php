<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeploymentCheck extends Command
{
    protected $signature = 'app:deployment-check';

    protected $description = 'Check deployment requirements before release';

    public function handle(): int
    {
        $checks = [
            'Session storage uses a durable driver' => config('session.driver') !== 'array',
            'Database uses MySQL' => in_array(config('database.default'), ['mysql', 'mariadb'], true),
        ];
        $hasFailures = false;

        foreach ($checks as $description => $passed) {
            $this->line(($passed ? 'PASS' : 'FAIL').' '.$description);
            $hasFailures = $hasFailures || ! $passed;
        }

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }
}
