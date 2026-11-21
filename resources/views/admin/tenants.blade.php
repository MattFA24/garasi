<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Verifikasi Penyewa</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">NIK</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tenants as $tenant)
                            <tr class="border-b">
                                <td class="py-2">{{ $tenant->nik }}</td>
                                <td>{{ $tenant->name }}</td>
                                <td>{{ $tenant->phone }}</td>
                                <td>{{ $tenant->email }}</td>
                                <td>{{ $tenant->status }}</td>
                                <td>
                                    @if ($tenant->status === 'pending')
                                        <form method="POST" action="{{ route('tenants.approve', $tenant) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <x-primary-button>Terima</x-primary-button>
                                        </form>
                                        <form method="POST" action="{{ route('tenants.reject', $tenant) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <x-danger-button>Tolak</x-danger-button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-gray-500">Belum ada pendaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>