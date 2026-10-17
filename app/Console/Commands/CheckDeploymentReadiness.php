<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDeploymentReadiness extends Command
{
    protected $signature = 'app:deployment-check';

    protected $description = 'Check production configuration without exposing environment values';

    public function handle(): int
    {
        $checks = [
            'APP_ENV is production' => config('app.env') === 'production',
            'APP_KEY is configured' => filled(config('app.key')),
            'APP_DEBUG is disabled' => config('app.debug') === false,
            'APP_URL uses HTTPS' => parse_url((string) config('app.url'), PHP_URL_SCHEME) === 'https',
            'Database uses MySQL' => config('database.default') === 'mysql',
            'Cache is not an in-memory array' => config('cache.default') !== 'array',
            'Session storage uses a durable driver' => config('session.driver') !== 'array',
            'Public storage link exists' => is_link(public_path('storage')) || is_dir(public_path('storage')),
            'Mail transport is configured' => ! in_array(config('mail.default'), ['log', 'array'], true),
            'Mail sender address is valid' => filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL) !== false,
            'SMTP connection settings are configured' => config('mail.default') !== 'smtp'
                || (filled(config('mail.mailers.smtp.host'))
                    && config('mail.mailers.smtp.host') !== '127.0.0.1'
                    && filled(config('mail.mailers.smtp.port'))
                    && filled(config('mail.mailers.smtp.username'))
                    && filled(config('mail.mailers.smtp.password'))),
        ];

        $failed = [];
        foreach ($checks as $label => $passed) {
            $this->line(($passed ? '<fg=green>PASS</>' : '<fg=red>FAIL</>')." {$label}");
            if (! $passed) {
                $failed[] = $label;
            }
        }

        if ($failed !== []) {
            $this->newLine();
            $this->error(count($failed).' production readiness check(s) failed. See the deployment checklist.');

            return self::FAILURE;
        }

        $this->info('Production configuration checks passed.');

        return self::SUCCESS;
    }
}
