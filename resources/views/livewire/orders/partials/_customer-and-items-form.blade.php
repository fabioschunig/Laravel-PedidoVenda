{{-- resources/views/livewire/orders/partials/_customer-and-items-form.blade.php --}}
<div>
    <label class="block text-sm font-medium">Cliente</label>
    <select wire:model="customer_id" class="w-full border-gray-300 rounded">
        <option value="">Selecione um cliente</option>
        @foreach ($customers as $customer)
        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
        @endforeach
    </select>
    @error('customer_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
</div>

<div class="mt-6">
    <div class="flex justify-between items-center mb-2">
        <h3 class="font-medium">Itens</h3>
        <button type="button" wire:click="addItem" class="text-blue-600 hover:underline text-sm">
            + Adicionar item
        </button>
    </div>

    <table class="w-full border-collapse">
        <thead>
            <tr class="text-left border-b text-sm">
                <th class="py-2">Produto</th>
                <th class="py-2 w-24">Qtd.</th>
                <th class="py-2 w-32">Preço unit.</th>
                <th class="py-2 w-32">Subtotal</th>
                <th class="py-2 w-10"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
            <tr wire:key="item-{{ $index }}" class="border-b">
                <td class="py-2">
                    <select wire:model="items.{{ $index }}.product_id" class="w-full border-gray-300 rounded">
                        <option value="">Selecione</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error("items.{$index}.product_id") <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </td>
                <td class="py-2">
                    <input type="number" step="0.01" min="0.01"
                        wire:model="items.{{ $index }}.quantity"
                        class="w-full border-gray-300 rounded">
                    @error("items.{$index}.quantity") <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </td>
                <td class="py-2">
                    R$ {{ number_format($item['unit_price'], 2, ',', '.') }}
                </td>
                <td class="py-2">
                    R$ {{ number_format($item['subtotal'], 2, ',', '.') }}
                </td>
                <td class="py-2">
                    @if (count($items) > 1)
                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-600">
                        &times;
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @error('items') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

    <div class="text-right mt-2 font-semibold">
        Total: R$ {{ number_format($this->orderTotal, 2, ',', '.') }}
    </div>
</div>