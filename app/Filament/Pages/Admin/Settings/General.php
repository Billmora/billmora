<?php

namespace App\Filament\Pages\Admin\Settings;

use App\Models\Setting;
use App\Notifications\Mail;
use App\Traits\EnvironmentWriter;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Locale;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class General extends Page
{
    use EnvironmentWriter;

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
        $ordering_redirect,
        $ordering_grace,
        $ordering_tos,
        $ordering_notes,
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
        $mail_mailgun_endpoint,
        $invoices_pdf,
        $invoices_pdf_size,
        $invoices_pdf_font,
        $invoices_mass_payment,
        $invoices_choose_payment,
        $invoices_cancelation_handling,
        $invoices_paid_numbering,
        $invoices_next_paid_number,
        $invoices_number_format,
        $invoices_late_type,
        $invoices_late_amount,
        $invoices_late_minimum,
        $invoices_increment,
        $invoices_start,
        $credit_use,
        $credit_min_deposit,
        $credit_max_deposit,
        $credit_max,
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
        $social_telegram;

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
            'ordering_redirect' => 'payment',
            'ordering_grace' => 2,
            'ordering_tos' => false,
            'ordering_notes' => false,
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
            'invoices_pdf' => false,
            'invoices_pdf_size' => 'A4',
            'invoices_pdf_font' => 'Poppins',
            'invoices_mass_payment' => true,
            'invoices_choose_payment' => true,
            'invoices_cancelation_handling' => false,
            'invoices_paid_numbering' => true,
            'invoices_next_paid_number' => 1,
            'invoices_number_format' => '{NUMBER}',
            'invoices_late_type' => 'percent',
            'invoices_late_amount' => 10,
            'invoices_late_minimum' => 0,
            'invoices_increment' => 1,
            'invoices_start' => null,
            'credit_use' => false,
            'credit_min_deposit' => 0,
            'credit_max_deposit' => 0,
            'credit_max' => 0,
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
                    Forms\Components\Tabs\Tab::make('invoices')
                        ->label('Invoices')
                        ->icon('tabler-invoice')
                        ->schema($this->tabInvoices()),
                    Forms\Components\Tabs\Tab::make('credit')
                        ->label('Credit')
                        ->icon('tabler-coin')
                        ->schema($this->tabCredit()),
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
                    Forms\Components\Radio::make('ordering_redirect')
                        ->label('Auto Redirect on Checkout')
                        ->options([
                            'complete' => 'Just show the order completed page (no payment redirect)',
                            'invoice' => 'Automatically take the user to the invoice',
                            'payment' => 'Automatically forward the user to the payment gateway'
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('ordering_grace')
                        ->label('Order days grace')
                        ->numeric()
                        ->required()
                        ->helperText('The number of days to allow for payment of an order before being overdue.'),
                    Forms\Components\Toggle::make('ordering_tos')
                        ->label('Terms of Service')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('If enable, clients must agree to company Terms of Service.'),
                    Forms\Components\Toggle::make('ordering_notes')
                        ->label('Notes on Checkout')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('If enable, clients can enter additional info on the order form.'),
                ]),
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
                        ->action(fn () => Mail::sendTestMail())
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

    private function tabInvoices()
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Toggle::make('invoices_pdf')
                        ->label('PDF Invoices')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('Enables sending PDF invoices with emails and downloading PDFs directly from the invoice page.'),
                    Forms\Components\Select::make('invoices_pdf_size')
                        ->label('PDF Paper Size')
                        ->options([
                            'A4' => 'A4',
                            'letter' => 'Letter',
                        ])
                        ->native(false)
                        ->required()
                        ->helperText('Choose the paper format to use when generating PDF files.'),
                    Forms\Components\TextInput::make('invoices_pdf_font')
                        ->label('PDF Font Family')
                        ->hintAction(
                            Forms\Components\Actions\Action::make('googleFonts')
                                ->label('Browse Google Fonts')
                                ->icon('tabler-world-search')
                                ->url('https://fonts.google.com', true))
                        ->required()
                        ->helperText('All Google Fonts are available to use.'),
                    Forms\Components\Toggle::make('invoices_mass_payment')
                        ->label('Mass Payment')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('Enable the multiple invoice payment options on the client area.'),
                    Forms\Components\Toggle::make('invoices_choose_payment')
                        ->label('Clients Choose Gateway')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('Enable to allow clients to choose the gateway they pay with.'),
                    Forms\Components\Toggle::make('invoices_cancelation_handling')
                        ->label('Cancellation Request Handling')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('Enable to automatically cancel outstanding unpaid invoices when a cancellation request is submitted.'),
                    Forms\Components\Toggle::make('invoices_paid_numbering')
                        ->label('Sequential Paid Invoice Numbering')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('Enable automatic sequential numbering of paid invoices'),
                    Forms\Components\TextInput::make('invoices_next_paid_number')
                        ->label('Next Paid Invoice Number')
                        ->numeric()
                        ->required()
                        ->helperText('The next invoice number that will be assigned.'),
                    Forms\Components\TextInput::make('invoices_number_format')
                        ->label('Sequential Invoice Number Format')
                        ->required()
                        ->helperText('Available auto-insert tags are: {YEAR} {MONTH} {DAY} {NUMBER}.'),
                    Forms\Components\Radio::make('invoices_late_type')
                        ->label('Late Fee Type')
                        ->options([
                            'percent' => 'Percentage',
                            'amount' => 'Fix Amount',
                            ])
                            ->required(),
                    Forms\Components\TextInput::make('invoices_late_amount')
                        ->label('Late Fee Amount')
                        ->required()
                        ->helperText('Enter the amount (percentage or monetary value) to apply to late invoices (set to 0 to disable).'),
                    Forms\Components\TextInput::make('invoices_late_minimum')
                        ->label('Late Fee Minimum')
                        ->required()
                        ->helperText('Enter the minimum amount to charge in cases where the calculated late fee falls below this figure.'),
                ]),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('invoices_increment')
                        ->label('Invoice # Incrementation')
                        ->numeric()
                        ->required()
                        ->helperText('Enter the desired difference between generated invoice numbers (Default: 1, Maximum: 999)'),
                    Forms\Components\TextInput::make('invoices_start')
                        ->label('Invoice Starting #')
                        ->numeric()
                        ->helperText('Enter a value to set the next invoice number (Minimum: 10,000, Maximum: 9,999,999). Blank for no change'),
                ]),
        ];
    }

    private function tabCredit()
    {
        return [
            Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\Toggle::make('credit_use')
                    ->label('Credit')
                    ->inline(false)
                    ->onIcon('tabler-check')
                    ->offIcon('tabler-x')
                    ->onColor('success')
                    ->offColor('danger')
                    ->required()
                    ->helperText('Enable adding and use of funds by clients from the client area.'),
                Forms\Components\TextInput::make('credit_min_deposit')
                    ->label('Minimum Deposit')
                    ->required()
                    ->helperText('Enter the minimum amount that can be deposited.'),
                Forms\Components\TextInput::make('credit_max_deposit')
                    ->label('Maximum Deposit')
                    ->required()
                    ->helperText('Enter the maximum amount that can be deposited.'),
                Forms\Components\TextInput::make('credit_max')
                    ->label('Maximum Balance')
                    ->required()
                    ->helperText('Enter the maximum balance that can be deposited.'),
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
    
    public function save()
    {
        try {
            $validated = $this->validate([
                'company_name' => 'required|string|max:255',
                'company_theme' => 'required|string',
                'company_logo' => 'required|url',
                'company_favicon' => 'required|url',
                'company_description' => 'nullable|string|max:255',
                'company_date_format' => 'required|string',
                'company_language' => 'required|string',
                'company_country' => 'required|string',
                'company_maintenance' => 'nullable|boolean',
                'company_maintenance_url' => 'nullable|url',
                'company_maintenance_message' => 'nullable|string|max:255',
                'ordering_redirect' => 'required|string|in:complete,invoice,payment',
                'ordering_grace' => 'required|integer',
                'ordering_tos' => 'required|boolean',
                'ordering_notes' => 'required|boolean',
                'invoices_pdf' => 'required|boolean',
                'invoices_pdf_size' => 'required|string|in:A4,letter',
                'invoices_pdf_font' => 'required|string',
                'invoices_mass_payment' => 'required|boolean',
                'invoices_choose_payment' => 'required|boolean',
                'invoices_cancelation_handling' => 'required|boolean',
                'invoices_paid_numbering' => 'required|boolean',
                'invoices_next_paid_number' => 'required|integer|min:1',
                'invoices_number_format' => 'required|string',
                'invoices_late_type' => 'required|string|in:percent,amount',
                'invoices_late_amount' => 'required|numeric',
                'invoices_late_minimum' => 'required|numeric',
                'invoices_increment' => 'required|integer|min:1|max:999',
                'invoices_start' => 'nullable|integer|min:10000|max:9999999',
                'credit_use' => 'required|boolean',
                'credit_min_deposit' => 'required|numeric',
                'credit_max_deposit' => 'required|numeric',
                'credit_max' => 'required|numeric',
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
            ]);
            
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
