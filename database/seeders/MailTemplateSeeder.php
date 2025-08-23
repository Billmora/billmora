<?php

namespace Database\Seeders;

use App\Models\MailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'test_message',
                'name' => 'Test Message',
                'subject' => 'Welcome to Billmora!',
                'body' => <<<'BODY'
                <p>Hello, {name}!</p>
                <br />
                <p>This is a test email to verify the configuration.</p>
                <br />
                <p>Best Regards</p>
                <p>Billmora</p>
                BODY,
                'placeholder' => [
                    'name' => 'Client name',
                    'signature' => 'Global signature',
                ],
            ],
        ];

        foreach ($templates as $template) {
            MailTemplate::updateOrCreate(
                ['key' => $template['key']],
                [
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'placeholder' => $template['placeholder'],
                ]
            );
        }
    }
}
