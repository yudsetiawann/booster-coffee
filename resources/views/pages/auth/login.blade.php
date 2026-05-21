<x-layouts::auth.card :title="__('Masuk ke Sistem')">
    <div class="flex flex-col gap-6">
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf
            <flux:input name="email" :label="__('Email')" :value="old('email')" type="email" required autofocus
                placeholder="email@toko.com" />

            <div class="relative">
                <flux:input name="password" :label="__('Password')" type="password" required viewable
                    placeholder="••••••••" />
                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-xs font-bold end-0 text-amber-700"
                        :href="route('password.request')" wire:navigate>
                        {{ __('Lupa?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Ingat saya')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full bg-amber-600 hover:bg-amber-700">
                {{ __('Masuk') }}
            </flux:button>
        </form>

        <div class="text-center text-sm text-stone-500">
            {{ __('Belum punya akun?') }}
            <flux:link :href="route('register')" wire:navigate class="font-bold text-amber-700">{{ __('Daftar') }}
            </flux:link>
        </div>
    </div>
</x-layouts::auth.card>
