<?php

namespace App\Filament\Pages\Admin\Settings;

use App\Models\Setting;
use App\Notifications\MailTested;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification as MailNotification;
use Illuminate\Support\Facades\Request;
use Locale;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class General extends Page
{
    protected static ?string $navigationIcon = 'tabler-nut';
    protected static string $view = 'filament.pages.admin.settings.general';
    protected static ?string $slug = 'settings/general';
    protected ?string $subheading = 'Configure a general settings.';

    public 
        $company_name, 
        $company_theme, 
        $company_logo, 
        $company_favicon, 
        $company_description, 
        $company_date_format, 
        $company_language, 
        $company_country, 
        $company_maintenance, 
        $company_maintenance_url, 
        $company_maintenance_message,
        $term_tos,
        $term_tos_url,
        $term_tos_content,
        $term_toc,
        $term_toc_url,
        $term_toc_content,
        $term_privacy,
        $term_privacy_url,
        $term_privacy_content,
        $social_discord,
        $social_youtube,
        $social_whatsapp,
        $social_instagram,
        $social_facebook,
        $social_twitter,
        $social_linkedin,
        $social_github,
        $social_reddit,
        $social_skype,
        $social_telegram,
        $ordering_tos,
        $ordering_notes,
        $ordering_firstname,
        $ordering_lastname,
        $ordering_email,
        $ordering_phone,
        $ordering_company,
        $ordering_address1,
        $ordering_address2,
        $ordering_city,
        $ordering_country,
        $ordering_state,
        $ordering_postcode,
        $mail_driver,
        $mail_from_address,
        $mail_from_name,
        $mail_host,
        $mail_port,
        $mail_username,
        $mail_password,
        $mail_encryption,
        $mail_mailgun_domain,
        $mail_mailgun_secret,
        $mail_mailgun_endpoint;

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

    public function mount(): void
    {
        $defaults = [
            'company_name' => 'Billmora',
            'company_theme' => 'default',
            'company_logo' => 'https://viidev.com/assets/img/logo/logo.png',
            'company_favicon' => 'https://viidev.com/assets/img/logo/logo.png',
            'company_description' => 'Free and Open source Billing Management Operations & Recurring Automation.',
            'company_date_format' => 'd/m/Y',
            'company_language' => 'en_US',
            'company_country' => 'ID',
            'company_maintenance' => null,
            'company_maintenance_url' => null,
            'company_maintenance_message' => 'We are currently performing maintenance and will be back shortly.',
            'term_tos' => false,
            'term_tos_url' => null,
            'term_tos_content' => null,
            'term_toc' => false,
            'term_toc_url' => null,
            'term_toc_content' => null,
            'term_privacy' => false,
            'term_privacy_url' => null,
            'term_privacy_content' => null,
            'social_discord' => null,
            'social_youtube' => null,
            'social_whatsapp' => null,
            'social_instagram' => null,
            'social_facebook' => null,
            'social_twitter' => null,
            'social_linkedin' => null,
            'social_github' => null,
            'social_reddit' => null,
            'social_skype' => null,
            'social_telegram' => null,
            'ordering_tos' => false,
            'ordering_notes' => false,
            'ordering_firstname' => null,
            'ordering_lastname' => null,
            'ordering_email' => null,
            'ordering_phone' => null,
            'ordering_company' => null,
            'ordering_address1' => null,
            'ordering_address2' => null,
            'ordering_city' => null,
            'ordering_country' => 'ID',
            'ordering_state' => null,
            'ordering_postcode' => null,
            'mail_driver' => env('MAIL_DRIVER', null),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', null),
            'mail_from_name' => env('MAIL_FROM_NAME', null),
            'mail_host' => env('MAIL_HOST', null),
            'mail_port' => env('MAIL_PORT', null),
            'mail_username' => env('MAIL_USERNAME', null),
            'mail_password' => env('MAIL_PASSWORD', null),
            'mail_encryption' => env('MAIL_ENCRYPTION', null),
            'mail_mailgun_domain' => env('MAILGUN_DOMAIN', null),
            'mail_mailgun_secret' => env('MAILGUN_SECRET', null),
            'mail_mailgun_endpoint' => env('MAILGUN_ENDPOINT', null),
        ];
    
        $settings = Setting::pluck('value', 'key');
    
        foreach ($defaults as $key => $defaultValue) {
            $value = $settings[$key] ?? $defaultValue;

            if (is_bool($defaultValue)) {
                $this->$key = (bool) $value;
            } elseif (is_int($defaultValue)) {
                $this->$key = (int) $value;
            } elseif (is_float($defaultValue)) {
                $this->$key = (float) $value;
            } elseif (is_null($defaultValue)) {
                $this->$key = $value !== null ? $value : null;
            } else {
                $this->$key = $value;
            }
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->persistTabInQueryString()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('company')
                        ->label('Company')
                        ->icon('tabler-building')
                        ->schema($this->tabCompany()),
                    Forms\Components\Tabs\Tab::make('ordering')
                        ->label('Ordering')
                        ->icon('tabler-truck-delivery')
                        ->schema($this->tabOrdering()),
                    Forms\Components\Tabs\Tab::make('mail')
                        ->label('Mail')
                        ->icon('tabler-mail')
                        ->schema($this->tabMail()),
                    Forms\Components\Tabs\Tab::make('term')
                        ->label('Term')
                        ->icon('tabler-circle-dashed-check')
                        ->schema($this->tabTerm()),
                    Forms\Components\Tabs\Tab::make('social')
                        ->label('Social')
                        ->icon('tabler-social')
                        ->schema($this->tabSocial()),
                ]),
        ];
    }

    private function tabCompany()
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('company_name')
                                ->label('Name')
                                ->required()
                                ->helperText('The name of your Company.'),
                            Forms\Components\Select::make('company_theme')
                                ->label('Theme')
                                ->options(collect(File::directories(resource_path('views/theme')))
                                    ->mapWithKeys(fn ($path) => [
                                        basename($path) => basename($path)
                                    ])
                                    ->toArray())
                                ->native(false)
                                ->required()
                                ->columnSpan(1)
                                ->helperText('The theme you want Billmora to use.'),
                            Forms\Components\TextInput::make('company_logo')
                                ->label('Logo URL')
                                ->suffixIcon('tabler-world')
                                ->required()
                                ->helperText('Enter your Company logo URL.'),
                            Forms\Components\TextInput::make('company_favicon')
                                ->label('Favicon URL')
                                ->suffixIcon('tabler-world')
                                ->required()
                                ->helperText('Enter your Company favicon URL.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\Textarea::make('company_description')
                                ->label('Description')
                                ->rows(3)
                                ->helperText('The description of your Company.'),
                        ]),
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('company_date_format')
                                ->label('Date Format')
                                ->options([
                                    'd/m/Y' => 'DD/MM/YYYY (31/12/2025)',
                                    'm/d/Y' => 'MM/DD/YYYY (12/31/2025)',
                                    'Y-m-d' => 'YYYY-MM-DD (2025-12-31)',
                                    'd-m-Y' => 'DD-MM-YYYY (31-12-2025)',
                                    'M d, Y' => 'Mon DD, YYYY (Dec 31, 2025)',
                                    'F d, Y' => 'Month DD, YYYY (December 31, 2025)',
                                ])
                                ->native(false)
                                ->required()
                                ->helperText('Default date format for your Company.'),
                            Forms\Components\Select::make('company_language')
                                ->label('Language')
                                ->options(collect(File::directories(resource_path('lang')))
                                    ->mapWithKeys(fn ($path) => [
                                        basename($path) => Locale::getDisplayName(basename($path), app()->getLocale())
                                    ])
                                    ->toArray()
                                )
                                ->native(false)
                                ->required()
                                ->helperText('Default language for Billmora clientarea.'),
                            Forms\Components\Select::make('company_country')
                                ->label('Country')
                                ->options(config('utils.countries'))
                                ->searchable()
                                ->native(false)
                                ->required()
                                ->helperText('Country where your company is located.'),
                        ])
                ]),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Toggle::make('company_maintenance')
                                        ->label('Maintenance Mode')
                                        ->inline(false)
                                        ->onIcon('tabler-check')
                                        ->offIcon('tabler-x')
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->helperText('Prevents client area access when enabled.'),
                                    Forms\Components\TextInput::make('company_maintenance_url')
                                        ->label('Maintenance URL')
                                        ->suffixIcon('tabler-world')
                                        ->helperText('If specified, clients will be redirected to that URL when Maintenance is enabled.'),
                                ])
                                ->columnSpan(1),
                            Forms\Components\Textarea::make('company_maintenance_message')
                                ->label('Maintenance Message')
                                ->rows(5)
                                ->columnSpan(1)
                                ->helperText('The message that will be displayed when Maintenance is enabled.'),
                        ]),
                ]),
        ];
    }

    private function tabOrdering()
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Toggle::make('ordering_tos')
                        ->label('Terms of Service')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->helperText('If enable, clients must agree to company Terms of Service.'),
                    Forms\Components\Toggle::make('ordering_notes')
                        ->label('Notes on Checkout')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->helperText('If enable, clients can enter additional info on the order form.'),
                ]),
            Forms\Components\Section::make('Default Billing or Contact details')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('ordering_firstname')
                                ->label('First Name'),
                            Forms\Components\TextInput::make('ordering_lastname')
                                ->label('Last Name'),
                            Forms\Components\TextInput::make('ordering_email')
                                ->label('Email Address'),
                            PhoneInput::make('ordering_phone')
                                ->label('Phone Number')
                                ->displayNumberFormat(PhoneInputNumberType::NATIONAL),
                            Forms\Components\TextInput::make('ordering_company')
                                ->label('Company Name'),
                            Forms\Components\TextInput::make('ordering_address1')
                                ->label('Street Address 1'),
                            Forms\Components\TextInput::make('ordering_address2')
                                ->label('Street Address 2'),
                            Forms\Components\TextInput::make('ordering_city')
                                ->label('City'),
                            Forms\Components\Select::make('ordering_country')
                                ->label('Country')
                                ->options(config('utils.countries'))
                                ->searchable(),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('ordering_state')
                                        ->label('State/Region'),
                                    Forms\Components\TextInput::make('ordering_postcode')
                                        ->label('Postcode'),
                                ])
                                ->columnSpan(1),
                        ]),
                ])
        ];
    }

    private function tabMail()
    {
        return [
            Forms\Components\ToggleButtons::make('mail_driver')
                ->label('Mail Driver')
                ->inline()
                ->options([
                    'smtp' => 'SMTP Server',
                    'mailgun' => 'Mailgun',
                    'sendmail' => 'Sendmail (PHP)',
                ])
                ->live()
                ->required()
                ->hintAction(
                    Forms\Components\Actions\Action::make('test')
                        ->label('Send Test Mail')
                        ->icon('tabler-send')
                        ->action(fn () => $this->sendTestEmail())
                ),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('mail_from_address')
                        ->label('Mail From Address')
                        ->required()
                        ->email(),
                    Forms\Components\TextInput::make('mail_from_name')
                        ->label('Mail From Name')
                        ->required(),
                ]),
            Forms\Components\Section::make('SMTP Configuration')
                ->columns()
                ->visible(fn (Forms\Get $get) => $get('mail_driver') === 'smtp')
                ->schema([
                    Forms\Components\TextInput::make('mail_host')
                        ->label('Host')
                        ->required(),
                    Forms\Components\TextInput::make('mail_port')
                        ->label('Port')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(65535),
                    Forms\Components\TextInput::make('mail_username')
                        ->label('Username'),
                    Forms\Components\TextInput::make('mail_password')
                        ->label('Password')
                        ->password()
                        ->revealable(),
                    Forms\Components\ToggleButtons::make('mail_encryption')
                        ->label('Encryption')
                        ->inline()
                        ->options([
                            'tls' => 'TLS',
                            'ssl' => 'SSL',
                            '' => 'None',
                        ])
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $port = match ($state) {
                                'tls' => 587,
                                'ssl' => 465,
                                default => 25,
                            };
                            $set('mail_port', $port);
                        }),
                ]),
            Forms\Components\Section::make('Mailgun Configuration')
                ->columns(3)
                ->visible(fn (Forms\Get $get) => $get('mail_driver') === 'mailgun')
                ->schema([
                    Forms\Components\TextInput::make('mail_mailgun_domain')
                        ->label('Domain')
                        ->suffixIcon('tabler-world')
                        ->required(),
                    Forms\Components\TextInput::make('mail_mailgun_secret')
                        ->label('Secret')
                        ->password()
                        ->revealable()
                        ->required(),
                    Forms\Components\TextInput::make('mail_mailgun_endpoint')
                        ->label('Endpoint')
                        ->suffixIcon('tabler-world')
                        ->required(),
                ]),
        ];
    }

    private function tabTerm()
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('term_tos')
                                ->label('Terms of Service')
                                ->inline(false)
                                ->onIcon('tabler-check')
                                ->offIcon('tabler-x')
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/terms-of-service.'),
                            Forms\Components\TextInput::make('term_tos_url')
                                ->label('Terms of Service URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('term_tos_content')
                                ->label('Terms of Service Content')
                                ->disableToolbarButtons([
                                    'attachFiles',
                                ])
                                ->extraAttributes(['x-ref' => 'editor'])
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('fullscreen')
                                        ->icon('tabler-arrows-maximize')
                                        ->alpineClickHandler('$refs.editor.requestFullscreen()'))
                                        ->helperText('This content will be displayed at: https://example.com/terms-of-service.'),
                        ])
                ]),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('term_toc')
                                ->label('Terms of Condition')
                                ->inline(false)
                                ->onIcon('tabler-check')
                                ->offIcon('tabler-x')
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/terms-of-condition.'),
                            Forms\Components\TextInput::make('term_toc_url')
                                ->label('Terms of Condition URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('term_toc_content')
                                ->label('Terms of Condition Content')
                                ->disableToolbarButtons([
                                    'attachFiles',
                                ])
                                ->extraAttributes(['x-ref' => 'editor'])
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('fullscreen')
                                        ->icon('tabler-arrows-maximize')
                                        ->alpineClickHandler('$refs.editor.requestFullscreen()'))
                                        ->helperText('This content will be displayed at: https://example.com/terms-of-condition.'),

                        ])
                ]),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('term_privacy')
                                ->label('Privacy Policy')
                                ->inline(false)
                                ->onIcon('tabler-check')
                                ->offIcon('tabler-x')
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/privacy-policy.'),
                            Forms\Components\TextInput::make('term_privacy_url')
                                ->label('Privacy Policy URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('term_privacy_content')
                                ->label('Privacy Policy Content')
                                ->disableToolbarButtons([
                                    'attachFiles',
                                ])
                                ->extraAttributes(['x-ref' => 'editor'])
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('fullscreen')
                                        ->icon('tabler-arrows-maximize')
                                        ->alpineClickHandler('$refs.editor.requestFullscreen()'))
                                        ->helperText('This content will be displayed at: https://example.com/privacy-policy.'),

                        ])
                ]),
        ];
    }

    private function tabSocial()
    {
        return [
            Forms\Components\Grid::make(1)
                ->schema([  
                    Forms\Components\TextInput::make('social_discord')
                        ->label('Discord')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_youtube')
                        ->label('YouTube')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_whatsapp')
                        ->label('WhatsApp')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_instagram')
                        ->label('Instagram')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_facebook')
                        ->label('Facebook')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_twitter')
                        ->label('Twitter')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_linkedin')
                        ->label('LinkedIn')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_github')
                        ->label('GitHub')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_reddit')
                        ->label('Reddit')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_skype')
                        ->label('Skype')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_telegram')
                        ->label('Telegram')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    
                ]),
        ];
    }

    private function writeToEnv(array $data)
    {
        $path = base_path('.env');
        
        if (!File::exists($path)) {
            throw new \Exception(".env file not found!");
        }

        $env = File::get($path);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}=" . (is_null($value) ? 'null' : '"' . addslashes($value) . '"');

            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $replacement, $env);
            } else {
                $env .= "\n{$replacement}";
            }
        }

        File::put($path, $env);
    }

    public function sendTestEmail()
    {
        try {
            $toEmail = auth()->user()->email;
            $subject = 'Welcome to Billmora!';
            $body = 'This is a test email to verify the configuration.';

            \Mail::raw($body, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)
                    ->subject($subject);
            });

            Notification::make()
                ->title('Test email sent successfully!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to send test email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function save()
    {
        try {
            $validated = $this->validate([
                'company_name' => 'required|string|max:255',
                'company_theme' => 'required|string',
                'company_logo' => 'required|url',
                'company_favicon' => 'required|url',
                'company_description' => 'nullable|string|max:255',
                'company_date_format' => 'required|string|in:d/m/Y,m/d/Y,Y-m-d,d-m-Y,M d, Y,F d, Y',
                'company_language' => 'required|string',
                'company_country' => 'required|string',
                'company_maintenance' => 'nullable|boolean',
                'company_maintenance_url' => 'nullable|url',
                'company_maintenance_message' => 'nullable|string|max:255',
                'term_tos' => 'nullable|boolean',
                'term_tos_url' => 'nullable|url',
                'term_tos_content' => 'nullable',
                'term_toc' => 'nullable|boolean',
                'term_toc_url' => 'nullable|url',
                'term_toc_content' => 'nullable',
                'term_privacy' => 'nullable|boolean',
                'term_privacy_url' => 'nullable|url',
                'term_privacy_content' => 'nullable',
                'social_discord' => 'nullable|url',
                'social_youtube' => 'nullable|url',
                'social_whatsapp' => 'nullable|url',
                'social_instagram' => 'nullable|url',
                'social_facebook' => 'nullable|url',
                'social_twitter' => 'nullable|url',
                'social_linkedin' => 'nullable|url',
                'social_github' => 'nullable|url',
                'social_reddit' => 'nullable|url',
                'social_skype' => 'nullable|url',
                'social_telegram' => 'nullable|url',
                'ordering_tos' => 'nullable|boolean',
                'ordering_notes' => 'nullable|boolean',
                'ordering_firstname' => 'nullable|string',
                'ordering_lastname' => 'nullable|string',
                'ordering_email' => 'nullable|email',
                'ordering_phone' => 'nullable|numeric',
                'ordering_company' => 'nullable|string',
                'ordering_address1' => 'nullable|string',
                'ordering_address2' => 'nullable|string',
                'ordering_city' => 'nullable|string',
                'ordering_country' => 'nullable|string',
                'ordering_state' => 'nullable|string',
                'ordering_postcode' => 'nullable|string',
            ]);

            
            if (is_null($validated['ordering_phone'])) {
                $validated['ordering_phone'] = '';
            }
            
            collect($validated)->each(function ($value, $key) {
                if (!is_null($value)) {
                    Setting::updateOrCreate(['key' => $key], ['value' => $value]);
                }
            });

            $validatedMail = $this->validate([
                'mail_driver' => 'required|string|in:smtp,mailgun,sendmail',
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'required|string|max:255',
                'mail_host' => 'required_if:mail_driver,smtp|string|max:255',
                'mail_port' => 'required_if:mail_driver,smtp|integer|min:1|max:65535',
                'mail_username' => 'nullable|string|max:255',
                'mail_password' => 'nullable|string|max:255',
                'mail_encryption' => 'nullable|string|in:tls,ssl,none',
                'mail_mailgun_domain' => 'required_if:mail_driver,mailgun|string',
                'mail_mailgun_secret' => 'required_if:mail_driver,mailgun|string',
                'mail_mailgun_endpoint' => 'required_if:mail_driver,mailgun|url',
            ]);
            if ($validatedMail['mail_encryption'] === 'none') {
                $validatedMail['mail_encryption'] = null;
            }
    
            $this->writeToEnv([
                'MAIL_MAILER'     => $validatedMail['mail_driver'],
                'MAIL_HOST'       => $validatedMail['mail_host'],
                'MAIL_PORT'       => $validatedMail['mail_port'],
                'MAIL_USERNAME'   => $validatedMail['mail_username'] ?? '',
                'MAIL_PASSWORD'   => $validatedMail['mail_password'] ?? '',
                'MAIL_ENCRYPTION' => $validatedMail['mail_encryption'] ?? '',
                'MAIL_FROM_ADDRESS' => $validatedMail['mail_from_address'],
                'MAIL_FROM_NAME'    => $validatedMail['mail_from_name'],
                'MAILGUN_DOMAIN'   => $validatedMail['mail_mailgun_domain'] ?? '',
                'MAILGUN_SECRET'   => $validatedMail['mail_mailgun_secret'] ?? '',
                'MAILGUN_ENDPOINT' => $validatedMail['mail_mailgun_endpoint'] ?? '',
            ]);
    
            Artisan::call('config:clear');
    
            Notification::make()
                ->title('Saved successfully!')
                ->success()
                ->send();
        } catch (\Exception $exception) {
            Notification::make()
                ->title('Failed to save')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
    
}
