<x-filament-widgets::widget>
    <x-filament::section class="bg-blue-900 text-white dark:bg-blue-900">
        <div class="flex items-center gap-4">
            <div class="bg-white text-blue-900 p-3 rounded-full">
                <x-heroicon-o-user class="w-8 h-8" />
            </div>

            <div>
                <h2 class="text-2xl font-bold">Portal del Empleado</h2>
                <p class="text-gray-200 text-sm mt-1">
                    {{ auth()->user()->name }} | {{ auth()->user()->email }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>