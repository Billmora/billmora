<?php

use App\Console\Commands\RunAutomationCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RunAutomationCommand::class)->everyMinute();