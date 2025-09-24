<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        <p>Papel atual: <strong>{{ auth()->user()->role }}</strong></p>

        @can('admin')
        <p>Você tem acesso de <strong>Admin</strong>.</p>
        @endcan

        @can('vendedor')
        <p>Você tem acesso de <strong>Vendedor</strong>.</p>
        @endcan

        @can('visualizador')
        <p>Você tem acesso de <strong>Visualizador</strong>.</p>
        @endcan
    </div>
</x-app-layout>