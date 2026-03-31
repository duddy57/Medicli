<?php

declare(strict_types = 1);

namespace App\Notifications\Clinicas;

use App\Models\ClinicaInvitation as ClinicaInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicaInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ClinicaInvitationModel $invitation)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $Clinica = $this->invitation->Clinica;
        $inviter = $this->invitation->inviter;

        return (new MailMessage())
            ->subject("You've been invited to join " . $Clinica->name)
            ->line(sprintf('%s has invited you to join the %s Clinica.', $inviter->name, $Clinica->name))
            ->action('Accept invitation', url(sprintf('/invitations/%s/accept', $this->invitation->code)));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'Clinica_id'    => $this->invitation->Clinica_id,
            'Clinica_name'  => $this->invitation->Clinica->name,
            'role'          => $this->invitation->role->value,
        ];
    }
}
