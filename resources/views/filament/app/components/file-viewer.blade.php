<div x-data="{ 
         maximized: false,
         modal: null,
         init() {
             this.$nextTick(() => {
                 this.modal = this.$el.closest('.fi-modal-window');
                 if(this.modal) {
                     this.modal.style.resize = 'none'; // Desactivamos el resize CSS nativo
                     this.modal.style.minWidth = '400px';
                     this.modal.style.minHeight = '400px';
                     // Forzamos al modal de Filament a respetar nuestra altura
                     this.modal.style.height = '700px'; 
                     this.modal.style.maxWidth = '95vw';
                     
                     // Asegurar que el contenedor del contenido llene el espacio disponible
                     const contentWrap = this.modal.querySelector('.fi-modal-content');
                     if (contentWrap) {
                         contentWrap.style.flex = '1 1 auto';
                         contentWrap.style.display = 'flex';
                         contentWrap.style.flexDirection = 'column';
                     }
                 }
             });
             
             this.$watch('maximized', value => {
                 if(!this.modal) return;
                 if(value) {
                     this.modal.style.position = 'fixed';
                     this.modal.style.top = '0';
                     this.modal.style.left = '0';
                     this.modal.style.width = '100vw';
                     this.modal.style.height = '100vh';
                     this.modal.style.maxWidth = '100vw';
                     this.modal.style.maxHeight = '100vh';
                     this.modal.style.margin = '0';
                     this.modal.style.borderRadius = '0';
                 } else {
                     this.modal.style.position = 'relative';
                     this.modal.style.top = '';
                     this.modal.style.left = '';
                     this.modal.style.width = '';
                     this.modal.style.height = '700px';
                     this.modal.style.maxWidth = '95vw';
                     this.modal.style.maxHeight = '';
                     this.modal.style.margin = '';
                     this.modal.style.borderRadius = '';
                 }
             });
         },
         startResize(e, direction) {
             if (!this.modal || this.maximized) return;
             e.preventDefault();
             e.stopPropagation();
             
             const startX = e.clientX;
             const startY = e.clientY;
             const startWidth = this.modal.offsetWidth;
             const startHeight = this.modal.offsetHeight;
             
             // Convertimos a absolute temporalmente para poder mover el left libremente
             const rect = this.modal.getBoundingClientRect();
             this.modal.style.position = 'fixed'; // fixed es más seguro que absolute en Filament
             this.modal.style.left = rect.left + 'px';
             this.modal.style.top = rect.top + 'px';
             this.modal.style.margin = '0';
             
             const doDrag = (dragEvent) => {
                 if (direction.includes('right')) {
                     this.modal.style.width = (startWidth + dragEvent.clientX - startX) + 'px';
                 }
                 if (direction.includes('left')) {
                     const newWidth = startWidth - (dragEvent.clientX - startX);
                     if (newWidth > 400) {
                        this.modal.style.width = newWidth + 'px';
                        this.modal.style.left = (rect.left + (dragEvent.clientX - startX)) + 'px';
                     }
                 }
                 if (direction.includes('bottom')) {
                     this.modal.style.height = (startHeight + dragEvent.clientY - startY) + 'px';
                 }
             };
             
             const stopDrag = () => {
                 document.documentElement.removeEventListener('mousemove', doDrag, false);
                 document.documentElement.removeEventListener('mouseup', stopDrag, false);
             };
             
             document.documentElement.addEventListener('mousemove', doDrag, false);
             document.documentElement.addEventListener('mouseup', stopDrag, false);
         }
     }"
     class="relative w-full h-full flex-1 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden transition-all duration-150"
     style="min-height: 100%;">

    <!-- Agarraderas (Handles) para redimensionar -->
    <div x-show="!maximized" @mousedown="startResize($event, 'right')" class="absolute top-0 right-[-10px] w-4 h-full cursor-e-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'left')" class="absolute top-0 left-[-10px] w-4 h-full cursor-w-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom')" class="absolute bottom-[-10px] left-0 w-full h-4 cursor-s-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom-right')" class="absolute bottom-[-10px] right-[-10px] w-6 h-6 cursor-se-resize z-40"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom-left')" class="absolute bottom-[-10px] left-[-10px] w-6 h-6 cursor-sw-resize z-40"></div>
    
    <!-- Controls -->
    <div class="absolute top-2 right-2 flex space-x-2 z-10 bg-white/90 dark:bg-gray-800/90 p-1 rounded shadow">
        @if(isset($isDownloadable) && $isDownloadable)
            <a href="{{ $downloadUrl }}" download="{{ $fileName ?? 'archivo' }}" class="p-2 text-gray-700 hover:text-primary-600 dark:text-gray-300 transition rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="Descargar">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
            </a>
        @endif
        <button @click="maximized = !maximized" type="button" class="p-2 text-gray-700 hover:text-primary-600 dark:text-gray-300 transition rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="Maximizar / Restaurar">
            <x-heroicon-o-arrows-pointing-out x-show="!maximized" class="w-5 h-5" />
            <x-heroicon-o-arrows-pointing-in x-show="maximized" x-cloak class="w-5 h-5" />
        </button>
    </div>

    <!-- Viewer -->
    <div class="w-full h-full p-2 pt-14 flex-1">
        @if($type === 'pdf')
            <object data="{{ $url }}" type="application/pdf" class="w-full h-full rounded">
                <iframe src="{{ $url }}" class="w-full h-full rounded" frameborder="0">
                    <p class="text-center mt-10">Tu navegador no puede mostrar este documento de forma integrada. <br> <a href="{{ $downloadUrl }}" class="text-primary-600 underline">Descárgalo aquí</a>.</p>
                </iframe>
            </object>
        @elseif($type === 'image')
            <img src="{{ $url }}" alt="Imagen" class="max-w-full max-h-full object-contain mx-auto rounded">
        @elseif($type === 'txt')
            <iframe src="{{ $url }}" class="w-full h-full bg-white dark:bg-gray-900 rounded" frameborder="0"></iframe>
        @else
            <div class="text-center mt-20">
                <x-heroicon-o-document class="w-16 h-16 mx-auto mb-4 text-gray-400" />
                <p class="text-gray-500 dark:text-gray-400">Vista previa no disponible para este tipo de archivo.</p>
                @if(isset($isDownloadable) && $isDownloadable)
                    <a href="{{ $downloadUrl }}" download="{{ $fileName ?? 'archivo' }}" class="mt-4 inline-flex items-center space-x-2 text-primary-600 hover:underline">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        <span>Descargar Archivo</span>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
