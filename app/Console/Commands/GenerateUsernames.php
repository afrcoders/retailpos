<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateUsernames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-usernames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate usernames for users without one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $usersWithoutUsername = User::whereNull('username')->get();

        if ($usersWithoutUsername->isEmpty()) {
            $this->info('All users already have usernames!');
            return;
        }

        foreach ($usersWithoutUsername as $user) {
            // Generate username from email or name
            $username = Str::slug(explode('@', $user->email)[0]);

            // Ensure uniqueness
            $baseUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $user->update(['username' => $username]);
            $this->info("Generated username '{$username}' for user: {$user->email}");
        }

        $this->info('Usernames generated successfully!');
    }
}
