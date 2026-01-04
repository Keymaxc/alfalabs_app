<?php

namespace App\Jobs;

use App\Models\CustomerMessage;
use App\Services\ChatNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SendCustomerChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private CustomerMessage $message)
    {
        //
    }

    public function handle(ChatNotifier $notifier): void
    {
        $phone = $this->message->phone;
        $body  = $this->message->message;

        if (! $phone || ! $body) {
            $this->message->update([
                'status' => 'failed',
                'error'  => 'Phone or message empty',
            ]);
            return;
        }

        try {
            $sent = $notifier->send($phone, $body);
            $this->message->update([
                'status'  => $sent ? 'sent' : 'failed',
                'sent_at' => now(),
                'error'   => $sent ? null : 'Send returned false',
            ]);
        } catch (\Throwable $e) {
            Log::error('SendCustomerChat failed', ['id' => $this->message->id, 'error' => $e->getMessage()]);
            $this->message->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }
}
