<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-center">
            <h2 class="text-xl font-semibold text-gray-800">Login</h2>
            <p class="text-sm text-gray-600">Masuk ke Aurora Beauty Salon</p>
        </div>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div>
                <x-label for="email" value="Email" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" 
                         :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="Password" />
                <x-input id="password" class="block mt-1 w-full" type="password" 
                         name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-6">
                <div class="text-sm">
                    <a href="{{ route('register') }}" 
                       class="text-indigo-600 hover:text-indigo-500 underline">
                        Belum punya akun? Daftar
                    </a>
                </div>

                <x-button class="ml-3">
                    Masuk
                </x-button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <p class="text-xs text-gray-500">
                Sistem akan otomatis mengarahkan Anda ke dashboard yang sesuai berdasarkan role akun.
            </p>
        </div>
    </x-authentication-card>
</x-guest-layout>