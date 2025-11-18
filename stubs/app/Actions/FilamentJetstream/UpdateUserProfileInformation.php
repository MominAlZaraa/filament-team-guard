<?php

namespace App\Actions\FilamentJetstream;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Jetstream\Actions\UpdateUserProfileInformation as BaseUpdateUserProfileInformation;
use Filament\Jetstream\Jetstream;
use Filament\Schemas\Components\Section;

class UpdateUserProfileInformation extends BaseUpdateUserProfileInformation
{
    /**
     * Get the field components for the profile form (without Section wrapper).
     *
     * You can customize the fields here. For example, to add a surname field:
     *
     * public function getFieldComponents(): array
     * {
     *     return [
     *         FileUpload::make('profile_photo_path')
     *             ->label(__('filament-jetstream::default.form.profile_photo.label'))
     *             ->avatar()
     *             ->image()
     *             ->imageEditor()
     *             ->visibility('public')
     *             ->directory('profile-photos')
     *             ->formatStateUsing(fn () => Filament::auth()->user()?->profile_photo_path)
     *             ->disk(fn (): string => Jetstream::plugin()?->profilePhotoDisk())
     *             ->visible(fn (): bool => Jetstream::plugin()?->managesProfilePhotos()),
     *         TextInput::make('name')
     *             ->label(__('filament-jetstream::default.form.name.label'))
     *             ->string()
     *             ->maxLength(255)
     *             ->required(),
     *         TextInput::make('surname')
     *             ->label('Surname')
     *             ->string()
     *             ->maxLength(255),
     *         TextInput::make('email')
     *             ->label(__('filament-jetstream::default.form.email.label'))
     *             ->email()
     *             ->required()
     *             ->unique(get_class(Filament::auth()->user()), ignorable: Filament::auth()->user()),
     *     ];
     * }
     */
}
