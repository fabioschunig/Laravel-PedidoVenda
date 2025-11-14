{{-- resources/views/livewire/products/create.blade.php --}}
<div>
    <h2 class="text-xl font-semibold mb-4">Novo Produto</h2>

    <form wire:submit="save" class="space-y-4 max-w-lg">
        <div>
            <label class="block text-sm font-medium">Nome</label>
            <input type="text" wire:model="name" class="w-full border-gray-300 rounded">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Descrição</label>
            <textarea wire:model="description" rows="3" class="w-full border-gray-300 rounded"></textarea>
            @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Preço</label>
            <input type="number" step="0.01" min="0" wire:model="price" class="w-full border-gray-300 rounded">
            @error('price') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model="active" id="active" class="rounded border-gray-300">
            <label for="active" class="text-sm font-medium">Produto ativo</label>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Salvar
            </button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 rounded border">
                Cancelar
            </a>
        </div>
    </form>
</div>