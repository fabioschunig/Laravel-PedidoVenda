{{-- resources/views/livewire/products/index.blade.php --}}
<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Produtos</h2>

        @can('manage-products')
        <a href="{{ route('products.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Novo Produto
        </a>
        @endcan
    </div>

    @if (session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex gap-4 mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nome..."
            class="flex-1 border-gray-300 rounded">

        <select wire:model.live="statusFilter" class="border-gray-300 rounded">
            <option value="all">Todos</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
        </select>
    </div>

    <table class="w-full border-collapse">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">Nome</th>
                <th class="py-2">Preço</th>
                <th class="py-2">Status</th>
                <th class="py-2">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
            <tr class="border-b" wire:key="product-{{ $product->id }}">
                <td class="py-2">{{ $product->name }}</td>
                <td class="py-2">R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                <td class="py-2">
                    <span class="px-2 py-1 rounded text-xs {{ $product->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $product->active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="py-2 space-x-2">
                    @can('manage-products')
                    <a href="{{ route('products.edit', $product) }}"
                        class="text-blue-600 hover:underline">Editar</a>
                    <button
                        wire:click="toggleActive({{ $product->id }})"
                        class="text-gray-600 hover:underline">
                        {{ $product->active ? 'Desativar' : 'Ativar' }}
                    </button>
                    <button
                        wire:click="delete({{ $product->id }})"
                        wire:confirm="Tem certeza que deseja excluir este produto?"
                        class="text-red-600 hover:underline">
                        Excluir
                    </button>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="py-4 text-center text-gray-500">
                    Nenhum produto encontrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>