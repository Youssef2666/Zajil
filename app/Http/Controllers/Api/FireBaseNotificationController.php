<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Services\FireBaseService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderDeliveredNotification;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Messaging\Notification;

class FireBaseNotificationController extends Controller
{
    use ResponseTrait;

    public function __construct(protected FireBaseService $fireBaseService)
    {
        
    }
    public function sendNotification(Request $request)
    {
        $title = $request->title;
        $body = $request->body;
        $data = [
            'type' => $request->type,
            'url' => $request->url,
        ];

        $tokens = [
            'ffdsgsd342iho$jcdkfkujdjkfkjjfkwefwejfwjw',
            'ffdsgsd342iho$jcdkfkujdjkfkjjkkpe46efwejfwjw',
        ];

        $notification = $this->storeNotification($title, $body, $data);
        if ($notification) {
            $this->fireBaseService->sendNotificationToAllUserDevices($tokens, $title, $body, $data);
        }
        return $this->success($data); 
    }

    public function storeNotification(string $title, string $body, $image = null, string $type = 'fcm', string $netch = 'Youssef'): Notification
    {
        $user = Auth::user();
        $order = Order::find(1);
        $user->notify(new OrderDeliveredNotification($order));
    }
}
