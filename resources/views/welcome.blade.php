<x-guest-layout>
    <div class="text-center">
        <h1 class="text-2xl font-semibold text-gray-800">Penyewaan Garasi</h1>
        <p class="mt-2 text-sm text-gray-600">
            Sewa garasi sepeda, mobil, dan mobil listrik secara harian atau bulanan.
        </p>

        <div class="mt-6 flex justify-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md">Login</a>
                <a href="{{ route('register') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md">Register</a>
            @endauth
        </div>
    </div>
</x-guest-layout>