<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perpanjang Sewa</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            {{-- Daftar sewa yang aktif hari ini --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Sewa yang Aktif Hari Ini ({{ today()->format('d-m-Y') }})</h3>

                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Garasi</th>
                            <th>Penyewa</th>
                            <th>Kendaraan</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Bukti Bayar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rentals as $rental)
                            <tr class="border-b">
                                <td class="py-2">{{ $rental->garage->code }}</td>
                                <td>{{ $rental->tenant->name }}</td>
                                <td>{{ $rental->vehicle->brand }} {{ $rental->vehicle->model }}</td>
                                <td>{{ $rental->start_date->format('d-m-Y') }}</td>
                                <td>{{ $rental->end_date->format('d-m-Y') }}</td>
                                <td>
                                    <a href="{{ asset('storage/'.$rental->payment_proof_path) }}" target="_blank" class="underline">Lihat</a>
                                </td>
                                <td>
                                    <a href="{{ route('extensions.index', ['rental_id' => $rental->id]) }}" class="underline">Pilih</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 text-gray-500">Tidak ada sewa yang aktif hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Form perpanjangan untuk sewa yang dipilih --}}
            @if ($selected)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-lg">Perpanjang Garasi {{ $selected->garage->code }}</h3>
                    <p class="text-gray-700">
                        {{ $selected->tenant->name }}, {{ $selected->vehicle->brand }} {{ $selected->vehicle->model }}.
                        Sekarang {{ $selected->start_date->format('d-m-Y') }} sampai {{ $selected->end_date->format('d-m-Y') }},
                        harga Rp {{ number_format($selected->garage->daily_price, 0, ',', '.') }} per hari.
                    </p>

                    <form method="GET" action="{{ route('extensions.index') }}" class="space-y-4 max-w-md">
                        <input type="hidden" name="rental_id" value="{{ $selected->id }}">

                        <div>
                            <x-input-label for="new_end_date" value="Tanggal Selesai Baru" />
                            <x-text-input id="new_end_date" name="new_end_date" type="date" :min="$selected->end_date->copy()->addDay()->toDateString()" class="block mt-1 w-full" :value="old('new_end_date', request('new_end_date'))" required />
                            <x-input-error :messages="$errors->get('new_end_date')" class="mt-2" />
                        </div>

                        <x-primary-button>Hitung Biaya</x-primary-button>
                    </form>

                    @if ($preview)
                        <div class="border-t pt-4 space-y-4">
                            <p class="text-gray-700">
                                Tambahan {{ $preview['added_days'] }} hari
                                ({{ $preview['from']->format('d-m-Y') }} sampai {{ $preview['new_end']->format('d-m-Y') }}).
                                Kekurangan biaya: <span class="font-semibold">Rp {{ number_format($preview['cost'], 0, ',', '.') }}</span>
                            </p>

                            @if ($preview['is_free'])
                                <form method="POST" action="{{ route('extensions.store', $selected) }}" enctype="multipart/form-data" class="space-y-4 max-w-md">
                                    @csrf
                                    <input type="hidden" name="new_end_date" value="{{ $preview['new_end']->toDateString() }}">

                                    <div>
                                        <x-input-label for="payment_proof" value="Bukti Pembayaran (gambar, maksimal 2 MB)" />
                                        <input id="payment_proof" name="payment_proof" type="file" accept="image/*" class="block mt-1" required>
                                        <x-input-error :messages="$errors->get('payment_proof')" class="mt-2" />
                                    </div>

                                    <x-primary-button>Simpan Perpanjangan</x-primary-button>
                                </form>
                            @else
                                <p class="text-red-600">Garasi ini sudah disewa orang lain di hari tambahan tersebut, jadi tidak bisa diperpanjang sampai tanggal itu.</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>