<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GrantAdministratorCommand extends Command
{
    protected $signature = 'mytradex:admin:grant {email : Email address of an existing verified user}';

    protected $description = 'Grant administrator access to an existing verified account after confirmation';

    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));
        $user = User::where('email', $email)->first();

        if (! $user || ! $user->hasVerifiedEmail()) {
            $this->error('No verified account was found for that email address.');

            return self::FAILURE;
        }

        if ($user->status !== 'active') {
            $this->error('Administrator access can only be granted to an active account.');

            return self::FAILURE;
        }

        if ($user->role === 'admin') {
            $this->info('This account already has administrator access.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Grant administrator access to '.$user->email.'?')) {
            $this->warn('No changes were made.');

            return self::SUCCESS;
        }

        $user->forceFill(['role' => 'admin'])->save();
        Log::notice('Administrator role granted through the console', ['user_id' => $user->id]);
        $this->info('Administrator access granted.');

        return self::SUCCESS;
    }
}
