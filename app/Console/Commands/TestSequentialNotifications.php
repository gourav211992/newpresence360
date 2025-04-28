<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\GeneralNotification;

class TestSequentialNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send email to user upcomming payment reminder.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::first();
        $additionalData = [
          'user_id' => 1,
          'user_type' => 'employee',
          'username' => "name",
          'request_id' => 1,
          'request_table' => 'erp_legals',
          'title' => 'Assignment Notification',
          'message' => "Dear name, a new Request (Request ID: 1) has been assigned to you. Please review the details and take the necessary action. Thank you for your prompt attention.",
      ];

        for ($i = 0; $i < 10; $i++) {
            $user->notify(new GeneralNotification($additionalData));
        }
        $this->info('Notifications created.');
    }

}
