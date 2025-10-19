<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-center">
            <h2 class="text-xl font-semibold text-gray-800">Reset Password</h2>
            <p class="text-sm text-gray-600 mt-2">
                Masukkan email dan password baru Anda.
            </p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="block">
                <x-label for="email" value="Email" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" 
                         :value="old('email', $email ?? '')" required autofocus autocomplete="username" readonly />
            </div>

            <div class="mt-4">
                <x-label for="password" value="Password Baru" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" 
                         required autocomplete="new-password" placeholder="Masukkan password baru" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="Konfirmasi Password" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" 
                         name="password_confirmation" required autocomplete="new-password" 
                         placeholder="Ulangi password baru" />
            </div>

            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="text-sm text-blue-800">
                    <strong>Password harus:</strong><br>
                    • Minimal 8 karakter<br>
                    • Kombinasi huruf dan angka disarankan
                </div>
            </div>

            <div class="mt-6">
                <x-button class="w-full justify-center">
                    Reset Password
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
