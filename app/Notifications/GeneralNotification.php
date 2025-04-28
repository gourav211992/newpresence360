<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Notifications\Channels\CustomDatabaseChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class GeneralNotification extends Notification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return [CustomDatabaseChannel::class];
    }

    public function toDatabase($notifiable)
    {
        return $this->data;
    }

    public function broadcastOn()
    {
        $notifiableId = $this->data['notifiable_id'];
        return new PrivateChannel('user.' . $notifiableId);
    }

    public function toBroadcast($notifiable)
    {
        return $this->data;
    }
}
