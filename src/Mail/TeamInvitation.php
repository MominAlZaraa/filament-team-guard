<?php

namespace Filament\Jetstream\Mail;

use Filament\Jetstream\Jetstream;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TeamInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The team invitation instance.
     */
    public Model $invitation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Model $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $panel = Jetstream::panel();
        $path = __('filament.:path.team-invitations.accept', [
            'path' => $panel->getId(),
        ]);

        $acceptUrl = URL::signedRoute($path, [
            'invitation' => $this->invitation,
        ]);

        $canRegister = $panel->hasRegistration();
        $registerUrl = $canRegister ? $panel->getRegistrationUrl() : null;

        return $this->subject(__('Team Invitation'))
            ->markdown('filament-jetstream::emails.team-invitation', [
                'acceptUrl' => $acceptUrl,
                'teamName' => $this->invitation->team?->name,
                'canRegister' => $canRegister,
                'registerUrl' => $registerUrl,
            ]);
    }
}
