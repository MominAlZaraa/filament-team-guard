<?php

namespace Filament\Jetstream\Livewire\Profile;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Jetstream\Contracts\UpdatesUserProfileInformation as UpdatesUserProfileInformationContract;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UpdateProfileInformation extends BaseLivewireComponent
{
    public ?array $data = [];

    public function mount(): void
    {
        $user = $this->authUser();

        // Get all user attributes - this includes all database columns and accessors
        // Filament will automatically filter to only use fields that exist in the form schema
        $data = $user->toArray();

        // Ensure profile_photo_path is included if it exists
        if (method_exists($user, 'getProfilePhotoPathAttribute') || property_exists($user, 'profile_photo_path')) {
            $data['profile_photo_path'] = $user->profile_photo_path ?? null;
        }

        // Fill the form - Filament will automatically use only fields that match the schema
        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        /** @var UpdatesUserProfileInformationContract $action */
        $action = app(UpdatesUserProfileInformationContract::class);

        $formSchema = $action->getFormSchema();

        // Find the Section component and recreate it with Actions included
        foreach ($formSchema as $index => $component) {
            if ($component instanceof Section) {
                // Get Section properties from the action class methods
                $sectionTitle = method_exists($action, 'getSectionHeading')
                    ? $action->getSectionHeading()
                    : __('filament-team-guard::default.update_profile_information.section.title');
                $sectionDescription = method_exists($action, 'getSectionDescription')
                    ? $action->getSectionDescription()
                    : $component->getDescription();
                $isAside = $component->isAside();

                // Create a new Section with the same configuration
                $newSection = Section::make($sectionTitle)
                    ->description($sectionDescription);

                if ($isAside) {
                    $newSection->aside();
                }

                // Get field components from action and add Actions
                $fieldComponents = $this->getFieldComponentsFromAction($action);
                $fieldComponents[] = Actions::make([
                    Action::make('save')
                        ->label(__('filament-team-guard::default.action.save.label'))
                        ->submit('updateProfile'),
                ]);

                // Set the schema with fields and Actions
                $newSection->schema($fieldComponents);

                // Replace the original Section with the new one
                $formSchema[$index] = $newSection;

                break;
            }
        }

        return $schema
            ->schema($formSchema)
            ->statePath('data');
    }

    /**
     * Get field components from the action class.
     */
    protected function getFieldComponentsFromAction(UpdatesUserProfileInformationContract $action): array
    {
        // Check if action has a method to get just the fields
        if (method_exists($action, 'getFieldComponents')) {
            return $action->getFieldComponents();
        }

        // Otherwise, get the schema and extract fields from the Section
        $formSchema = $action->getFormSchema();
        $section = function_exists('array_first') ? array_first($formSchema) : ($formSchema[0] ?? null);

        if ($section instanceof Section) {
            // Use reflection to get the schema property
            $reflection = new \ReflectionClass($section);

            // Try to get the schema property
            if ($reflection->hasProperty('schema')) {
                $property = $reflection->getProperty('schema');
                $property->setAccessible(true);
                $schemaValue = $property->getValue($section);

                // Handle closure
                if (is_callable($schemaValue) && ! is_array($schemaValue)) {
                    return $schemaValue();
                }

                if (is_array($schemaValue)) {
                    return $schemaValue;
                }
            }
        }

        return [];
    }

    public function updateProfile(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();
        $user = $this->authUser();

        /** @var UpdatesUserProfileInformationContract $action */
        $action = app(UpdatesUserProfileInformationContract::class);
        $action->update($user, $data);

        $this->sendNotification();
    }

    public function render()
    {
        return view('filament-team-guard::livewire.profile.update-profile-information');
    }
}
