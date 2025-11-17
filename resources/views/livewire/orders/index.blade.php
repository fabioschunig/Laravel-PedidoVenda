{{-- resources/views/livewire/orders/index.blade.php --}}
<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Pedidos</h2>

        @can('manage-orders')
        <a href="{{ route('orders.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Novo Pedido
        </a>
        @endcan
    </div>

    <div class="flex gap-4 mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por cliente..."
            class="flex-1 border-gray-300 rounded">

        <select wire:model.live="statusFilter" class="border-gray-300 rounded">
            <option value="all">Todos os status</option>
            @foreach ($statuses as $status)
            <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <table class="w-full border-collapse">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">#</th>
                <th class="py-2">Cliente</th>
                <th class="py-2">Itens</th>
                <th class="py-2">Total</th>
                <th class="py-2">Status</th>
                <th class="py-2">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
            <tr class="border-b" wire:key="order-{{ $order->id }}">
                <td class="py-2">#{{ $order->id }}</td>
                <td class="py-2">{{ $order->customer->name }}</td>
                <td class="py-2">{{ $order->items->count() }}</td>
                <td class="py-2">R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                <td class="py-2">
                    <span @class([ 'px-2 py-1 rounded text-xs' , 'bg-yellow-100 text-yellow-800'=> $order->status === \App\Enums\OrderStatus::Aberto,
                        'bg-blue-100 text-blue-800' => $order->status === \App\Enums\OrderStatus::Confirmado,
                        'bg-green-100 text-green-800' => $order->status === \App\Enums\OrderStatus::Entregue,
                        'bg-red-100 text-red-800' => $order->status === \App\Enums\OrderStatus::Cancelado,
                        ])>
                        {{ $order->status->label() }}
                    </span>
                </td>
                <td class="py-2 space-x-2">
                    @can('manage-orders')
                    @if ($order->status === \App\Enums\OrderStatus::Aberto)
                    <a href="{{ route('orders.edit', $order) }}" class="text-blue-600 hover:underline">Editar</a>
                    <button wire:click="confirm({{ $order->id }})" class="text-green-600 hover:underline">Confirmar</button>
                    @endif

                    @if ($order->status === \App\Enums\OrderStatus::Confirmado)
                    <button wire:click="deliver({{ $order->id }})" class="text-green-600 hover:underline">Entregar</button>
                    @endif
                    @endcan

                    @can('cancel-orders')
                    @if (! in_array($order->status, [\App\Enums\OrderStatus::Entregue, \App\Enums\OrderStatus::Cancelado]))
                    <button
                        wire:click="cancel({{ $order->id }})"
                        wire:confirm="Cancelar este pedido?"
                        class="text-red-600 hover:underline">
                        Cancelar
                    </button>
                    @endif
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-4 text-center text-gray-500">
                    Nenhum pedido encontrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>