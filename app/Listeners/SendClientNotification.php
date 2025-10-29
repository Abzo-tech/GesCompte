<?php

namespace App\Listeners;

use App\Events\SendClientNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendClientNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SendClientNotification $event): void
    {
        $client = $event->client;

        try {
            // Send email notification
            if (in_array($event->type, ['email', 'both'])) {
                $this->sendEmailNotification($client);
            }

            // Send SMS notification
            if (in_array($event->type, ['sms', 'both'])) {
                $this->sendSmsNotification($client);
            }

            Log::info('Notifications sent successfully for client: ' . $client->id);

        } catch (\Exception $e) {
            Log::error('Failed to send notifications for client ' . $client->id . ': ' . $e->getMessage());
            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Send email notification to client
     */
    private function sendEmailNotification($client): void
    {
        // For now, just log the email that would be sent
        // In production, you would use Mail::to() with a proper Mailable class
        Log::info('Email notification would be sent to: ' . $client->email . ' with password: ' . $client->password);

        // Example of how it would work with a Mailable:
        // Mail::to($client->email)->send(new ClientAccountCreated($client));
    }

    /**
     * Send SMS notification to client
     */
    private function sendSmsNotification($client): void
    {
        // For now, just log the SMS that would be sent
        // In production, you would integrate with an SMS service like Twilio, Africa's Talking, etc.
        Log::info('SMS notification would be sent to: ' . $client->telephone . ' with code: ' . $client->code_verification);

        // Example of how it would work:
        // $smsService = app(SmsService::class);
        // $smsService->send($client->telephone, "Votre code de vérification est: " . $client->code_verification);
    }
}
