<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Default Laravel user channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
 * Private chat channel: conversation.{userA}_{userB}
 * The conversation ID is always the two user IDs sorted ascending joined by "_".
 * e.g. users 3 and 7 → "conversation.3_7"
 *
 * Only the two participants may subscribe.
 */
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $ids = array_map('intval', explode('_', $conversationId));
    return in_array($user->id, $ids);
});
