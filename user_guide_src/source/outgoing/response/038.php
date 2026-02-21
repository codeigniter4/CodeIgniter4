<?php

use App\Models\NotificationModel;
use CodeIgniter\HTTP\SSEResponse;

$user_id = session()->get('user_id');

return new SSEResponse(static function (SSEResponse $sse) use ($user_id) {
    // Stream live notifications for the current user
    $notificationModel = model(NotificationModel::class);

    $lastId = 0;

    // In a real app, you would typically keep the connection open indefinitely
    for ($i = 0; $i < 6; $i++) {
        $order = $lastId === 0 ? 'desc' : 'asc';

        // On the first pass, pick the newest notification
        // After that, stream any newer ones in order
        $notification = $notificationModel->where('user_id', $user_id)
            ->where('id >', $lastId)
            ->orderBy('id', $order)
            ->first();

        if ($notification !== null) {
            $lastId = (int) $notification['id'];

            if (! $sse->event($notification, 'notification', (string) $lastId)) {
                break;
            }
        } else {
            // No new notifications yet: send a keep-alive comment
            if (! $sse->comment('keep-alive')) {
                break;
            }
        }

        // Poll every 10 seconds
        sleep(10);
    }

    // Ask the browser to retry in 60 seconds if the connection closes
    $sse->retry(60000);
});
