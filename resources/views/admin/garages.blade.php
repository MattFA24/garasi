<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Garasi</h2>
    </x-slot>

    @php
        $typeLabels = ['bike' => 'Sepeda', 'car' => 'Mobil', 'electric' => 'Mobil Listrik'];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Buat Garasi Baru</h3>

                <form method="POST" action="{{ route('garages.store') }}" class="space-y-4 max-w-md">
                    @csrf

                    <div>
                        <x-input-label for="type" value="Tipe Garasi" />
                        <select id="type" name="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            @foreach ($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="quantity" value="Jumlah Garasi" />
                        <x-text-input id="quantity" name="quantity" type="number" min="1" max="999" class="block mt-1 w-full" :value="old('quantity', 1)" required />
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="daily_price" value="Harga Sewa per Hari (Rp)" />
                        <x-text-input id="daily_price" name="daily_price" type="number" min="1" class="block mt-1 w-full" :value="old('daily_price')" required />
                        <x-input-error :messages="$errors->get('daily_price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="price_per_kwh" value="Harga Isi Listrik per kWh (Rp, khusus garasi mobil listrik)" />
                        <x-text-input id="price_per_kwh" name="price_per_kwh" type="number" min="1" class="block mt-1 w-full" :value="old('price_per_kwh')" />
                        <x-input-error :messages="$errors->get('price_per_kwh')" class="mt-2" />
                    </div>

                    <x-primary-button>Simpan</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Daftar Garasi</h3>

                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Kode</th>
                            <th>Tipe</th>
                            <th>Harga per Hari</th>
                            <th>Harga per kWh</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($garages as $garage)
                            <tr class="border-b">
                                <td class="py-2">{{ $garage->code }}</td>
                                <td>{{ $typeLabels[$garage->type] }}</td>
                                <td>Rp {{ number_format($garage->daily_price, 0, ',', '.') }}</td>
                                <td>{{ $garage->price_per_kwh ? 'Rp '.number_format($garage->price_per_kwh, 0, ',', '.') : '-' }}</td>
                                <td>{{ $garage->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-gray-500">Belum ada garasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>