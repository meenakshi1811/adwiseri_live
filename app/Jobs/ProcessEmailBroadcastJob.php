<?php

namespace App\Jobs;

use App\Mail\EmailBroadcastMail;
use App\Models\Email_broadcasts;
use App\Models\User;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessEmailBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public int $tries = 3;

    public function __construct(
        public int $broadcastId,
        public int $offset = 0
    ) {
        $this->onQueue('email-broadcasts');
    }

    public function handle(): void
    {
        $broadcast = Email_broadcasts::find($this->broadcastId);

        if (!$broadcast || in_array($broadcast->status, ['completed', 'failed', 'cancelled'], true)) {
            return;
        }

        if ($this->offset === 0) {
            $broadcast->update([
                'status' => 'processing',
                'started_at' => $broadcast->started_at ?? now(),
            ]);
        }

        $recipients = $broadcast->recipient_payload ?? [];
        $chunkSize = max(1, (int) config('mail.broadcast_chunk_size', 300));
        $chunk = array_slice($recipients, $this->offset, $chunkSize);

        if ($chunk === []) {
            $this->markCompleted($broadcast);

            return;
        }

        $content = BrandedMail::formatBroadcastBody($broadcast->body);
        $subscriberFooter = null;

        if (!empty($broadcast->subscriber_id)) {
            $subscriber = User::find($broadcast->subscriber_id);
            if ($subscriber) {
                $subscriberFooter = BrandedMail::subscriberFooterContext($subscriber);
            }
        }

        $sent = 0;
        $failed = 0;

        foreach ($chunk as $index => $recipient) {
            $email = trim((string) ($recipient['email'] ?? ''));

            if ($email === '') {
                continue;
            }

            try {
                Mail::to($email)->send(new EmailBroadcastMail(
                    $broadcast->subject,
                    $content,
                    $subscriberFooter,
                    $this->offset === 0 && $index === 0,
                    $broadcast->sender_email,
                    $broadcast->sender_name
                ));
                $sent++;
            } catch (\Throwable $exception) {
                $failed++;
                Log::warning('Email broadcast send failed.', [
                    'broadcast_id' => $broadcast->broadcast_id,
                    'email' => $email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $broadcast->increment('sent_count', $sent);
        $broadcast->increment('failed_count', $failed);

        $nextOffset = $this->offset + $chunkSize;

        if ($nextOffset < count($recipients)) {
            $delaySeconds = max(0, (int) config('mail.broadcast_chunk_delay_seconds', 2));

            self::dispatch($this->broadcastId, $nextOffset)
                ->delay(now()->addSeconds($delaySeconds));

            return;
        }

        $this->markCompleted($broadcast->fresh());
    }

    public function failed(\Throwable $exception): void
    {
        $broadcast = Email_broadcasts::find($this->broadcastId);

        if (!$broadcast) {
            return;
        }

        $broadcast->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }

    private function markCompleted(Email_broadcasts $broadcast): void
    {
        $broadcast->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
