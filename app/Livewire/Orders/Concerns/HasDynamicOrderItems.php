<?php

namespace App\Livewire\Orders\Concerns;

use App\Models\Product;

trait HasDynamicOrderItems
{
    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updated(string $name): void
    {
        if (preg_match('/^items\.(\d+)\.product_id$/', $name, $m)) {
            $this->syncPriceFromProduct((int) $m[1]);
        }

        if (preg_match('/^items\.(\d+)\.(product_id|quantity)$/', $name, $m)) {
            $this->recalculateSubtotal((int) $m[1]);
        }
    }

    protected function syncPriceFromProduct(int $index): void
    {
        $productId = $this->items[$index]['product_id'] ?? null;
        $product = $productId ? Product::find($productId) : null;

        $this->items[$index]['unit_price'] = $product?->price ?? 0;
    }

    protected function recalculateSubtotal(int $index): void
    {
        $qty = (float) ($this->items[$index]['quantity'] ?? 0);
        $price = (float) ($this->items[$index]['unit_price'] ?? 0);

        $this->items[$index]['subtotal'] = round($qty * $price, 2);
    }

    public function getOrderTotalProperty(): float
    {
        return round(array_sum(array_column($this->items, 'subtotal')), 2);
    }

    protected function itemsRules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
