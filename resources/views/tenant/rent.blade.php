<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sewa Garasi Baru</h2>
    </x-slot>

    @php
        $vehicleLabels = ['bicycle' => 'Sepeda', 'motorcycle' => 'Sepeda Motor', 'car' => 'Mobil', 'electric_car' => 'Mobil Listrik'];
        $garageLabels = ['bike' => 'Garasi Sepeda', 'car' => 'Garasi Mobil', 'electric' => 'Garasi Mobil Listrik'];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            {{-- Tahap 1: isi data sewa lalu cari garasi --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">1. Data Sewa</h3>

                @if ($vehicles->isEmpty())
                    <p class="text-gray-600">
                        Kamu belum punya kendaraan. Tambahkan dulu di
                        <a href="{{ route('vehicles.index') }}" class="underline">Kendaraan Saya</a>.
                    </p>
                @else
                                        <form method="GET" action="{{ route('rent.create') }}" class="space-y-4 max-w-md"
                        x-data="{ electric: false, check(select) { this.electric = select.selectedOptions[0].dataset.type === 'electric_car' } }">
                        <div>
                            <x-input-label for="vehicle_id" value="Kendaraan" />
                            <select id="vehicle_id" name="vehicle_id" x-init="check($el)" x-on:change="check($el)" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" data-type="{{ $vehicle->type }}" @selected(old('vehicle_id', request('vehicle_id')) == $vehicle->id)>
                                        {{ $vehicleLabels[$vehicle->type] }}: {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->plate_number ? '('.$vehicle->plate_number.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="start_date" value="Tanggal Mulai" />
                            <x-text-input id="start_date" name="start_date" type="date" :min="today()->toDateString()" class="block mt-1 w-full" :value="old('start_date', request('start_date', today()->toDateString()))" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="rental_type" value="Tipe Sewa" />
                            <select id="rental_type" name="rental_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="daily" @selected(old('rental_type', request('rental_type')) === 'daily')>Harian</option>
                                <option value="monthly" @selected(old('rental_type', request('rental_type')) === 'monthly')>Bulanan (1 bulan)</option>
                            </select>
                            <x-input-error :messages="$errors->get('rental_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="days" value="Jumlah Hari (khusus harian, maksimal jumlah hari di bulan mulai dikurangi 1)" />
                            <x-text-input id="days" name="days" type="number" min="1" class="block mt-1 w-full" :value="old('days', request('days'))" />
                            <x-input-error :messages="$errors->get('days')" class="mt-2" />
                        </div>

                        <template x-if="electric">
                            <div>
                                <x-input-label for="battery_remaining_kwh" value="Sisa Baterai (kWh)" />
                                <x-text-input id="battery_remaining_kwh" name="battery_remaining_kwh" type="number" step="0.1" min="0" class="block mt-1 w-full" :value="old('battery_remaining_kwh', request('battery_remaining_kwh'))" />
                                <x-input-error :messages="$errors->get('battery_remaining_kwh')" class="mt-2" />
                            </div>
                        </template>

                        <x-primary-button>Cari Garasi</x-primary-button>
                    </form>
                @endif
            </div>

            {{-- Tahap 2: pilih garasi, lihat biaya, upload bukti bayar --}}
            @if ($search)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">2. Pilih Garasi</h3>
                    <p class="mb-4 text-gray-700">
                        {{ $search['days'] }} hari, {{ $search['start']->format('d-m-Y') }} sampai {{ $search['end']->format('d-m-Y') }}
                    </p>

                    <form method="POST" action="{{ route('rent.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="vehicle_id" value="{{ request('vehicle_id') }}">
                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="rental_type" value="{{ request('rental_type') }}">
                        <input type="hidden" name="days" value="{{ request('days') }}">
                        <input type="hidden" name="battery_remaining_kwh" value="{{ request('battery_remaining_kwh') }}">

                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Pilih</th>
                                    <th>Kode</th>
                                    <th>Tipe Garasi</th>
                                    <th>Biaya Sewa</th>
                                    <th>Biaya Listrik</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($search['options'] as $option)
                                    <tr class="border-b">
                                        <td class="py-2">
                                            <input type="radio" name="garage_id" value="{{ $option['garage']->id }}" required>
                                        </td>
                                        <td>{{ $option['garage']->code }}</td>
                                        <td>{{ $garageLabels[$option['garage']->type] }}</td>
                                        <td>Rp {{ number_format($option['rent'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($option['charging'], 0, ',', '.') }}</td>
                                        <td class="font-semibold">Rp {{ number_format($option['total'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-gray-500">Tidak ada garasi yang tersedia di tanggal itu.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <x-input-error :messages="$errors->get('garage_id')" class="mt-2" />

                        <div class="max-w-md">
                            <x-input-label for="payment_proof" value="Bukti Pelunasan (gambar, maksimal 2 MB)" />
                            <input id="payment_proof" name="payment_proof" type="file" accept="image/*" class="block mt-1" required>
                            <x-input-error :messages="$errors->get('payment_proof')" class="mt-2" />
                        </div>

                        <x-primary-button>Sewa Garasi</x-primary-button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>