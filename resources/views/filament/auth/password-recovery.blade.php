<div class="tcse-recovery">
    <div class="tcse-recovery__header">
        <div class="tcse-recovery__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M7 10V8a5 5 0 0 1 10 0v2M6 10h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                <path d="M12 14v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
        </div>
        <h1>{{ $this->getHeading() }}</h1>
        <p>{{ $this->getSubheading() }}</p>
    </div>

    @if ($step === 'request')
        <form wire:submit="requestCode" class="tcse-recovery__form">
            <label class="tcse-recovery__field">
                <span>Correo electrónico</span>
                <input wire:model="identifier" type="email" required
                    class="tcse-recovery__control"
                    placeholder="nombre@dominio.com">
                @error('identifier') <span class="tcse-recovery__error">{{ $message }}</span> @enderror
            </label>

            <button type="submit" class="tcse-recovery__button">
                Enviar código
            </button>
        </form>
    @elseif ($step === 'verify')
        <form wire:submit="verifyCode" class="tcse-recovery__form">
            <label class="tcse-recovery__field">
                <span>Código de 6 dígitos</span>
                <input wire:model="code" inputmode="numeric" maxlength="6" required
                    class="tcse-recovery__control tcse-recovery__code">
                @error('code') <span class="tcse-recovery__error">{{ $message }}</span> @enderror
            </label>
            <button type="submit" class="tcse-recovery__button">Verificar código</button>
        </form>
    @else
        <form wire:submit="resetPassword" class="tcse-recovery__form">
            <label class="tcse-recovery__field">
                <span>Nueva contraseña</span>
                <input wire:model="password" type="password" required class="tcse-recovery__control">
                @error('password') <span class="tcse-recovery__error">{{ $message }}</span> @enderror
            </label>
            <label class="tcse-recovery__field">
                <span>Confirmar nueva contraseña</span>
                <input wire:model="password_confirmation" type="password" required class="tcse-recovery__control">
            </label>
            <button type="submit" class="tcse-recovery__button">Cambiar contraseña</button>
        </form>
    @endif

    <a href="{{ $this->loginUrl() }}" class="tcse-recovery__back">
        Volver al inicio de sesión
    </a>
</div>
