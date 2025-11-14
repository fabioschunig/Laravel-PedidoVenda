<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class Edit extends Component
{
    public Product $product;

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

    public function mount(Product $product): void
    {
        $this->authorize('manage-products');

        $this->product = $product;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = (string) $product->price;
        $this->active = $product->active;
    }

    public function save()
    {
        $this->validate();

        $this->product->update([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'active' => $this->active,
        ]);

        session()->flash('success', 'Produto atualizado com sucesso.');

        return $this->redirect(route('products.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.products.edit');
    }
}
