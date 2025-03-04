<x-filament-panels::page>
    <div class="w-full mx-auto">
        @foreach ($this->getWidgets() as $widget)
            @livewire($widget)
        @endforeach
    </div>
</x-filament-panels::page>
