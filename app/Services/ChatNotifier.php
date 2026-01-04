<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatNotifier
{
    /**
     * Kirim pesan WhatsApp via WhatsApp Cloud API.
     * Ganti/extend sesuai provider lain (SMS/email) bila perlu.
     */
    public function send(string $phone, string $message): bool
    {
        $token   = env('WHATSAPP_TOKEN');
        $phoneId = env('WHATSAPP_PHONE_ID');

        if (empty($token) || empty($phoneId)) {
            Log::warning('ChatNotifier missing token/phoneId', ['phone' => $phone]);
            return false;
        }

        $to = $this->sanitizePhone($phone);

        try {
            $resp = Http::withToken($token)
                ->acceptJson()
                ->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ]);

            if ($resp->successful()) {
                return true;
            }

            Log::error('ChatNotifier send failed', [
                'phone'   => $to,
                'status'  => $resp->status(),
                'body'    => $resp->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('ChatNotifier exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function sanitizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        return $digits;
    }
}
