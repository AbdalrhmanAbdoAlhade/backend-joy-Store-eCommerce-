<?php
// app/Jobs/SendFcmPushNotification.php
namespace App\Jobs;

use App\Models\User;
use App\Models\DeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFcmPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $tokens = $this->user->deviceTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $serverKey = config('services.fcm.server_key');

        foreach ($tokens as $token) {
            try {
                Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'to' => $token,
                    'notification' => [
                        'title' => $this->title,
                        'body' => $this->body,
                        'sound' => 'default',
                    ],
                    'data' => $this->data,
                ]);
            } catch (\Exception $e) {
                Log::warning('FCM push failed', [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
