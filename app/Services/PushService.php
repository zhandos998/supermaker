<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class PushService
{
    public function sendToDevice($deviceToken, $title, $body)
    {
        $messaging = (new Factory)
            ->withServiceAccount(config('firebase.credentials.file'))
            ->createMessaging();

        $message = [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
        ];

        $messaging->send($message);
    }
}
