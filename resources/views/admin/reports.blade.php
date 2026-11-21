<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Sewa</h2>
    </x-slot>

    @php
        $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $typeLabels = ['bike' => 'Garasi Sepeda', 'car' => 'Garasi Mobil', 'electric' => 'Garasi Mobil Listrik'];
        $rupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Filter --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="year" value="Tahun" />
                        <x-text-input id="year" name="year" type="number" class="block mt-1 w-32" :value="$year" />
                    </div>
                    <div>
                        <x-input-label for="month" value="Bulan (untuk laporan per hari)" />
                        <select id="month" name="month" class="block mt-1 border-gray-300 rounded-md shadow-sm">
                            @foreach ($monthNames as $number => $name)
                                <option value="{{ $number }}" @selected($month === $number)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Tampilkan</x-primary-button>
                </form>
                <x-input-error :messages="$errors->get('year')" class="mt-2" />
                <x-input-error :messages="$errors->get('month')" class="mt-2" />

                <p class="mt-4 text-gray-700">
                    Tahun {{ $year }}: <span class="font-semibold">{{ $total['count'] }} sewa</span>,
                    pendapatan <span class="font-semibold">{{ $rupiah($total['revenue']) }}</span>.
                </p>
                <p class="text-sm text-gray-500">Pendapatan = total sewa + biaya perpanjangan, dicatat menurut tanggal mulai sewa.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                {{-- Per bulan --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Per Bulan, Tahun {{ $year }}</h3>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Bulan</th>
                                <th class="text-right">Jumlah Sewa</th>
                                <th class="text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($perMonth as $number => $row)
                                <tr class="border-b">
                                    <td class="py-1">{{ $monthNames[$number] }}</td>
                                    <td class="text-right">{{ $row['count'] }}</td>
                                    <td class="text-right">{{ $rupiah($row['revenue']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Per hari --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Per Hari, {{ $monthNames[$month] }} {{ $year }}</h3>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Tanggal</th>
                                <th class="text-right">Jumlah Sewa</th>
                                <th class="text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($perDay as $day => $row)
                                <tr class="border-b">
                                    <td class="py-1">{{ sprintf('%02d-%02d-%d', $day, $month, $year) }}</td>
                                    <td class="text-right">{{ $row['count'] }}</td>
                                    <td class="text-right">{{ $rupiah($row['revenue']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Per tipe garasi --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Per Tipe Garasi, Tahun {{ $year }}</h3>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Tipe Garasi</th>
                                <th class="text-right">Jumlah Sewa</th>
                                <th class="text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($perType as $type => $row)
                                <tr class="border-b">
                                    <td class="py-1">{{ $typeLabels[$type] }}</td>
                                    <td class="text-right">{{ $row['count'] }}</td>
                                    <td class="text-right">{{ $rupiah($row['revenue']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Per penyewa --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Per Penyewa, Tahun {{ $year }}</h3>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Penyewa</th>
                                <th class="text-right">Jumlah Sewa</th>
                                <th class="text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perTenant as $row)
                                <tr class="border-b">
                                    <td class="py-1">{{ $row['name'] }}</td>
                                    <td class="text-right">{{ $row['count'] }}</td>
                                    <td class="text-right">{{ $rupiah($row['revenue']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-gray-500">Belum ada sewa di tahun ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>