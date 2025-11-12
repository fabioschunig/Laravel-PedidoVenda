<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;

class Edit extends Component
{
    public Customer $customer;

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

    public function mount(Customer $customer): void
    {
        $this->authorize('manage-customers');

        $this->customer = $customer;
        $this->name = $customer->name;
        $this->document = $customer->document;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
    }

    public function save()
    {
        $this->validate();

        $this->customer->update([
            'name' => $this->name,
            'document' => $this->document,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
        ]);

        session()->flash('success', 'Cliente atualizado com sucesso.');

        return $this->redirect(route('customers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.customers.edit');
    }
}
