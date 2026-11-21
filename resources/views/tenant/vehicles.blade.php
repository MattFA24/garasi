<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kendaraan Saya</h2>
    </x-slot>

    @php
        $typeLabels = [
            'bicycle' => 'Sepeda',
            'motorcycle' => 'Sepeda Motor',
            'car' => 'Mobil',
            'electric_car' => 'Mobil Listrik',
        ];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Tambah Kendaraan</h3>

                <form method="POST" action="{{ route('vehicles.store') }}" class="space-y-4 max-w-md">
                    @csrf

                    <div>
                        <x-input-label for="type" value="Jenis Kendaraan" />
                        <select id="type" name="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            @foreach ($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="plate_number" value="Nomor Plat (kosongkan untuk sepeda)" />
                        <x-text-input id="plate_number" name="plate_number" type="text" class="block mt-1 w-full" :value="old('plate_number')" />
                        <x-input-error :messages="$errors->get('plate_number')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="brand" value="Merek" />
                        <x-text-input id="brand" name="brand" type="text" class="block mt-1 w-full" :value="old('brand')" required />
                        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="model" value="Tipe / Model" />
                        <x-text-input id="model" name="model" type="text" class="block mt-1 w-full" :value="old('model')" required />
                        <x-input-error :messages="$errors->get('model')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="battery_capacity_kwh" value="Kapasitas Baterai (kWh, khusus mobil listrik)" />
                        <x-text-input id="battery_capacity_kwh" name="battery_capacity_kwh" type="number" step="0.1" min="1" class="block mt-1 w-full" :value="old('battery_capacity_kwh')" />
                        <x-input-error :messages="$errors->get('battery_capacity_kwh')" class="mt-2" />
                    </div>

                    <x-primary-button>Simpan</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Daftar Kendaraan</h3>

                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Jenis</th>
                            <th>Nomor Plat</th>
                            <th>Merek</th>
                            <th>Tipe</th>
                            <th>Kapasitas Baterai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehicles as $vehicle)
                            <tr class="border-b">
                                <td class="py-2">{{ $typeLabels[$vehicle->type] }}</td>
                                <td>{{ $vehicle->plate_number ?? '-' }}</td>
                                <td>{{ $vehicle->brand }}</td>
                                <td>{{ $vehicle->model }}</td>
                                <td>{{ $vehicle->battery_capacity_kwh ? $vehicle->battery_capacity_kwh.' kWh' : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-gray-500">Belum ada kendaraan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>