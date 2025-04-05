<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
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
                <p>This is a test email to verify the configuration.</p>
                {signature}
                BODY,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['key' => $template['key']],
                [
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                ]
            );
        }
    }
}
