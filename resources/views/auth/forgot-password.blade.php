<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-center">
            <h2 class="text-xl font-semibold text-gray-800">Lupa Password?</h2>
            <p class="text-sm text-gray-600 mt-2">
                Masukkan alamat email Anda dan kami akan mengirimkan link untuk reset password.
            </p>
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600 text-center p-3 bg-green-50 border border-green-200 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="Email" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" 
                         :value="old('email')" required autofocus autocomplete="username" 
                         placeholder="Masukkan alamat email Anda" />
            </div>

            <div class="mt-6">
                <x-button class="w-full justify-center">
                    Kirim Link Reset Password
                </x-button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" 
               class="text-sm text-indigo-600 hover:text-indigo-500 underline">
                Kembali ke Login
            </a>
        </div>
    </x-authentication-card>
</x-guest-layout>
