<?php

declare(strict_types=1);

namespace App\Console\Commands\Git;

use App\Enums\GitProvider;
use App\Services\Git\GitOAuthService;
use Illuminate\Console\Command;

class ConnectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:connect {provider : The git provider (github, gitlab)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to a Git provider via OAuth';

    /**
     * Execute the console command.
     */
    public function handle(GitOAuthService $oauthService): int
    {
        $providerStr = $this->argument('provider');

        try {
            $provider = GitProvider::from($providerStr);
        } catch (\ValueError) {
            $this->error("Invalid provider: {$providerStr}");
            $this->info('Available providers: github, gitlab');

            return self::FAILURE;
        }

        $redirectUri = config("services.{$provider->value}.redirect");

        $this->info("🔐 Connecting to {$provider->displayName()}...");
        $this->newLine();

        $authData = $oauthService->generateAuthUrl($provider, $redirectUri);

        $this->info('📋 Please open this URL in your browser:');
        $this->line($authData['auth_url']);
        $this->newLine();

        if ($this->confirm('Would you like to open it now?', true)) {
            $this->openUrl($authData['auth_url']);
        }

        $this->newLine();
        $this->info('After authorization, you will be redirected to:');
        $this->line($redirectUri);
        $this->newLine();
        $this->warn('⏱️  OAuth state expires in 10 minutes');

        return self::SUCCESS;
    }

    /**
     * Open URL in browser.
     */
    private function openUrl(string $url): void
    {
        $command = match (PHP_OS_FAMILY) {
            'Darwin' => 'open',
            'Windows' => 'start',
            'Linux' => 'xdg-open',
            default => null,
        };

        if ($command !== null) {
            exec("{$command} ".escapeshellarg($url));
            $this->info('✓ URL opened in browser');
        } else {
            $this->warn('Could not automatically open browser');
        }
    }
}
