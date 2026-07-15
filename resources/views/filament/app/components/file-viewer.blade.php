<div x-data="{ 
         maximized: false,
         modal: null,
         pageNum: 1,
         pageRendering: false,
         pageNumPending: null,
         scale: 1.2,
         pdfUrl: '{{ $url }}',
         totalPages: 0,
         pdfError: null,
         
         init() {
             this.$nextTick(() => {
                 this.modal = this.$el.closest('.fi-modal-window');
                 if(this.modal) {
                     this.modal.style.resize = 'none'; 
                     this.modal.style.minWidth = '400px';
                     this.modal.style.minHeight = '400px';
                     this.modal.style.height = '700px'; 
                     this.modal.style.maxWidth = '95vw';
                     
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
         
         initPdf() {
             if ('{{ $type }}' !== 'pdf') return;
             
             if (typeof pdfjsLib === 'undefined') {
                 const script = document.createElement('script');
                 script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
                 script.onload = () => {
                     this.loadDocument();
                 };
                 script.onerror = () => {
                     this.pdfError = 'No se pudo cargar la librería PDF.js.';
                 };
                 document.head.appendChild(script);
             } else {
                 this.loadDocument();
             }
         },
         
         loadDocument() {
             this.$nextTick(() => {
                 this.$el._canvas = this.$el.querySelector('canvas');
                 if(!this.$el._canvas) {
                     this.pdfError = 'Canvas element not found!';
                     return;
                 }
                 this.$el._ctx = this.$el._canvas.getContext('2d');
                 
                 pdfjsLib.getDocument(this.pdfUrl).promise.then(pdfDoc_ => {
                     this.$el._pdfDoc = pdfDoc_;
                     this.totalPages = this.$el._pdfDoc.numPages;
                     this.renderPage(this.pageNum);
                 }).catch(err => {
                     console.error('Error loading PDF: ', err);
                     this.pdfError = 'Error al cargar el PDF: ' + err.message;
                 });
             });
         },
         
         renderPage(num) {
             this.pageRendering = true;
             this.$el._pdfDoc.getPage(num).then(page => {
                 const viewport = page.getViewport({scale: this.scale});
                 this.$el._canvas.height = viewport.height;
                 this.$el._canvas.width = viewport.width;
                 
                 const renderContext = {
                     canvasContext: this.$el._ctx,
                     viewport: viewport
                 };
                 
                 const renderTask = page.render(renderContext);
                 renderTask.promise.then(() => {
                     this.pageRendering = false;
                     if (this.pageNumPending !== null) {
                         this.renderPage(this.pageNumPending);
                         this.pageNumPending = null;
                     }
                 });
             });
         },
         
         queueRenderPage(num) {
             if (this.pageRendering) {
                 this.pageNumPending = num;
             } else {
                 this.renderPage(num);
             }
         },
         
         onPrevPage() {
             if (this.pageNum <= 1) return;
             this.pageNum--;
             this.queueRenderPage(this.pageNum);
         },
         
         onNextPage() {
             if (this.pageNum >= this.totalPages) return;
             this.pageNum++;
             this.queueRenderPage(this.pageNum);
         },
         
         zoomIn() {
             this.scale += 0.2;
             this.queueRenderPage(this.pageNum);
         },
         
         zoomOut() {
             if (this.scale <= 0.4) return;
             this.scale -= 0.2;
             this.queueRenderPage(this.pageNum);
         },
         
         printPdf() {
             const iframe = document.createElement('iframe');
             iframe.style.display = 'none';
             iframe.src = this.pdfUrl;
             document.body.appendChild(iframe);
             iframe.onload = () => {
                 setTimeout(() => {
                     iframe.contentWindow.focus();
                     iframe.contentWindow.print();
                 }, 500);
             };
         },
         
         startResize(e, direction) {
             if (!this.modal || this.maximized) return;
             e.preventDefault();
             e.stopPropagation();
             
             const startX = e.clientX;
             const startY = e.clientY;
             const startWidth = this.modal.offsetWidth;
             const startHeight = this.modal.offsetHeight;
             
             const rect = this.modal.getBoundingClientRect();
             this.modal.style.position = 'fixed'; 
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
     x-init="init(); initPdf();"
     class="relative w-full h-full flex-1 flex flex-col bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden transition-all duration-150"
     style="min-height: 100%;">

    <!-- Agarraderas (Handles) para redimensionar -->
    <div x-show="!maximized" @mousedown="startResize($event, 'right')" class="absolute top-0 right-[-10px] w-4 h-full cursor-e-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'left')" class="absolute top-0 left-[-10px] w-4 h-full cursor-w-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom')" class="absolute bottom-[-10px] left-0 w-full h-4 cursor-s-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom-right')" class="absolute bottom-[-10px] right-[-10px] w-6 h-6 cursor-se-resize z-40"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom-left')" class="absolute bottom-[-10px] left-[-10px] w-6 h-6 cursor-sw-resize z-40"></div>
    
    <!-- Controls (Top Right) -->
    <div class="absolute top-2 right-2 flex space-x-2 z-10 bg-white/90 dark:bg-gray-800/90 p-1 rounded shadow">
        @if(isset($isPrintable) && $isPrintable && $type === 'pdf')
            <button @click="printPdf" type="button" class="p-2 text-gray-700 hover:text-primary-600 dark:text-gray-300 transition rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="Imprimir">
                <x-heroicon-o-printer class="w-5 h-5" />
            </button>
        @endif
        
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

    <!-- Viewer Content -->
    <div class="w-full h-full p-2 pt-14 flex-1 overflow-hidden">
        @if($type === 'pdf')
            <div class="flex flex-col h-full w-full relative">
                <!-- PDF.js Toolbar (Zoom and Pages) -->
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex items-center space-x-4 px-4 py-2 bg-gray-900/80 text-white rounded-full shadow-lg z-20 backdrop-blur-sm" x-show="!pdfError">
                    <div class="flex items-center space-x-1">
                        <button @click="onPrevPage" class="p-1 hover:text-primary-400 disabled:opacity-50" :disabled="pageNum <= 1">
                            <x-heroicon-o-chevron-left class="w-5 h-5" />
                        </button>
                        <span class="text-sm font-medium px-2">
                            <span x-text="pageNum"></span> / <span x-text="totalPages"></span>
                        </span>
                        <button @click="onNextPage" class="p-1 hover:text-primary-400 disabled:opacity-50" :disabled="pageNum >= totalPages">
                            <x-heroicon-o-chevron-right class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="w-px h-5 bg-gray-600"></div>
                    <div class="flex items-center space-x-1">
                        <button @click="zoomOut" class="p-1 hover:text-primary-400 disabled:opacity-50" :disabled="scale <= 0.4">
                            <x-heroicon-o-magnifying-glass-minus class="w-5 h-5" />
                        </button>
                        <span class="text-sm px-2 w-12 text-center" x-text="Math.round(scale * 100) + '%'"></span>
                        <button @click="zoomIn" class="p-1 hover:text-primary-400">
                            <x-heroicon-o-magnifying-glass-plus class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                
                <!-- Canvas Container -->
                <div class="flex-1 overflow-auto flex justify-center items-start pt-4 custom-scrollbar relative">
                    <canvas x-show="!pdfError" id="pdf-render" class="shadow-xl bg-white max-w-full"></canvas>
                    <div x-show="pdfError" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 p-4 rounded shadow-lg z-50 text-center">
                        <x-heroicon-o-exclamation-triangle class="w-12 h-12 mx-auto mb-2 opacity-80" />
                        <span x-text="pdfError" class="font-medium"></span>
                        <p class="text-sm mt-2">Intenta descargarlo directamente con el botón de la esquina superior derecha.</p>
                    </div>
                </div>
            </div>
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
    
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
        }
    </style>
</div>
