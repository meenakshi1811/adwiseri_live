<?php

namespace App\Services;

use App\Mail\SupportTicketClosureMail;
use App\Mail\SupportTicketResponseMail;
use App\Models\TicketActivityLog;
use App\Models\Tickets;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketActivityService
{
    public function log(
        Tickets $ticket,
        string $action,
        ?string $detail = null,
        ?User $actor = null,
        array $meta = []
    ): TicketActivityLog {
        return TicketActivityLog::create([
            'ticket_id' => $ticket->id,
            'actor_user_id' => $actor?->id,
            'actor_name' => $actor?->name,
            'action' => $action,
            'detail' => $detail,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }

    public function notifyRaiser(
        Tickets $ticket,
        string $title,
        ?string $body = null,
        ?string $emailSubject = null,
        ?string $emailIntro = null,
        bool $sendEmail = true
    ): void {
        $raiser = $ticket->ticketRaiser();

        if (!$raiser) {
            Log::warning('Support ticket notification skipped: ticket raiser not found.', [
                'ticket_id' => $ticket->id,
                'ticket_no' => $ticket->ticket_no,
                'user_id' => $ticket->user_id,
                'subscriber_id' => $ticket->subscriber_id,
            ]);

            return;
        }

        app(NotificationService::class)->notifyUser(
            $raiser,
            'support_ticket_updates',
            $title,
            $body,
            $ticket->ticketViewRoute(),
            [
                'ticket_id' => $ticket->id,
                'ticket_no' => $ticket->ticket_no,
            ]
        );

        if (!$sendEmail || empty($raiser->email)) {
            return;
        }

        $maildata = $this->mailPayload($ticket, $raiser);

        try {
            if ($emailSubject !== null && $emailIntro !== null) {
                Mail::to($raiser->email)->send(new SupportTicketResponseMail($maildata, $emailSubject, $emailIntro));
            } else {
                Mail::to($raiser->email)->send(new SupportTicketClosureMail($maildata));
            }
        } catch (\Throwable $exception) {
            Log::warning('Support ticket raiser email failed.', [
                'ticket_id' => $ticket->id,
                'email' => $raiser->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function notifyRaiserOfResponse(Tickets $ticket, string $response, User $actor): void
    {
        $this->log(
            $ticket,
            'response_sent',
            $response,
            $actor,
            ['status' => $ticket->status]
        );

        $preview = \Illuminate\Support\Str::limit(strip_tags($response), 160);

        $this->notifyRaiser(
            $ticket,
            'Support ticket #' . ($ticket->ticket_no ?? $ticket->id) . ' — new response',
            $preview,
            'Support Ticket Response (' . ($ticket->ticket_no ?? $ticket->id) . ')',
            'Your support ticket has received a response from our team.',
            true
        );
    }

    public function notifyRaiserOfStatusChange(Tickets $ticket, string $previousStatus, User $actor): void
    {
        $isClosed = strtolower((string) $ticket->status) === 'closed';

        $this->log(
            $ticket,
            $isClosed ? 'closed' : 'reopened',
            'Status changed from ' . $previousStatus . ' to ' . $ticket->status,
            $actor,
            ['status' => $ticket->status]
        );

        if ($isClosed) {
            $this->notifyRaiser(
                $ticket,
                'Support ticket #' . ($ticket->ticket_no ?? $ticket->id) . ' closed',
                'Your support ticket has been closed.',
                null,
                null,
                true
            );

            return;
        }

        $this->notifyRaiser(
            $ticket,
            'Support ticket #' . ($ticket->ticket_no ?? $ticket->id) . ' reopened',
            'Your support ticket has been reopened.',
            'Support Ticket Reopened (' . ($ticket->ticket_no ?? $ticket->id) . ')',
            'Your support ticket has been reopened by our support team.',
            true
        );
    }

    public function logAssignment(Tickets $ticket, User $assignee, User $actor): void
    {
        $this->log(
            $ticket,
            'assigned',
            'Assigned to ' . $assignee->name,
            $actor,
            ['assigned_user_id' => $assignee->id]
        );
    }

    public function logCreation(Tickets $ticket, User $actor): void
    {
        $this->log(
            $ticket,
            'created',
            'Ticket raised by ' . $actor->name,
            $actor,
            ['status' => $ticket->status]
        );
    }

    public function logDeletion(Tickets $ticket, User $actor): void
    {
        $this->log(
            $ticket,
            'deleted',
            'Ticket deleted by ' . $actor->name,
            $actor,
            [
                'ticket_no' => $ticket->ticket_no,
                'status' => $ticket->status,
            ]
        );
    }

    private function mailPayload(Tickets $ticket, User $raiser): \stdClass
    {
        $maildata = new \stdClass();
        $maildata->ticket_id = $ticket->ticket_no ?? $ticket->id;
        $maildata->department = $ticket->support ?? '-';
        $maildata->support = $ticket->support ?? '-';
        $maildata->issue = $ticket->issue ?? '';
        $maildata->response = $ticket->response ?? '';
        $maildata->name = $raiser->name;
        $maildata->ticket_url = route('my_query', $ticket->id);

        return $maildata;
    }
}
