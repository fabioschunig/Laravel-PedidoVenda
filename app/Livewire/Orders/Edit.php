<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Livewire\Orders\Concerns\HasDynamicOrderItems;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    use HasDynamicOrderItems;

    public Order $order;
    public string $customer_id = '';
    public array $items = [];

    public function mount(Order $order): void
    {
        $this->authorize('manage-orders');

        if ($order->status !== OrderStatus::Aberto) {
            abort(403, 'Este pedido não pode mais ser editado (status atual: ' . $order->status->label() . ').');
        }

        $this->order = $order;
        $this->customer_id = (string) $order->customer_id;

        $this->items = $order->items->map(fn($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'quantity' => (string) $item->quantity,
            'unit_price' => (string) $item->unit_price,
            'subtotal' => (string) $item->subtotal,
        ])->all();
    }

    protected function rules(): array
    {
        return $this->itemsRules();
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $this->order->update(['customer_id' => $this->customer_id]);

            $keepIds = [];

            foreach ($this->items as $item) {
                if (! empty($item['id'])) {
                    $this->order->items()->whereKey($item['id'])->update([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                    $keepIds[] = $item['id'];
                } else {
                    $newItem = $this->order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                    $keepIds[] = $newItem->id;
                }
            }

            $this->order->items()->whereNotIn('id', $keepIds)->delete();

            $this->order->recalculateTotal();
        });

        session()->flash('success', 'Pedido atualizado com sucesso.');

        return $this->redirect(route('orders.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.orders.edit', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::where('active', true)->orderBy('name')->get(),
        ]);
    }
}
