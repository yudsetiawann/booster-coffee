<x-layouts::auth.card :title="__('Daftar Akun')">
    <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
        @csrf
        <flux:input name="name" :label="__('Nama Lengkap')" :value="old('name')" type="text" required autofocus
            placeholder="Nama Anda" />

        <flux:input name="email" :label="__('Email')" :value="old('email')" type="email" required
            placeholder="email@toko.com" />

        <flux:input name="password" :label="__('Password')" type="password" required viewable placeholder="••••••••" />

        <flux:input name="password_confirmation" :label="__('Konfirmasi Password')" type="password" required
            placeholder="••••••••" />

        <flux:button type="submit" variant="primary" class="w-full bg-amber-600 hover:bg-amber-700">
            {{ __('Buat Akun') }}
        </flux:button>

        <div class="text-center text-sm text-stone-500">
            {{ __('Sudah punya akun?') }}
            <flux:link :href="route('login')" wire:navigate class="font-bold text-amber-700">{{ __('Masuk') }}
            </flux:link>
        </div>
    </form>
</x-layouts::auth.card>
