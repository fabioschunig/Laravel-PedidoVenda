<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Customers\Create as CustomersCreate;
use App\Livewire\Customers\Edit as CustomersEdit;

use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Products\Create as ProductsCreate;
use App\Livewire\Products\Edit as ProductsEdit;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/clientes', CustomersIndex::class)->name('customers.index');
    Route::get('/clientes/criar', CustomersCreate::class)
        ->name('customers.create')
        ->middleware('can:manage-customers');
    Route::get('/clientes/{customer}/editar', CustomersEdit::class)
        ->name('customers.edit')
        ->middleware('can:manage-customers');

    Route::get('/produtos', ProductsIndex::class)->name('products.index');
    Route::get('/produtos/criar', ProductsCreate::class)
        ->name('products.create')
        ->middleware('can:manage-products');
    Route::get('/produtos/{product}/editar', ProductsEdit::class)
        ->name('products.edit')
        ->middleware('can:manage-products');
});

require __DIR__ . '/auth.php';
