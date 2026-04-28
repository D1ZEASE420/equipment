<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDueDateReminders extends Command
{
    protected $signature   = 'borrowings:send-reminders';
    protected $description = 'Saada meeldetuletused homme tähtajaga laenutuste kohta';

    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $borrowings = Borrowing::with('device')
            ->whereNull('returned_at')
            ->whereDate('due_date', $tomorrow)
            ->whereNotNull('student_email')
            ->get();

        foreach ($borrowings as $borrowing) {
            $deviceName = $borrowing->device?->name ?? 'seade';
            $dueDate    = Carbon::parse($borrowing->due_date)->format('d.m.Y');
            $dueTime    = $borrowing->due_time ?? '08:30';

            try {
                Mail::raw(
                    "Tere {$borrowing->student_name},\n\nMeeldetuletus: palun too laenutatud seade \"{$deviceName}\" tagasi homme {$dueDate} kell {$dueTime}.\n\nDisainimajakas",
                    function ($message) use ($borrowing, $deviceName) {
                        $message->to($borrowing->student_email, $borrowing->student_name)
                                ->subject("Meeldetuletus: homme on tagastustähtaeg — \"{$deviceName}\"")
                                ->from(config('mail.from.address'), 'Disainimajakas');
                    }
                );
                $this->info("Saadetud: {$borrowing->student_email}");
            } catch (\Exception $e) {
                $this->error("Viga {$borrowing->student_email}: " . $e->getMessage());
            }
        }

        $this->info("Valmis. {$borrowings->count()} meeldetuletust saadetud.");
    }
}