@php
    $panel = \Filament\Facades\Filament::getCurrentPanel();
    $loginUrl = $panel ? $panel->route('passkeys.login') : '#';
    $optionsUrl = $panel ? $panel->route('passkeys.authentication_options') : '#';
@endphp
<div>
    <form id="passkey-login-form" method="POST" action="{{ $loginUrl }}">
        @csrf
    </form>

    @if ($message = session()->get('authenticatePasskey::message'))
        <div
            class="fi-notification relative flex w-full gap-x-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 shadow-sm dark:border-red-500/20 dark:bg-red-500/10">
            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
        </div>
    @endif

    <div x-data x-on:click="authenticateWithPasskey()" role="button" tabindex="0"
        class="fi-btn relative grid-flow-col items-center justify-center gap-1.5 rounded-lg font-semibold outline-none transition duration-75 focus-visible:ring-2 fi-btn-color-gray fi-btn-size-sm fi-btn-outlined mt-3 w-full cursor-pointer fi-ac-action fi-ac-btn-action gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-transparent border border-gray-200 dark:border-white/10 text-gray-950 dark:text-white hover:bg-gray-50 dark:hover:bg-white/5 fi-outlined">
        <x-filament::icon icon="heroicon-o-finger-print" class="fi-btn-icon h-5 w-5" />
        <span class="fi-btn-label">
            {{ __('passkeys::passkeys.authenticate_using_passkey') }}
        </span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.authenticateWithPasskey = async function(remember = false) {
                const optionsUrl = @js($optionsUrl);
                const response = await fetch(optionsUrl);
                const options = await response.json();

                if (typeof startAuthentication !== 'function') {
                    console.error('Passkey authentication is not available in this browser.');
                    return;
                }

                const startAuthenticationResponse = await startAuthentication({
                    optionsJSON: options
                });
                const form = document.getElementById('passkey-login-form');

                form.addEventListener('formdata', function({
                    formData
                }) {
                    formData.set('remember', remember);
                    formData.set('start_authentication_response', JSON.stringify(
                        startAuthenticationResponse));
                }, {
                    once: true
                });

                form.submit();
            };
        });
    </script>
</div>
