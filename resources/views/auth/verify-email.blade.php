<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-center">
            <h2 class="text-xl font-semibold text-gray-800">Verifikasi Email</h2>
        </div>

        <div class="mb-4 text-sm text-gray-600 text-center">
            Sebelum melanjutkan, silakan verifikasi alamat email Anda dengan mengklik tautan yang telah kami kirimkan ke <strong>{{ Auth::user()->email }}</strong>.
        </div>

        @if (session('message'))
            <div class="mb-4 font-medium text-sm text-green-600 text-center">
                {{ session('message') }}
            </div>
        @endif

        @if (session('resent'))
            <div class="mb-4 font-medium text-sm text-green-600 text-center">
                Tautan verifikasi baru telah dikirim ke alamat email Anda.
            </div>
        @endif

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600 text-center">
                Tautan verifikasi baru telah dikirim ke alamat email Anda.
            </div>
        @endif

        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="text-sm text-blue-800">
                <strong>📧 Cek email Anda!</strong><br>
                • Periksa folder Inbox<br>
                • Jika tidak ada, cek folder Spam/Junk<br>
                • Klik tautan verifikasi dalam email
            </div>
        </div>

        <div class="mt-6 space-y-4">
            <form method="POST" action="{{ route('verification.send') }}" class="text-center">
                @csrf
                <x-button type="submit" class="w-full">
                    Kirim Ulang Email Verifikasi
                </x-button>
            </form>

            <div class="text-center">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>
