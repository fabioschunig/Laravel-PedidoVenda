<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public ?string $description = null;
    public string $price = '';
    public bool $active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'active' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        $this->authorize('manage-products');
    }

    public function save()
    {
        $this->validate();

        Product::create([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'active' => $this->active,
        ]);

        session()->flash('success', 'Produto criado com sucesso.');

        return $this->redirect(route('products.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.products.create');
    }
}
