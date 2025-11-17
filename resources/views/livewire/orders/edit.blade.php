{{-- resources/views/livewire/orders/edit.blade.php --}}
<div>
    <h2 class="text-xl font-semibold mb-4">Editar Pedido #{{ $order->id }}</h2>

    <form wire:submit="save" class="space-y-6 max-w-3xl">
        @include('livewire.orders.partials._customer-and-items-form')

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Atualizar Pedido
            </button>
            <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded border">Cancelar</a>
        </div>
    </form>
</div>