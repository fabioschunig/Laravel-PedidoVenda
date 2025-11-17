<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;


class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function confirm(Order $order): void
    {
        Gate::authorize('manage-orders');

        if ($order->status !== OrderStatus::Aberto) {
            return;
        }

        $order->update(['status' => OrderStatus::Confirmado]);
    }

    public function deliver(Order $order): void
    {
        Gate::authorize('manage-orders');

        if ($order->status !== OrderStatus::Confirmado) {
            return;
        }

        $order->update(['status' => OrderStatus::Entregue]);
    }

    public function cancel(Order $order): void
    {
        Gate::authorize('cancel-orders');

        if (in_array($order->status, [OrderStatus::Entregue, OrderStatus::Cancelado], true)) {
            return;
        }

        $order->update(['status' => OrderStatus::Cancelado]);
    }

    public function render()
    {
        $orders = Order::query()
            ->with(['customer', 'items'])
            ->when($this->search, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
