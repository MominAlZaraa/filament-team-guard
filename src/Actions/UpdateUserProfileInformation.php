<?php

namespace Filament\Jetstream\Actions;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Jetstream\Contracts\UpdatesUserProfileInformation;
use Filament\Jetstream\Jetstream;
use Filament\Models\Contracts\FilamentUser;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Arr;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Get the section heading/title.
     */
    public function getSectionHeading(): string
    {
        return __('filament-team-guard::default.update_profile_information.section.title');
    }

    /**
     * Get the section description.
     */
    public function getSectionDescription(): ?string
    {
        return __('filament-team-guard::default.update_profile_information.section.description');
    }

    /**
     * Get the field components for the profile form (without Section wrapper).
     */
    public function getFieldComponents(): array
    {
        return [
            FileUpload::make('profile_photo_path')
                ->label(__('filament-team-guard::default.form.profile_photo.label'))
                ->avatar()
                ->image()
                ->imageEditor()
                ->visibility('public')
                ->directory('profile-photos')
                ->formatStateUsing(fn () => Filament::auth()->user()?->profile_photo_path)
                ->disk(fn (): string => Jetstream::plugin()?->profilePhotoDisk())
                ->visible(fn (): bool => Jetstream::plugin()?->managesProfilePhotos()),
            TextInput::make('name')
                ->label(__('filament-team-guard::default.form.name.label'))
                ->string()
                ->maxLength(255)
                ->required(),
            TextInput::make('email')
                ->label(__('filament-team-guard::default.form.email.label'))
                ->email()
                ->required()
                ->unique(get_class(Filament::auth()->user()), ignorable: Filament::auth()->user()),
        ];
    }

    /**
     * Get the form schema for profile information update.
     */
    public function getFormSchema(): array
    {
        return [
            Section::make($this->getSectionHeading())
                ->aside()
                ->description($this->getSectionDescription())
                ->schema($this->getFieldComponents()),
        ];
    }

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(FilamentUser $user, array $input): void
    {
        $isUpdatingEmail = ($input['email'] ?? null) !== $user->email;
        $isUpdatingPhoto = isset($input['profile_photo_path']) && $input['profile_photo_path'] !== $user->profile_photo_path;

        $user->forceFill(Arr::except($input, ['profile_photo_path']))->save();

        if ($isUpdatingEmail && $user instanceof MustVerifyEmail) {
            $user->forceFill(['email_verified_at' => null])->save();
            $user->sendEmailVerificationNotification();
        }

        if ($isUpdatingPhoto) {
            if (Arr::get($input, 'profile_photo_path')) {
                $user->updateProfilePhoto($input['profile_photo_path']);
            } else {
                $user->deleteProfilePhoto();
            }
        }
    }
}
