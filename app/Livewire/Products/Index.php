<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all'; // all | active | inactive

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleActive(Product $product): void
    {
        Gate::authorize('manage-products');

        $product->update(['active' => ! $product->active]);
    }

    public function delete(Product $product): void
    {
        Gate::authorize('manage-products');

        $product->delete();

        session()->flash('success', 'Produto excluído com sucesso.');
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->when($this->statusFilter === 'active', fn($query) => $query->where('active', true))
            ->when($this->statusFilter === 'inactive', fn($query) => $query->where('active', false))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.products.index', [
            'products' => $products,
        ]);
    }
}
