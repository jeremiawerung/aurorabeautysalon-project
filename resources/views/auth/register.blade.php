<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />
         <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Nama') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="nama" :value="old('nama')" required autofocus />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            </div>

            <!-- Kolom nomor telepon -->
            <div class="mt-4">
                <x-label for="nomor_telepon" value="{{ __('Nomor Telepon') }}" />
                <x-input id="nomor_telepon" class="block mt-1 w-full" type="text" name="nomor_telepon" :value="old('nomor_telepon')" required />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Konfirmasi Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            </div>

            <x-button class="mt-4">
                {{ __('Daftar') }}
            </x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>
