<?php

/**
 * Filament Jetstream Language File
 *
 * This file contains all translations for the Filament Jetstream package.
 * When published, this file will be located at: lang/{locale}/filament-team-guard.php
 * (e.g., lang/en/filament-team-guard.php, lang/fr/filament-team-guard.php)
 *
 * PUBLISHING:
 * -----------
 * To customize translations, publish this file:
 * php artisan vendor:publish --tag=filament-team-guard-lang
 *
 * The published file will be at: lang/{locale}/filament-team-guard.php
 * This locale-first structure makes it easier to manage multiple languages.
 *
 * ADDING CUSTOM FIELDS:
 * ---------------------
 * When you add custom fields to your profile form (like 'surname', 'phone', etc.),
 * you can add their translations here in the 'form' section:
 *
 * Example:
 * 'form' => [
 *     'name' => ['label' => 'Name'],
 *     'surname' => ['label' => 'Surname'],  // Add your custom field
 *     'phone' => ['label' => 'Phone Number'],  // Add another custom field
 *     'email' => ['label' => 'Email'],
 *     // ... other fields
 * ],
 *
 * Then use it in your Action class:
 * TextInput::make('surname')
 *     ->label(__('filament-team-guard::default.form.surname.label'))
 *
 * CUSTOMIZING EXISTING TRANSLATIONS:
 * -----------------------------------
 * Simply modify any value below to change the displayed text.
 * The package will automatically use your published translations from
 * lang/{locale}/filament-team-guard.php, falling back to package translations
 * for any missing keys.
 *
 * ADDING NEW LANGUAGES:
 * ---------------------
 * 1. Publish the language files
 * 2. Copy lang/en/filament-team-guard.php to lang/your-locale/filament-team-guard.php
 * 3. Translate all values
 * 4. The package will automatically use the correct locale
 *
 * FILE STRUCTURE:
 * --------------
 * - 'form' => Form field labels and hints
 * - 'action' => Action button labels and descriptions
 * - 'notification' => Success/error messages
 * - 'page' => Page titles and descriptions
 * - 'mail' => Email content
 * - 'table' => Table column labels
 * - 'section' => Section titles and descriptions
 */

return [
    'form' => [

        'name' => [
            'label' => 'Name',
        ],

        'team_owner' => [
            'label' => 'Team Owner',
        ],

        'email' => [
            'label' => 'Email',
        ],

        'password' => [

            'label' => 'Password',

            'error_message' => 'The provided password was incorrect.',

        ],

        'code' => [

            'label' => 'Code',

            'hint' => 'Please confirm access to your account by entering the authentication code provided by your authenticator application.',

            'error_message' => 'The provided two factor authentication code is invalid.',

        ],

        'profile_photo' => [
            'label' => 'Photo',
        ],

        'current_password' => [
            'label' => 'Current Password',
        ],

        'new_password' => [
            'label' => 'New Password',
        ],

        'confirm_password' => [
            'label' => 'Confirm Password',
        ],

        'recovery_code' => [

            'label' => 'Recovery Code',

            'hint' => 'Please confirm access to your account by entering one of your emergency recovery codes.',

        ],

        'token_name' => [
            'label' => 'Token Name',
        ],

        'permissions' => [
            'label' => 'Permissions',
        ],

        'team_name' => [
            'label' => 'Team Name',
        ],

        'or' => [
            'label' => 'Or ',
        ],

    ],

    'table' => [

        'columns' => [

            'token_name' => [
                'label' => 'Tokens',
            ],

            'pending_invitations' => [
                'label' => 'Pending Invitations',
            ],

            'team_members' => [
                'label' => 'Members',
            ],

            'role' => [
                'label' => 'Role',
            ],

        ],

    ],

    'notification' => [

        'save' => [

            'success' => [
                'message' => 'Saved.',
            ],

        ],

        'create_token' => [

            'success' => [
                'message' => 'Please copy your new API token. For your security, it won\'t be shown again.',
            ],

            'error' => [
                'message' => 'Select at least one permission.',
            ],

        ],

        'copy_token' => [

            'success' => [
                'message' => 'copied to clipboard',
            ],

        ],

        'token_deleted' => [

            'success' => [
                'message' => 'Token deleted!',
            ],

        ],

        'team_deleted' => [

            'success' => [
                'message' => 'Team deleted!',
            ],

        ],

        'team_member_removed' => [
            'success' => [
                'message' => 'You have removed this team member.',
            ],
        ],

        'team_invitation_sent' => [
            'success' => [
                'message' => 'Team invitation sent.',
            ],
        ],

        'team_invitation_cancelled' => [
            'success' => [
                'message' => 'Team invitation cancelled.',
            ],
        ],

        'leave_team' => [

            'success' => [
                'message' => 'You have left the team.',
            ],

        ],

        'accepted_invitation' => [

            'success' => [

                'title' => 'Team Invitation Accepted',

                'message' => 'Great! You have accepted the invitation to join the :team team.',

            ],
        ],

        'rate_limited' => [

            'title' => 'Too many requests',

            'message' => 'Please try again in :seconds seconds',

        ],

        'logged_out_other_sessions' => [

            'success' => [
                'message' => 'All other browser sessions have been logged out successfully.',
            ],

        ],

        'permission_denied' => [

            'cannot_update_team_member' => 'You do not have permission to update this team member.',

            'cannot_leave_team' => 'You may not leave a team that you created.',

            'cannot_remove_team_member' => 'You do not have permission to remove this team member.',

            'cannot_delete_team' => 'You do not have permission to delete this team.',

        ],
    ],

    'action' => [

        'save' => [
            'label' => 'Save',
        ],

        'confirm' => [
            'label' => 'Confirm',
        ],

        'cancel' => [
            'label' => 'Cancel',
        ],

        'disable' => [
            'label' => 'Disable',
        ],

        'enable' => [
            'label' => 'Enable',
        ],

        'two_factor_authentication' => [

            'label' => [

                'regenerate_recovery_codes' => 'Regenerate Recovery Codes',

                'use_recovery_code' => 'use a recovery code',

                'use_authentication_code' => 'use an authentication code',

                'logout' => 'Logout',

            ],

        ],

        'update_token' => [

            'title' => 'API Token Permissions',

            'label' => 'Permissions',

            'modal' => [
                'label' => 'Save',
            ],

        ],

        'delete_token' => [

            'title' => 'Delete API Token',

            'description' => 'Are you sure you would like to delete this API token?',

            'label' => 'Remove',

        ],

        'delete_account' => [

            'label' => 'Delete Account',

            'notice' => 'Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',

            'success_title' => 'Account Deleted',

        ],

        'delete_team' => [

            'label' => 'Delete Team',

            'notice' => 'Are you sure you want to delete this team? Once a team is deleted, all of its resources and data will be permanently deleted.',

        ],

        'create_token' => [
            'label' => 'Create Token',
        ],

        'copy_token' => [
            'label' => 'Copy',
        ],

        'copy_token_confirm' => [

            'label' => 'Confirm Copied',
        ],

        'add_team_member' => [

            'label' => 'Add',

            'error_message' => [

                'email_already_joined' => 'This user already belongs to the team.',

                'email_not_found' => 'We were unable to find a registered user with this email address.',

                'email_already_invited' => 'This user has already been invited to the team.',

            ],
        ],

        'update_team_role' => [
            'title' => 'Manage Role',
        ],

        'remove_team_member' => [

            'label' => 'Remove',

            'notice' => 'Are you sure you would like to remove this team member?',
        ],

        'leave_team' => [

            'label' => 'Leave',

            'notice' => 'Are you sure you would like to leave this team?',
        ],

        'resend_team_invitation' => [
            'label' => 'Resend',
        ],

        'cancel_team_invitation' => [
            'label' => 'Cancel',
        ],

        'log_out_other_browsers' => [

            'label' => 'Log Out Other Browser Sessions',

            'title' => 'Log Out Other Browser Sessions',

            'description' => 'Enter your password to confirm you would like to log out of your other browser sessions across all of your devices.',

        ],

    ],

    'mail' => [

        'team_invitation' => [

            'subject' => 'Team Invitation',

            'message' => [
                'invitation' => 'You have been invited to join the :team team!',

                'instruction' => 'Click the button below to accept the invitation and get started:',

                'notice' => 'If you did not expect to receive an invitation to this team, you may discard this email.',
            ],

            'label' => [

                'create_account' => 'Create Account',

                'accept_invitation' => 'Accept Invitation',

            ],

        ],

    ],

    'page' => [

        'create_team' => [

            'title' => 'Create Team',

        ],

        'edit_team' => [

            'title' => 'Team Settings',

        ],

    ],

    'menu_item' => [

        'api_tokens' => [
            'label' => 'API Tokens',
        ],

    ],

    'profile_photo' => [],

    'update_profile_information' => [

        'section' => [

            'title' => 'Profile Information',

            'description' => 'Update your account\'s profile information and email address.',

        ],

    ],

    'update_password' => [

        'section' => [

            'title' => 'Update Password',

            'description' => 'Ensure your account is using a long, random password to stay secure.',

        ],

    ],

    'two_factor_authentication' => [

        'section' => [

            'title' => 'Two Factor Authentication',

            'description' => 'Add additional security to your account using two factor authentication.',

        ],

    ],

    'delete_account' => [

        'section' => [

            'title' => 'Delete Account',

            'description' => 'Permanently delete your account.',

            'notice' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',

        ],

    ],

    'create_api_token' => [

        'section' => [

            'title' => 'Create API Token',

            'description' => 'API tokens allow third-party services to authenticate with our application on your behalf.',

        ],

    ],

    'manage_api_tokens' => [

        'section' => [

            'title' => 'Manage API Tokens',

            'description' => 'You may delete any of your existing tokens if they are no longer needed.',

        ],

    ],

    'browser_sessions' => [

        'section' => [

            'title' => 'Browser Sessions',

            'description' => 'Manage and log out your active sessions on other browsers and devices.',

            'notice' => 'If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.',

            'labels' => [

                'current_device' => 'This device',

                'last_active' => 'Last active',

                'unknown_device' => 'Unknown',

            ],

        ],

    ],

    'create_team' => [

        'section' => [

            'title' => 'Create Team',

        ],

    ],

    'update_team_name' => [

        'section' => [

            'title' => 'Team Name',

            'description' => 'The team\'s name and owner information.',

        ],

    ],

    'add_team_member' => [

        'section' => [

            'title' => 'Add Team Member',

            'description' => 'Add a new team member to your team, allowing them to collaborate with you.',

            'notice' => 'Please provide the email address of the person you would like to add to this team.',

        ],

    ],

    'team_members' => [

        'section' => [

            'title' => 'Team Members',

            'description' => 'All of the people that are part of this team.',

        ],

    ],

    'pending_team_invitations' => [

        'section' => [

            'title' => 'Pending Team Invitations',

            'description' => 'These people have been invited to your team and have been sent an invitation email. They may join the team by accepting the email invitation.',

        ],

    ],

    'delete_team' => [

        'section' => [

            'title' => 'Delete Team',

            'description' => 'Permanently delete this team.',

            'notice' => 'Once a team is deleted, all of its resources and data will be permanently deleted. Before deleting this team, please download any data or information that you wish to retain.',

        ],

    ],

];
