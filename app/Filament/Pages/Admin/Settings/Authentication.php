<?php

namespace App\Filament\Pages\Admin\Settings;

use App\Services\BillmoraService as Billmora;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use function PHPUnit\Framework\returnArgument;

class Authentication extends Page
{
    protected static ?string $navigationIcon = 'tabler-password-user';
    protected static string $view = 'filament.pages.admin.settings.authentication';
    protected static ?string $slug = 'settings/authentication';
    protected ?string $subheading = 'Configure a authentication settings.';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Settings')
                ->url('/admin/settings')
                ->icon('tabler-settings')
                ->isActiveWhen(fn () => request()->is('admin/settings*'))
                ->sort(1),
        ];
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
        ->statePath('data')
        ->schema([
            Forms\Components\Tabs::make()
                ->persistTabInQueryString()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('user')
                        ->label('User')
                        ->icon('tabler-user-plus')
                        ->schema($this->tabUser()),
                    Forms\Components\Tabs\Tab::make('captcha')
                        ->label('Captcha')
                        ->icon('tabler-shield')
                        ->schema($this->tabCaptcha()),
                ]),
        ]);
    }

    private function tabUser(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\Toggle::make('user_verified')
                        ->label('User Must Verified')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('Require users to verify their email address before they can login.')
                        ->default(Billmora::getAuth('user_verified')),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\CheckboxList::make('form_disable')
                            ->label('Hide Registration Form')
                            ->options([
                                'phone_number' => 'Phone Number',
                                'company_name' => 'Company Name',
                                'street_address_1' => 'Street Address 1',
                                'street_address_2' => 'Street Address 2',
                                'city' => 'City',
                                'country' => 'Country',
                                'state' => 'State',
                                'postcode' => 'Postcode',
                            ])
                            ->columns(4)
                            ->helperText('Select the fields you want to disable from the registration form.')
                            ->default(Billmora::getAuth('form_disable')),
                        ]),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\CheckboxList::make('form_required')
                            ->label('Required Registration Form')
                            ->options([
                                'phone_number' => 'Phone Number',
                                'company_name' => 'Company Name',
                                'street_address_1' => 'Street Address 1',
                                'street_address_2' => 'Street Address 2',
                                'city' => 'City',
                                'country' => 'Country',
                                'state' => 'State',
                                'postcode' => 'Postcode',
                            ])
                            ->columns(4)
                            ->helperText('Select the fields you want to required from the registration form.')
                            ->default(Billmora::getAuth('form_required')),
                        ])
                ]),
        ];
    }

    private function tabCaptcha(): array
    {
        return [
            Forms\Components\ToggleButtons::make('captcha_driver')
                ->label('Captcha Driver')
                ->inline()
                ->options([
                    '' => 'None',
                    'turnstile' => 'Turnstile',
                    'recaptchav2' => 'reCaptcha v2',
                    'hcaptcha' => 'hCaptcha',
                ])
                ->live()
                ->required()
                ->helperText('Select which captcha service to use for authentication. Choose "None" to disable captcha protection.')
                ->default(env('CAPTCHA_DRIVER')),
            Forms\Components\Section::make()
                ->visible(fn (Forms\Get $get) => $get('captcha_driver') != '')
                ->schema([
                    Forms\Components\CheckboxList::make('captcha_active')
                    ->label('Enable Captcha on?')
                    ->options([
                        'user_register' => 'User registration',
                        'user_login' => 'User login',
                    ])
                    ->columns(4)
                    ->required()
                    ->helperText('Select the fields you want to enable the Captcha.')
                    ->default(Billmora::getAuth('captcha_active')),
                ]),
            Forms\Components\Section::make('Turnstile Configuration')
                ->columns()
                ->visible(fn (Forms\Get $get) => $get('captcha_driver') === 'turnstile')
                ->schema([
                    Forms\Components\TextInput::make('turnstile_site_key')
                        ->label('Site Key')
                        ->required()
                        ->password()
                        ->revealable()
                        ->helperText('Enter the Cloudflare Turnstile Site Key.')
                        ->default(env('TURNSTILE_SITE_KEY')),
                    Forms\Components\TextInput::make('turnstile_secret_key')
                        ->label('Secret Key')
                        ->required()
                        ->password()
                        ->revealable()
                        ->helperText('Enter the Cloudflare Turnstile Secret Key.')
                        ->default(env('TURNSTILE_SECRET_KEY')),
                ]),
            Forms\Components\Section::make('reCaptcha v2 Configuration')
                ->columns()
                ->visible(fn (Forms\Get $get) => $get('captcha_driver') === 'recaptchav2')
                ->schema([
                    Forms\Components\TextInput::make('recaptchav2_site_key')
                        ->label('Site Key')
                        ->required()
                        ->password()
                        ->revealable()
                        ->helperText('Enter the reCaptcha v2 Site Key.')
                        ->default(env('RECAPTCHAV2_SITE_KEY')),
                    Forms\Components\TextInput::make('recaptchav2_secret_key')
                        ->label('Secret Key')
                        ->required()
                        ->password()
                        ->revealable()
                        ->helperText('Enter the reCaptcha v2 Secret Key.')
                        ->default(env('RECAPTCHAV2_SECRET_KEY')),
                ]),
            Forms\Components\Section::make('hCaptcha Configuration')
                ->columns()
                ->visible(fn (Forms\Get $get) => $get('captcha_driver') === 'hcaptcha')
                ->schema([
                    Forms\Components\TextInput::make('hcaptcha_site_key')
                        ->label('Site Key')
                        ->required()
                        ->password()
                        ->revealable()
                        ->helperText('Enter the hCaptcha Site Key.')
                        ->default(env('RECAPTCHAV2_SITE_KEY')),
                    Forms\Components\TextInput::make('hcaptcha_secret_key')
                        ->label('Secret Key')
                        ->required()
                        ->password()
                        ->revealable()
                        ->helperText('Enter the hCaptcha Secret Key.')
                        ->default(env('RECAPTCHAV2_SECRET_KEY')),
                ]),
        ];
    }

    public function save(): void
    {
        try {
            $validated = Validator::make($this->data, [
                'user_verified' => ['required', 'boolean'],
                'form_disable' => ['nullable', 'array'],
                'form_required' => ['nullable', 'array'],
                'captcha_driver' => ['nullable', 'string'],
                'captcha_active' => ['nullable', 'array'],
                'turnstile_site_key' => ['nullable', 'string'],
                'turnstile_secret_key' => ['nullable', 'string'],
                'recaptchav2_site_key' => ['nullable', 'string'],
                'recaptchav2_secret_key' => ['nullable', 'string'],
                'hcaptcha_site_key' => ['nullable', 'string'],
                'hcaptcha_secret_key' => ['nullable', 'string'],
            ])->validate();

            Billmora::setAuth($validated);

            switch ($validated['captcha_driver']) {
                case 'turnstile':
                    Billmora::setEnv([
                        'CAPTCHA_DRIVER' => $validated['captcha_driver'],
                        'TURNSTILE_SITE_KEY' => $validated['turnstile_site_key'],
                        'TURNSTILE_SECRET_KEY' => $validated['turnstile_secret_key'],
                    ]);
                    break;
                case 'recaptchav2':
                    Billmora::setEnv([
                        'CAPTCHA_DRIVER' => $validated['captcha_driver'],
                        'RECAPTCHAV2_SITE_KEY' => $validated['recaptchav2_site_key'],
                        'RECAPTCHAV2_SECRET_KEY' => $validated['recaptchav2_secret_key'],
                    ]);
                    break;
                case 'hcaptcha':
                    Billmora::setEnv([
                        'CAPTCHA_DRIVER' => $validated['captcha_driver'],
                        'HCAPTCHA_SITE_KEY' => $validated['hcaptcha_site_key'],
                        'HCAPTCHA_SECRET_KEY' => $validated['hcaptcha_secret_key'],
                    ]);
                    break;
                default:
                    Billmora::setAuth([
                        'CAPTCHA_DRIVER' => $validated['captcha_driver'],
                    ]);
                    break;
            }

            Notification::make()
                ->title('Success')
                ->body('Authentication settings have been updated successfully.')
                ->success()
                ->send();
        } catch (ValidationException $e) {
            $errorMessages = '<ul>' . collect($e->errors())
            ->map(fn ($messages) => '<li>' . implode('</li><li>', $messages) . '</li>')
            ->implode('') . '</ul>';

            Notification::make()
                ->title('Validation Error')
                ->body($errorMessages)
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('An unexpected error occurred: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
