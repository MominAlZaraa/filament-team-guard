# Changelog

All notable changes to `mominalzaraa/filament-jetstream` will be documented in this file.

This is an enhanced version of [stephenjude/filament-jetstream](https://github.com/stephenjude/filament-jetstream), which itself is inspired by the original [Laravel Jetstream](https://github.com/laravel/jetstream) package.

## v1.0.2 - 2025-12-10

### Enhanced Avatar Generation with Context-Aware Naming

#### Enhanced

- **Context-aware avatar generation** - Default profile photo avatars now use `getFilamentName()` method when available, ensuring avatar initials match the context-aware display names shown throughout the Filament UI
- Avatar generation now respects custom naming logic (e.g., "Mr. John Doe", "Ms. Jane Doe") instead of using only the raw `name` column
- Falls back gracefully to `$this->name` if `getFilamentName()` method doesn't exist, maintaining backward compatibility

#### Technical Details

- `HasProfilePhoto::defaultProfilePhotoUrl()` now checks for `getFilamentName()` method before generating avatar initials
- Avatar initials are generated from the full context-aware name (including titles/prefixes)
- Maintains compatibility with models that don't implement `getFilamentName()`

#### Benefits

- **Consistency** - Avatar initials now match the display names shown in Filament UI
- **Context-Aware** - Supports applications with custom naming logic (e.g., gender-based titles, role-based prefixes)
- **Backward Compatible** - Works seamlessly with existing implementations
- **Better UX** - Users see consistent naming across all UI elements

#### Example

For a user with `getFilamentName()` returning "Mr. John Doe":

- **Before:** Avatar shows initials "JD" (from `name` column: "John Doe")
- **After:** Avatar shows initials "MJD" (from context-aware name: "Mr. John Doe")

This ensures the avatar reflects the same context-aware naming used throughout the application.

## v1.0.3 - 2026-01-01

### Fixed Team Name Update Serialization Issue

#### Fixed
- **UpdateTeamName Livewire Component** - Fixed issue where updating team name attempted to INSERT a new team instead of UPDATE existing team
  - Root cause: Team model lost its "exists" state after Livewire serialization, causing `forceFill()->save()` to attempt INSERT
  - Solution: Store `teamId` separately as a public property to avoid serialization issues
  - Solution: Use `findOrFail($teamId)` to get fresh instance from database before updating
  - Solution: Use `update()` method instead of `forceFill()->save()` to ensure UPDATE operation

- **UpdateTeamName Action Class** - Applied same fix for consistency
  - Uses `findOrFail()` to get fresh team instance
  - Uses `update()` method to ensure UPDATE operation

#### Technical Details
- Team model serialization through Livewire can cause the model to lose its "exists" state
- Storing team ID separately as a simple integer property ensures reliable serialization
- Always refreshing from database using `findOrFail()` ensures we have a valid existing record
- Using `update()` method is safer than `forceFill()->save()` for updating existing records

#### Impact
- Team name updates now correctly UPDATE existing teams instead of attempting to INSERT
- Prevents database errors: "Field 'user_id' doesn't have a default value"
- Prevents 404 errors when team model loses its state after serialization 1b5c00c2 (Fix: Team name update serialization issue (v1.0.2))

## v1.0.1 - 2025-01-18

### Enhanced Team Invitation Acceptance & Improved Action Customization

#### Added

- `AcceptsTeamInvitations` contract interface for team invitation acceptance
- `AcceptTeamInvitation` base action class implementing the contract
- `AcceptTeamInvitation` publishable stub with commented default code examples
- Automatic custom action discovery for team invitation acceptance
- Enhanced all action stubs with commented default code examples

#### Enhanced

- `HasTeamsFeatures` trait now uses contract binding for team invitation acceptance
- `JetstreamServiceProvider` registers `AcceptsTeamInvitations` contract
- All action stubs include comprehensive commented examples showing customization patterns
- Improved code consistency and documentation across all stubs

#### Improved

- Team invitation acceptance is now fully customizable like other actions
- Backward compatible with closure-based customization
- Cleaner stub format with generic examples (no project-specific code)
- Better developer experience with ready-to-use code snippets

#### Technical Details

- Contract-based architecture for team invitation acceptance
- Automatic discovery of custom `AcceptTeamInvitation` action
- Falls back to default behavior if custom action doesn't exist
- All stubs follow consistent formatting and include required imports

## v1.0.0 - 2025-11-18

### Release Notes - Enhanced Version

#### 🎉 Major Release: Enhanced Filament Jetstream

This release represents a **significant enhancement** over the previous `stephenjude/filament-jetstream` package, bringing back all the powerful features from the original Laravel Jetstream package with mature data handling patterns and seamless Filament UI integration.


---

#### 📦 What's New

##### ✨ Enhanced Features

###### 1. **Complete Team Management System**

- ✅ **Add Team Members** - Invite users via email with role assignment
- ✅ **Remove Team Members** - Remove members with proper authorization checks
- ✅ **Update Team Member Roles** - Change roles dynamically with validation
- ✅ **Team Deletion Validation** - Prevents accidental deletion of personal teams
- ✅ **Proper Team Invitation Flow** - Users must register/login to accept invitations (aligned with Jetstream's mature handling)

###### 2. **Contract-Based Architecture**

All actions now use contracts/interfaces, matching Laravel Jetstream's architecture:

- `UpdatesUserProfileInformation` - Profile updates
- `InvitesTeamMembers` - Team invitations
- `AddsTeamMembers` - Adding team members
- `RemovesTeamMembers` - Removing team members (NEW)
- `CreatesTeams` - Team creation
- `UpdatesTeamNames` - Team name updates
- `DeletesTeams` - Team deletion
- `DeletesUsers` - User deletion

###### 3. **Publishable Action Classes**

All action classes are now publishable for complete customization:

```bash
php artisan vendor:publish --tag=filament-jetstream-actions


```
**Available Action Stubs:**

- `UpdateUserProfileInformation.php` - Customize profile fields and section metadata
- `InviteTeamMember.php` - Customize team invitations with role validation
- `AddTeamMember.php` - Customize adding team members
- `RemoveTeamMember.php` - Customize removing team members (NEW)
- `CreateTeam.php` - Customize team creation
- `UpdateTeamName.php` - Customize team updates
- `DeleteTeam.php` - Customize team deletion
- `DeleteUser.php` - Customize user deletion with team handling

###### 4. **Enhanced Profile Field Customization**

New methods in `UpdateUserProfileInformation` action:

- `getFieldComponents()` - Returns form fields without Section wrapper (easier to customize)
- `getSectionHeading()` - Customize section title
- `getSectionDescription()` - Customize section description

**Example: Adding a surname field**

```php
public function getFieldComponents(): array
{
    return [
        // ... existing fields ...
        TextInput::make('surname')
            ->label(__('filament-jetstream::default.form.surname.label'))
            ->required(),
    ];
}


```
###### 5. **Publishable Language Files**

Language files now publish to `lang/{locale}/filament-jetstream.php` for better locale organization:

```bash
php artisan vendor:publish --tag=filament-jetstream-lang


```
**Features:**

- Automatic merging with package translations
- Custom translations override package defaults
- Support for multiple locales (en, fr, es, el, etc.)
- Easy to add custom fields and translations

###### 6. **Enhanced Email Templates**

- Conditional "Create Account" button (if registration is enabled)
- Aligned messaging with Laravel Jetstream
- Publishable for customization:

```bash
php artisan vendor:publish --tag=filament-jetstream-email-templates


```
###### 7. **Custom Validation Rules**

- `Filament\Jetstream\Rules\Role` - Validates team roles (matches Jetstream's Role rule)

###### 8. **Improved Team Invitation Flow**

**Previous behavior:** Auto-registered new users (incorrect)
**New behavior:** Users must register/login to accept invitations (correct, aligned with Jetstream)

**How it works:**

1. User receives invitation email
2. If not registered → Redirected to registration (if enabled)
3. If registered but not logged in → Redirected to login
4. After authentication → Invitation automatically accepted
5. Session-based invitation ID storage for security


---

#### 🔧 Technical Improvements

##### Package Structure

- ✅ Updated ownership to **Momin Al Zaraa**
- ✅ Added `PLUGIN_INFO.json` for Filament directory integration
- ✅ Added plugin banner image
- ✅ Updated all GitHub workflows and references
- ✅ Added `FUNDING.yml` for GitHub Sponsors

##### Code Quality

- ✅ PHPStan level increased to **5** (highest level)
- ✅ Simplified PHPStan configuration (resolved memory issues)
- ✅ All workflows tested and verified
- ✅ Code style improvements (Laravel Pint)

##### Workflow Improvements

- ✅ Fixed unstaged changes handling in CI workflows
- ✅ Updated PHP version requirements to `^8.3|^8.4`
- ✅ Improved test workflows to match localization package


---

#### 📊 Comparison with Previous Version

| Feature | Previous (`stephenjude/filament-jetstream`) | This Enhanced Version |
|---------|---------------------------------------------|----------------------|
| **Team Member Management** | Basic (add only) | Complete (add, remove, update roles) |
| **Invitation Flow** | Auto-registration (incorrect) | Register/login required (correct) |
| **Action Classes** | Limited publishability | Fully publishable with contracts |
| **Profile Customization** | Hardcoded fields | Dynamic with `getFieldComponents()` |
| **Language Files** | Vendor path only | Locale-first structure with auto-merge |
| **Email Templates** | Basic | Enhanced with conditional buttons |
| **Validation Rules** | Standard Laravel | Custom Role rule (Jetstream pattern) |
| **Team Deletion** | Basic | With validation (prevents personal team deletion) |
| **Contracts** | Partial | Complete contract-based architecture |
| **Data Handling** | Simple | Mature patterns aligned with Jetstream |


---

#### 🚀 Migration Guide

##### From `stephenjude/filament-jetstream`

1. **Update Composer**
   
   ```bash
   composer remove stephenjude/filament-jetstream
   composer require nominalzaraa/filament-jetstream
   
   
   ```
2. **Publish New Components**
   
   ```bash
   php artisan vendor:publish --tag=filament-jetstream-actions
   php artisan vendor:publish --tag=filament-jetstream-lang
   php artisan vendor:publish --tag=filament-jetstream-email-templates
   
   
   ```
3. **Update Language Files**
   
   - Old location: `lang/vendor/filament-jetstream/{locale}/default.php`
   - New location: `lang/{locale}/filament-jetstream.php`
   - Custom translations will automatically merge
   
4. **Team Invitation Flow Changes**
   
   - **Important:** The invitation flow now requires users to register/login
   - If you had custom invitation handling, review and update accordingly
   - Session-based invitation ID storage is now used
   
5. **Custom Action Classes**
   
   - If you published action classes, they should still work
   - Consider updating to use new methods like `getFieldComponents()`
   


---

#### ⚠️ Breaking Changes

##### 1. Team Invitation Flow

**Previous:** Auto-registered users when accepting invitations
**New:** Users must register/login first

**Impact:** Existing invitation links will redirect to registration/login if user is not authenticated.

##### 2. Language File Location

**Previous:** `lang/vendor/filament-jetstream/{locale}/default.php`
**New:** `lang/{locale}/filament-jetstream.php`

**Migration:** Copy your custom translations to the new location. The custom translation loader will automatically merge them.

##### 3. PHP Version Requirement

**Previous:** PHP ^8.2|^8.3|^8.4
**New:** PHP ^8.3|^8.4

**Impact:** PHP 8.2 is no longer supported.


---

#### 🎯 Key Benefits

1. **Mature Data Handling** - Aligned with Laravel Jetstream's proven patterns
2. **Complete Customization** - All components are publishable and customizable
3. **Better Developer Experience** - Contract-based architecture for easy extension
4. **Improved Security** - Proper invitation flow prevents unauthorized access
5. **Enhanced Flexibility** - Easy to add custom fields, translations, and behaviors
6. **Production Ready** - Tested workflows, PHPStan level 5, comprehensive error handling


---

#### 📝 Credits

**Enhanced by:** Momin Al Zaraa
**Based on:** [stephenjude/filament-jetstream](https://github.com/stephenjude/filament-jetstream)
**Inspired by:** [Laravel Jetstream](https://github.com/laravel/jetstream) (discontinued)


---

#### 🔗 Resources

- **Repository:** https://github.com/MominAlZaraa/filament-jetstream
- **Documentation:** See README.md for detailed usage instructions
- **Issues:** https://github.com/MominAlZaraa/filament-jetstream/issues
- **Support:** support@mominpert.com


---

#### 🙏 Acknowledgments

Special thanks to:

- **Stephen Jude** - Original Filament Jetstream implementation
- **Laravel Team** - Original Jetstream package and framework
- **Filament Team** - Amazing Filament framework


---

**Version:** 1.0.0
**Release Date:** November 18, 2025
**PHP Requirement:** ^8.3|^8.4
**Laravel Requirement:** ^12.0
**Filament Requirement:** ^4.0

## Enhanced Version - 2025-01-XX

### What's Enhanced

This enhanced version by **Momin Al Zaraa** brings complete Jetstream features and mature data handling patterns:

* ✅ Complete team management features (add, remove, update roles)
* ✅ Proper invitation flow (register to accept, not auto-registration)
* ✅ Publishable Action classes with contracts (Jetstream pattern)
* ✅ Publishable language files with locale-first structure
* ✅ Enhanced email templates matching Jetstream's UI
* ✅ Custom validation rules (Role rule matching Jetstream)
* ✅ Better data handling aligned with Jetstream's mature patterns
* ✅ Contract-based architecture matching Laravel Jetstream
* ✅ Enhanced team member management (UpdateTeamMemberRole, RemoveTeamMember)
* ✅ Team deletion validation (ValidateTeamDeletion)
* ✅ Custom translation loader with automatic merging

### Credits

This enhanced package builds upon:

- **Laravel Jetstream** (discontinued): Original inspiration for team features and Action class patterns
- **stephenjude/filament-jetstream**: Original Filament port
- **Enhanced by**: Momin Al Zaraa - Complete Jetstream features and patterns

**Repository**: https://github.com/MominAlZaraa/filament-jetstream


---

## Previous Version History (from stephenjude/filament-jetstream)

## 1.2.11 - 2025-10-13

### What's Changed

* chore(phpstan): update configuration to use supported methods only by @MominAlZaraa in https://github.com/stephenjude/filament-jetstream/pull/77
* chore(phpstan): replace PHPStan with Larastan for enhanced built-in features by @MominAlZaraa in https://github.com/stephenjude/filament-jetstream/pull/78
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/83
* fixed Larastan dev dependency by @stephenjude in https://github.com/stephenjude/filament-jetstream/pull/84

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.10...1.2.11

## 1.2.10 - 2025-10-10

### What's Changed

* Added phpstan for code editing to fix action phpstan.yml action by @MominAlZaraa in https://github.com/stephenjude/filament-jetstream/pull/76

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.9...1.2.10

## 1.2.9 - 2025-10-05

### What's Changed

* Fix: DeleteAccount flow & replace deprecated modal form usage by @momin-00 in https://github.com/stephenjude/filament-jetstream/pull/75

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.6...1.2.9

## 1.2.8 - 2025-10-01

### What's Changed

* Revert: Added tenant slug for friendly URL #71

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.5...1.2.8

## 1.2.1 - 2025-09-01

### What's Changed

* Update php version in  phpstan.yml by @wotta in https://github.com/stephenjude/filament-jetstream/pull/53

### New Contributors

* @wotta made their first contribution in https://github.com/stephenjude/filament-jetstream/pull/53

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.0...1.2.1

## 1.2.0 - 2025-08-31

### What's Changed

* Guide for installing and configuring existing Laravel applications by @stephenjude in https://github.com/stephenjude/filament-jetstream/pull/52

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.0.1...1.2.0

## 1.0.1 - 2025-08-29

### What's Changed

* php artisan optimize compatible form formats by @momin-00 in https://github.com/stephenjude/filament-jetstream/pull/51

### New Contributors

* @momin-00 made their first contribution in https://github.com/stephenjude/filament-jetstream/pull/51

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.0.0...1.0.1

## 0.1.0 - 2025-08-16

### What's Changed

* Use translatable labels by @stephenjude in https://github.com/stephenjude/filament-jetstream/pull/23
* Bump dependabot/fetch-metadata from 2.2.0 to 2.4.0 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/35
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/37
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/36
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/40

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/0.0.14...0.1.0

## 0.0.16 - 2025-03-05

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/stephenjude/filament-jetstream/pull/28
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/stephenjude/filament-jetstream/pull/29
* Add support for Laravel 12. by @LucaPipolo in https://github.com/stephenjude/filament-jetstream/pull/30

### New Contributors

* @LucaPipolo made their first contribution in https://github.com/stephenjude/filament-jetstream/pull/30

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/0.0.15...0.0.16

## 0.0.15 - 2024-11-27

- Make Team Settings label translate reactive by @zvizvi in #24

## 0.0.14 - 2024-11-16

- Use translatable label by @zvizvi in #23

## 0.0.13 - 2024-08-03

- Fixed duplicate team creation during user registration

## 0.0.12 - 2024-06-20

- Fixed user profile URL bug

## 0.0.11 - 2024-06-12

- Fixed Laravel 11 User model scaffold bug

## 0.0.10 - 2024-05-14

- Fixes current_team_id not being updated by @tomhatzer

## 0.0.9 - 2024-04-18

- Use `ServiceProvider::addProviderToBootstrapFile` from L11

## 0.0.8 - 2024-04-16

- Laravel 11 support by @gpibarra

## 0.0.7 - 2024-03-09

- Check features on profile edit by @fabpl
- Check gate on delete team @fabpl
- Removed undefined filament guard by @stephenjude

## 0.0.6 - 2024-03-02

- Added stubs
- Make package removable.
- Clean up.

## 0.0.5 - 2024-03-02

- Scaffold filament assets.

## 0.0.4 - 2024-02-29

- Fixed installation error.

## 0.0.3 - 2024-02-29

- Fixed: Install jetstream from command line.

## 0.0.2 - 2024-02-29

- Fixed class not found

## 0.0.1 - 2024-02-29

- Initial release.

## 1.0.0 - 202X-XX-XX

- initial release
