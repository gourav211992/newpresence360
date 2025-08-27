<?php



use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;



/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('user.{id}', function ($user, $id) {
//     Log::info('Broadcast auth attempted pp', ['user' => $user]);
//     return (int) $user->id === (int) $id;
// }); // or 'auth:api' if using API tokens

