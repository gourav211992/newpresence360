<?php

namespace App\Notifications;

use Illuminate\Broadcasting\Channel;
use Illuminate\Notifications\Notification;
use App\Helpers\Helper;
use App\Models\User;
use App\Models\Legal;
use App\Notifications\Channels\CustomDatabaseChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Pusher\Pusher;
use Illuminate\Broadcasting\PrivateChannel;

class LegalNotification extends Notification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Determine the channels the notification will be sent on.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        try {
            return [CustomDatabaseChannel::class];
        } catch (\Exception $e) {
            Log::error('Error in via method: ' . $e->getMessage(), [
                'data' => $this->data
            ]);
            throw $e;  // Re-throw the exception after logging
        }
    }

    /**
     * Get the data that will be stored in the database.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        try {
            return $this->data;
        } catch (\Exception $e) {
            Log::error('Error in toDatabase method: ' . $e->getMessage(), [
                'notifiable_id' => $notifiable->id,
                'data' => $this->data
            ]);
            throw $e;  // Re-throw the exception after logging
        }
    }

    /**
     * Get the channels the notification will be broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        try {
            $notifiableId = $this->data['notifiable_id'];
            return new PrivateChannel('user.' . $notifiableId);
        } catch (\Exception $e) {
            Log::error('Error in broadcastOn method: ' . $e->getMessage(), [
                'data' => $this->data
            ]);
            throw $e;  // Re-throw the exception after logging
        }
    }

    /**
     * Get the data that will be broadcast.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toBroadcast($notifiable)
    {
        try {
            Log::info('Broadcasting event to user.' . $this->data['notifiable_id'], [
                'data' => $this->data,
                'channel' => 'user.' . $this->data['notifiable_id']  // Log the channel
            ]);
            return $this->data;
        } catch (\Exception $e) {
            Log::error('Error in toBroadcast method: ' . $e->getMessage(), [
                'notifiable_id' => $this->data['notifiable_id'],
                'data' => $this->data
            ]);
            throw $e;  // Re-throw the exception after logging
        }
    }


    /**
     * Get the data that will be broadcasted with the notification.
     *
     * @return array
     */


    /**
     * Optionally, you can add more methods like toMail, toSMS, etc., if you want to send notifications via other channels.
     */
}
