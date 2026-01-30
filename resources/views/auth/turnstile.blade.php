@php
    $siteKey = config('turnstile.sitekey');
    $enabled = config('turnstile.enabled', true);
    $fieldName = config('turnstile.field', 'cf-turnstile-response');
    $resetEvent = config('filament-team-guard.turnstile.reset_event', 'filament-team-guard-turnstile-reset');
@endphp

@if ($siteKey && $enabled)
    <div class="fi-sc-field" x-data id="filament-team-guard-turnstile-wrapper">
        @if (session('filament-team-guard.turnstile_error') || session('filament-team-guard.login_error'))
            <p class="fi-fo-field-widget-error-message text-sm text-danger-600 dark:text-danger-400 mt-1" role="alert">
                {{ session('filament-team-guard.turnstile_error') ?? session('filament-team-guard.login_error') }}
            </p>
        @endif
        <input type="hidden" wire:model="turnstileResponse" id="filament-team-guard-turnstile-response"
            name="{{ $fieldName }}" />
        <div id="filament-team-guard-turnstile-container" class="filament-team-guard-turnstile"></div>
    </div>

    <script>
        window.__filamentJetstreamTurnstileSuccess = function(token) {
            var el = document.querySelector('#filament-team-guard-turnstile-container');
            if (!el) return;

            var wire = null;
            var root = el.closest('[wire\\:id]');
            if (root && typeof window.Livewire !== 'undefined') {
                var id = root.getAttribute('wire:id');
                if (id) wire = window.Livewire.find(id);
            }
            if (wire) {
                if (typeof wire.$set === 'function') {
                    wire.$set('turnstileResponse', token);
                } else if (typeof wire.set === 'function') {
                    wire.set('turnstileResponse', token);
                }
            }

            var input = document.getElementById('filament-team-guard-turnstile-response');
            if (input) {
                input.value = token;
                input.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }
        };

        (function() {
            var resetEventName = @json($resetEvent);
            var siteKey = @json($siteKey);
            var widgetId = null;

            function renderTurnstile() {
                var container = document.getElementById('filament-team-guard-turnstile-container');
                if (!container || typeof turnstile === 'undefined') return;
                if (widgetId !== null) return;
                widgetId = turnstile.render('#filament-team-guard-turnstile-container', {
                    sitekey: siteKey,
                    theme: 'auto',
                    callback: window.__filamentJetstreamTurnstileSuccess
                });
            }

            function resetTurnstile() {
                var el = document.querySelector('#filament-team-guard-turnstile-container');
                var root = el && el.closest('[wire\\:id]');
                var id = root && root.getAttribute('wire:id');
                var wire = (id && typeof window.Livewire !== 'undefined') ? window.Livewire.find(id) : null;
                if (wire) {
                    if (typeof wire.$set === 'function') wire.$set('turnstileResponse', '');
                    else if (typeof wire.set === 'function') wire.set('turnstileResponse', '');
                }
                var input = document.getElementById('filament-team-guard-turnstile-response');
                if (input) {
                    input.value = '';
                    input.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }
                if (typeof turnstile !== 'undefined' && widgetId !== null) {
                    turnstile.reset(widgetId);
                }
            }

            window.__filamentJetstreamTurnstileOnLoad = function() {
                var attempts = 0;
                var maxAttempts = 50;

                function tryRender() {
                    var container = document.getElementById('filament-team-guard-turnstile-container');
                    if (container && typeof turnstile !== 'undefined') {
                        renderTurnstile();
                    } else if (typeof turnstile !== 'undefined' && attempts < maxAttempts) {
                        attempts++;
                        setTimeout(tryRender, 100);
                    }
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', tryRender);
                } else {
                    tryRender();
                }
                document.addEventListener('livewire:initialized', tryRender, {
                    once: true
                });
            };

            document.addEventListener('livewire:init', function() {
                Livewire.on(resetEventName, resetTurnstile);
            });
            window.addEventListener(resetEventName, resetTurnstile);

            var authActions = ['authenticate', 'register', 'request', 'resetPassword'];

            function getTurnstileTokenFromDom() {
                var fieldName = @json($fieldName);
                var el = document.querySelector('[name="' + fieldName + '"]') || document.getElementById(
                    'filament-team-guard-turnstile-response');
                return el && el.value ? el.value : '';
            }
            var interceptSetup = false;

            function setupIntercept() {
                if (interceptSetup) return;
                var turnstileEl = document.querySelector('#filament-team-guard-turnstile-container');
                if (!turnstileEl || typeof window.Livewire === 'undefined') return;
                var root = turnstileEl.closest('[wire\\:id]');
                if (!root) return;
                var id = root.getAttribute('wire:id');
                var wire = window.Livewire.find(id);
                if (!wire) return;
                interceptSetup = true;
                authActions.forEach(function(actionName) {
                    if (typeof wire.intercept !== 'function') return;
                    wire.intercept(actionName, function(_ref) {
                        var onSend = _ref.onSend,
                            action = _ref.action;
                        onSend(function() {
                            var token = getTurnstileTokenFromDom();
                            if (token) {
                                action.cancel();
                                if (typeof wire[actionName] === 'function') {
                                    wire[actionName](token);
                                }
                            }
                        });
                    });
                });
            }
            document.addEventListener('livewire:initialized', setupIntercept);
        })();
    </script>
    <script
        src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=__filamentJetstreamTurnstileOnLoad"
        async defer></script>
@endif
