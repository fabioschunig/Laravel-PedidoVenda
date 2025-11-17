<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Livewire\Orders\Concerns\HasDynamicOrderItems;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    use HasDynamicOrderItems;

    public string $customer_id = '';
    public array $items = [];

    public function mount(): void
    {
        $this->authorize('manage-orders');
        $this->addItem();
    }

    protected function rules(): array
    {
        return $this->itemsRules();
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_id' => $this->customer_id,
                'status' => OrderStatus::Aberto,
                'total' => 0,
            ]);

            foreach ($this->items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            $order->recalculateTotal();
        });

        session()->flash('success', 'Pedido criado com sucesso.');

        return $this->redirect(route('orders.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.orders.create', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::where('active', true)->orderBy('name')->get(),
        ]);
    }
}
