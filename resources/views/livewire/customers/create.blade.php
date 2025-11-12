<div>
    <h2 class="text-xl font-semibold mb-4">Novo Cliente</h2>

    <form wire:submit="save" class="space-y-4 max-w-lg">
        <div>
            <label class="block text-sm font-medium">Nome</label>
            <input type="text" wire:model="name" class="w-full border-gray-300 rounded">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">CPF/CNPJ</label>
            <input type="text" wire:model="document" class="w-full border-gray-300 rounded">
            @error('document') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">E-mail</label>
            <input type="email" wire:model="email" class="w-full border-gray-300 rounded">
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Telefone</label>
            <input type="text" wire:model="phone" class="w-full border-gray-300 rounded">
            @error('phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Endereço</label>
            <input type="text" wire:model="address" class="w-full border-gray-300 rounded">
            @error('address') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Salvar
            </button>
            <a href="{{ route('customers.index') }}" class="px-4 py-2 rounded border">
                Cancelar
            </a>
        </div>
    </form>
</div>