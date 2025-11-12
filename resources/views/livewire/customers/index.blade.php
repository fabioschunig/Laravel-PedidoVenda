<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Clientes</h2>

        @can('manage-customers')
        <a href="{{ route('customers.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Novo Cliente
        </a>
        @endcan
    </div>

    @if (session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
    </div>
    @endif

    <input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Buscar por nome, documento ou e-mail..."
        class="w-full mb-4 border-gray-300 rounded">

    <table class="w-full border-collapse">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">Nome</th>
                <th class="py-2">Documento</th>
                <th class="py-2">E-mail</th>
                <th class="py-2">Telefone</th>
                <th class="py-2">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
            <tr class="border-b" wire:key="customer-{{ $customer->id }}">
                <td class="py-2">{{ $customer->name }}</td>
                <td class="py-2">{{ $customer->document ?? '—' }}</td>
                <td class="py-2">{{ $customer->email ?? '—' }}</td>
                <td class="py-2">{{ $customer->phone ?? '—' }}</td>
                <td class="py-2 space-x-2">
                    @can('manage-customers')
                    <a href="{{ route('customers.edit', $customer) }}"
                        class="text-blue-600 hover:underline">Editar</a>
                    <button
                        wire:click="delete({{ $customer->id }})"
                        wire:confirm="Tem certeza que deseja excluir este cliente?"
                        class="text-red-600 hover:underline">
                        Excluir
                    </button>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-4 text-center text-gray-500">
                    Nenhum cliente encontrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>
</div>