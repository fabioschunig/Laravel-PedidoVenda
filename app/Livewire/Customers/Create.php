<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public ?string $document = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $address = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function mount(): void
    {
        $this->authorize('manage-customers');
    }

    public function save()
    {
        $this->validate();

        Customer::create([
            'name' => $this->name,
            'document' => $this->document,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
        ]);

        session()->flash('success', 'Cliente criado com sucesso.');

        return $this->redirect(route('customers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.customers.create');
    }
}
