<?php

namespace App\Filament\Pages\Admin\Settings;

use App\Services\BillmoraService as Billmora;
use App\Mail\NotificationMail;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail as Mailer;

class Mail extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'tabler-mail';
    protected static string $view = 'filament.pages.admin.settings.mail';
    protected static ?string $slug = 'settings/mail';
    protected ?string $subheading = 'Configure a mail settings.';

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
                    Forms\Components\Tabs\Tab::make('mailer')
                        ->label('Mailer')
                        ->icon('tabler-mail-cog')
                        ->schema($this->tabMailer()),
                    Forms\Components\Tabs\Tab::make('mail-template')
                        ->label('Mail Template')
                        ->icon('tabler-mail-code')
                        ->schema($this->tabMailTemplate()),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(EmailTemplate::query())
            ->columns([
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Email Template')
                    ->modalButton('Update')
                    ->successNotificationTitle('Email Template have been updated successfully.')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('Key')
                                    ->disabled(),
                                Forms\Components\TextInput::make('name')
                                    ->label('Name')
                                    ->disabled(),
                            ]),
                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->required(),
                        TiptapEditor::make('body')
                            ->label('Body')
                            ->required(),
                        Forms\Components\Toggle::make('status')
                            ->label('Enable Email')
                            ->required(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TagsInput::make('cc')
                                    ->label('CC Emails')
                                    ->nullable()
                                    ->placeholder('')
                                    ->separator(','),
                                Forms\Components\TagsInput::make('bcc')
                                    ->label('BCC Emails')
                                    ->nullable()
                                    ->placeholder('')
                                    ->separator(','),
                            ]),
                        Forms\Components\Textarea::make('placeholder')
                            ->label('List available placeholder')
                            ->autosize()
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ?: <<<'PLACEHOLDER'
                            Client Name = {name}
                            Global Signature = {signature}
                            PLACEHOLDER
                            ),
                    ]),
            ])
            ->defaultPaginationPageOption(50);
    }

    private function tabMailer()
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
                        ->action(function () {
                            try {
                                $user = auth()->user();
                                Mailer::to($user->email)->send(new NotificationMail('test_message', [
                                    'name' => auth()->user()->name,
                                    'signature' => nl2br(e(Billmora::getGeneral('mail_template_signature', "Regards,\nBillmora"))),
                                ]));
            
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
                        })
                )
                ->helperText('The mail driver (or mailer) determines how Billmora sends emails. You can configure it to use SMTP, Sendmail, Mailgun.')
                ->default(env('MAIL_MAILER', 'smtp')),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('mail_from_address')
                        ->label('Mail From Address')
                        ->required()
                        ->email()
                        ->helperText('Enter an email address that all outgoing emails will originate from.')
                        ->default(env('MAIL_FROM_ADDRESS', 'hello@example.com')),
                        Forms\Components\TextInput::make('mail_from_name')
                        ->label('Mail From Name')
                        ->required()
                        ->helperText('The name that emails should appear to come from.')
                        ->default(env('MAIL_FROM_NAME', 'Billmora')),
                ]),
            Forms\Components\Section::make('SMTP Configuration')
                ->columns()
                ->visible(fn (Forms\Get $get) => $get('mail_driver') === 'smtp')
                ->schema([
                    Forms\Components\TextInput::make('mail_host')
                        ->label('Host')
                        ->required()
                        ->helperText('Enter the SMTP server address that mail should be sent through.')
                        ->default(env('MAIL_HOST')),
                    Forms\Components\TextInput::make('mail_port')
                        ->label('Port')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(65535)
                        ->helperText('Enter the SMTP server port that mail should be sent through.')
                        ->default(env('MAIL_PORT')),
                    Forms\Components\TextInput::make('mail_username')
                        ->label('Username')
                        ->required()
                        ->helperText('The username to use when connecting to the SMTP server.')
                        ->default(env('MAIL_USERNAME')),
                    Forms\Components\TextInput::make('mail_password')
                        ->label('Password')
                        ->required()
                        ->password()
                        ->revealable()
                        ->helperText('The password to use in conjunction with the SMTP username. ')
                        ->default(env('MAIL_PASSWORD')),
                    Forms\Components\ToggleButtons::make('mail_encryption')
                        ->label('Encryption')
                        ->required()
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
                        })
                        ->helperText('Select the type of encryption to use when sending mail.')
                        ->default(env('MAIL_ENCRYPTION')),  
                ]),
            Forms\Components\Section::make('Mailgun Configuration')
                ->columns(3)
                ->visible(fn (Forms\Get $get) => $get('mail_driver') === 'mailgun')
                ->schema([
                    Forms\Components\TextInput::make('mail_mailgun_domain')
                        ->label('Domain')
                        ->suffixIcon('tabler-world')
                        ->required()
                        ->helperText('The Mailgun Domain is the domain registered with Mailgun.')
                        ->default(env('MAILGUN_DOMAIN')),
                    Forms\Components\TextInput::make('mail_mailgun_secret')
                        ->label('Secret')
                        ->password()
                        ->revealable()
                        ->required()
                        ->helperText('The Mailgun Secret is the API key used to authenticate.')
                        ->default(env('MAILGUN_SECRET')),
                    Forms\Components\TextInput::make('mail_mailgun_endpoint')
                        ->label('Endpoint')
                        ->suffixIcon('tabler-world')
                        ->required()
                        ->helperText('The Mailgun Endpoint specifies the API base URL.')
                        ->default(env('MAILGUN_ENDPOINT')),
                ]),
        ];
    }

    private function tabMailTemplate()
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Textarea::make('mail_template_signature')
                        ->label('Global Signature')
                        ->rows(2)
                        ->columnSpan(2)
                        ->helperText('The Mail Global Signature is a predefined signature that will be appended to all outgoing emails.')
                        ->default(Billmora::getMail('mail_template_signature', "Regards,\nBillmora")),
                    Forms\Components\Select::make('mail_template')
                        ->label('Email Template')
                        ->options(collect(File::directories(resource_path('themes/email')))
                            ->mapWithKeys(fn ($path) => [
                                basename($path) => basename($path)
                            ])
                            ->toArray())
                        ->native(false)
                        ->required()
                        ->helperText('The template you want Billmora mail to use.')
                        ->default(Billmora::getMail('mail_template', 'default')),
                ])
        ];
    }

    public function save(): void
    {
        try {
            $validated = Validator::make($this->data, [
                'mail_driver' => ['required', 'string', 'in:smtp,mailgun,sendmail'],
                'mail_from_address' => ['required', 'email'],
                'mail_from_name' => ['required', 'string'],
                'mail_host' => ['required_if:mail_driver,smtp', 'string'],
                'mail_port' => ['required_if:mail_driver,smtp', 'integer', 'between:1,65535'],
                'mail_username' => ['nullable', 'string'],
                'mail_password' => ['nullable', 'string'],
                'mail_encryption' => ['nullable', 'string', 'in:tls,ssl'],
                'mail_mailgun_domain' => ['required_if:mail_driver,mailgun', 'string'],
                'mail_mailgun_secret' => ['required_if:mail_driver,mailgun', 'string'],
                'mail_mailgun_endpoint' => ['required_if:mail_driver,mailgun', 'string'],
                
                'mail_template' => ['required', 'string'],
                'mail_template_signature' => ['required'],
            ])->validate();
    
            Billmora::setEnv([
                'MAIL_MAILER' => $validated['mail_driver'],
                'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
                'MAIL_FROM_NAME' => $validated['mail_from_name'],
                'MAIL_HOST' => $validated['mail_host'],
                'MAIL_PORT' => $validated['mail_port'],
                'MAIL_USERNAME' => $validated['mail_username'],
                'MAIL_PASSWORD' => $validated['mail_password'],
                'MAIL_ENCRYPTION' => $validated['mail_encryption'],
                'MAILGUN_DOMAIN' => $validated['mail_mailgun_domain'],
                'MAILGUN_SECRET' => $validated['mail_mailgun_secret'],
                'MAILGUN_ENDPOINT' => $validated['mail_mailgun_endpoint'],
            ]);

            Billmora::setMail([
                'mail_template' => $validated['mail_template'],
                'mail_template_signature' => $validated['mail_template_signature'],
            ]);

            Notification::make()
                ->title('Success')
                ->body('Mail settings have been updated successfully.')
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
