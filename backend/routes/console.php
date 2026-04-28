<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('borrowings:send-reminders')->dailyAt('08:00');
