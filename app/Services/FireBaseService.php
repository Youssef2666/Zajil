<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FireBaseService
{
    protected $messaging;
    public function __construct()
    {
        $this->messaging = (new Factory)
        ->withServiceAccount(config('firebase.credentials'))
        ->createMessaging(); 
    }

    public function sendNotificationToAllUserDevices(array $tokens, string $title, string $body, array $data = [], $imageUrl = null)
    {
        try {
            $notifications = Notification::create($title, $body)->withImageUrl($imageUrl);
            $message = CloudMessage::new()->withNotification($notifications);
            $this->messaging->sendMulticast($message, $tokens);
        }catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}