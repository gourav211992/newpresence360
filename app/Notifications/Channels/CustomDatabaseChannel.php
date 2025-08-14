<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;

class CustomDatabaseChannel extends BaseDatabaseChannel
{
  /**
   * Send the given notification.
   *
   * @param  mixed  $notifiable
   * @param  \Illuminate\Notifications\Notification  $notification
   * @return \Illuminate\Database\Eloquent\Model
   */
  public function send($notifiable, $notification)
  {
    return DB::transaction(function () use ($notifiable, $notification) {
      $data = $this->getData($notifiable, $notification);

      $databaseNotification = $notifiable->routeNotificationFor('database', $notification)->create([
        'id' => null, // Let the database assign the ID
        'type' => get_class($notification),
        'data' => $data,
        'read_at' => null,
      ]);

      $this->broadcast($notifiable, $notification, $databaseNotification);

      return $databaseNotification;
    });
  }

  protected function broadcast($notifiable, $notification, $databaseNotification)
  {
    $broadcastData = $this->getData($notifiable, $notification);

    if (method_exists($notification, 'toBroadcast')) {
      $broadcastMessage = $notification->toBroadcast($notifiable);
      if ($broadcastMessage instanceof BroadcastMessage) {
        $broadcastData = $broadcastMessage->data;
      } elseif (is_array($broadcastMessage)) {
        $broadcastData = $broadcastMessage;
      }
    }

    $broadcastEvent = new BroadcastNotificationCreated(
      $notifiable,
      $notification,
      $broadcastData
    );

    // Use the broadcast helper
    broadcast($broadcastEvent);
    //   Broadcast::event($broadcastEvent);
  }

}