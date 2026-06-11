@php
    $flashTypes = [
        'success' => 'fa-check-circle',
        'error'   => 'fa-exclamation-triangle',
        'warning' => 'fa-exclamation-circle',
        'info'    => 'fa-info-circle',
    ];
    $active = array_filter(array_keys($flashTypes), fn ($k) => session()->has($k));
@endphp

@if (!empty($active))
    <div class="toast-stack" id="toast-stack" aria-live="polite">
        @foreach ($active as $key)
            <div class="toast toast--{{ $key }}" role="status">
                <span class="toast__icon"><i class="fas {{ $flashTypes[$key] }}" aria-hidden="true"></i></span>
                <p class="toast__msg">{!! session($key) !!}</p>
                <button type="button" class="toast__close" aria-label="Cerrar">&times;</button>
            </div>
        @endforeach
    </div>
    <script>
        (function () {
            const stack = document.getElementById('toast-stack');
            if (!stack) return;
            stack.querySelectorAll('.toast').forEach(function (t) {
                const close = function () {
                    t.classList.add('is-leaving');
                    setTimeout(function () { t.remove(); }, 300);
                };
                t.querySelector('.toast__close').addEventListener('click', close);
                setTimeout(close, 5000);
            });
        })();
    </script>
@endif
