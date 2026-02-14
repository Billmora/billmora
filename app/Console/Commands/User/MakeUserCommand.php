<?php

namespace App\Console\Commands\User;

use App\Jobs\NotificationJob;
use App\Models\User;
use App\Models\UserEmailVerification;
use Billmora;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $insertBilling = Prompts\confirm(
            label: 'Do you want to insert Billing Information for this user? (Optional, can be set later in Client Area) [No]',
            default: false
        );

        $billingData = [];

        if ($insertBilling) {
            $billingData['phone_number'] = Prompts\text('Phone Number (optional)', required: false) ?: null;
            $billingData['company_name'] = Prompts\text('Company name (optional)', required: false) ?: null;
            $billingData['street_address_1'] = Prompts\text('Street address 1 (optional)', required: false) ?: null;
            $billingData['street_address_2'] = Prompts\text('Street address 2 (optional)', required: false) ?: null;
            $billingData['city'] = Prompts\text('City (optional)', required: false) ?: null;
            $billingData['state'] = Prompts\text('State (optional)', required: false) ?: null;
            $billingData['postcode'] = Prompts\text('Postcode (optional)', required: false) ?: null;

            $countries = config('utils.countries');

            $selectedCountry = Prompts\search(
                label: 'Country (optional)',
                options: fn (string $value) => collect(['' => '(skip)'] + $countries)
                    ->filter(fn ($name, $code) =>
                        str_contains(strtolower($name), strtolower($value)) ||
                        str_contains(strtolower($code), strtolower($value)) ||
                        $code === ''
                    )
                    ->map(fn ($name, $code) => $code ? "$code - $name" : $name)
                    ->all(),
                required: true
            );

            if ($selectedCountry && $selectedCountry !== '(skip)') {
                [$countryCode] = explode(' - ', $selectedCountry, 2);
                $billingData['country'] = $countryCode;
            } else {
                $billingData['country'] = null;
            }
        }

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($password),
            'is_root_admin' => $role === 'administrator',
        ]);

        $user->billing()->create($billingData ?? [
            'phone_number' => null,
            'company_name' => null,
            'street_address_1' => null,
            'street_address_2' => null,
            'city' => null,
            'state' => null,
            'postcode' => null,
            'country' => null,
        ]);

        $token = Str::random(64);
        UserEmailVerification::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addMinutes(60),
        ]);

        NotificationJob::dispatch(
            $user->email,
            'user_registration', 
            [
                'client_name' => $user->fullname,
                'company_name' => Billmora::getGeneral('company_name'),
                'verify_url' => route('client.email.verify', ['token' => $token]),
                'clientarea_url' => config('app.url'),
            ],
            $user->language
        );

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
