<x-filament-widgets::widget>
    @php
        $announcements = $this->announcements;
        $hiddenCount = $this->hiddenCount;
    @endphp

    @if($announcements->count() > 0 || $hiddenCount > 0)
        <x-filament::section class="mb-4">
            <x-slot name="heading">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-filament::icon
                            icon="heroicon-o-megaphone"
                            class="h-6 w-6 text-primary-500"
                        />
                        <span>Avisos Importantes</span>
                    </div>
                    @if($hiddenCount > 0)
                        <x-filament::button size="sm" color="gray" wire:click="restoreHiddenAnnouncements">
                            Restaurar avisos ocultos ({{ $hiddenCount }})
                        </x-filament::button>
                    @endif
                </div>
            </x-slot>

            <div class="grid grid-cols-1 gap-4">
                @forelse($announcements as $announcement)
                    <div class="relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="flex items-start justify-between">
                            <h3 class="font-semibold text-lg text-gray-950 dark:text-white">
                                {{ $announcement->title }}
                            </h3>
                            <button wire:click="hideAnnouncement({{ $announcement->id }})" title="Ocultar este aviso" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                            </button>
                        </div>
                        <div class="mt-2 prose dark:prose-invert max-w-none text-sm text-gray-600 dark:text-gray-400">
                            {!! $announcement->content !!}
                        </div>
                        @if($announcement->valid_until)
                            <div class="mt-4 text-xs text-gray-500">
                                Válido hasta: {{ $announcement->valid_until->translatedFormat('d M Y, H:i') }}
                            </div>
                        @endif
                    </div>
                @empty
                    @if($hiddenCount > 0)
                        <div class="text-center text-gray-500 py-4">
                            No tienes avisos nuevos. Has ocultado {{ $hiddenCount }} aviso(s).
                        </div>
                    @endif
                @endforelse
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
