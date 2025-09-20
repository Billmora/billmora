<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Laravel\Prompts;

class MakeUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:user:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Billmora user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Billmora User Creator');
        $this->newLine();

        $firstName = Prompts\text(
            label: 'First name',
            required: true
        );

        $lastName = Prompts\text(
            label: 'Last name',
            required: true
        );

        $email = Prompts\text(
            label: 'Email address',
            required: true,
            validate: function (string $value) {
                if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return 'Invalid email address';
                }
                if (User::where('email', $value)->limit(1)->exists()) {
                    return 'Email has been taken';
                }
                return null;
            }
        );

        $password = Prompts\password(
            label: 'Password',
            required: true,
            validate: fn ($value) => strlen($value) < 8
                ? 'Password must be at least 8 characters'
                : null
        );

        $role = Prompts\select(
            label: 'User role',
            required: true,
            options: [
                'administrator' => 'Administrator (full access)',
                'client' => 'Client',
            ],
            default: 'client'
        );

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role === 'administrator',
        ]);

        $this->newLine();
        $this->info("User has been created successfully with the following details:");
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['First Name', $user->first_name],
                ['Last Name', $user->last_name],
                ['Email', $user->email],
                ['Role', ucfirst($role)],
            ]
        );
        return self::SUCCESS;
    }
}
