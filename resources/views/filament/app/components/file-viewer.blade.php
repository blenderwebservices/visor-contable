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
         
         getRoot() {
             return this.$root || (this.$el ? (this.$el.hasAttribute('x-data') ? this.$el : this.$el.closest('[x-data]')) : null);
         },
         
         init() {
             this.$nextTick(() => {
                 const root = this.getRoot();
                 if (!root) return;
                 this.modal = root.closest('.fi-modal-window');
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
                         contentWrap.style.height = '100%';
                         contentWrap.style.overflow = 'hidden';
                     }
                     
                     let parent = root.parentElement;
                     while (parent && parent !== this.modal && !parent.classList.contains('fi-modal-window')) {
                         parent.style.flex = '1 1 auto';
                         parent.style.display = 'flex';
                         parent.style.flexDirection = 'column';
                         parent.style.height = '100%';
                         parent.style.overflow = 'hidden';
                         parent = parent.parentElement;
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
             
             const setupWorkerAndLoad = () => {
                 if (typeof pdfjsLib !== 'undefined' && !pdfjsLib.GlobalWorkerOptions.workerSrc) {
                     pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                 }
                 this.loadDocument();
             };
             
             if (typeof pdfjsLib === 'undefined') {
                 const script = document.createElement('script');
                 script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
                 script.onload = () => {
                     setupWorkerAndLoad();
                 };
                 script.onerror = () => {
                     this.pdfError = 'No se pudo cargar la librería PDF.js.';
                 };
                 document.head.appendChild(script);
             } else {
                 setupWorkerAndLoad();
             }
         },
         
         loadDocument() {
             this.$nextTick(() => {
                 const root = this.getRoot();
                 if (!root) return;
                 root._canvas = root.querySelector('canvas');
                 if(!root._canvas) {
                     this.pdfError = 'Canvas element not found!';
                     return;
                 }
                 
                 pdfjsLib.getDocument(this.pdfUrl).promise.then(pdfDoc_ => {
                     root._pdfDoc = pdfDoc_;
                     this.totalPages = root._pdfDoc.numPages;
                     this.renderPage(this.pageNum);
                 }).catch(err => {
                     console.error('Error loading PDF: ', err);
                     this.pdfError = 'Error al cargar el PDF: ' + err.message;
                 });
             });
         },
         
         renderPage(num) {
             const root = this.getRoot();
             if (!root || !root._pdfDoc || !root._canvas) return;
             
             if (root._renderTask && typeof root._renderTask.cancel === 'function') {
                 try { root._renderTask.cancel(); } catch(e) {}
             }
             
             this.pageRendering = true;
             root._pdfDoc.getPage(num).then(page => {
                 const viewport = page.getViewport({scale: this.scale});
                 root._canvas.height = viewport.height;
                 root._canvas.width = viewport.width;
                 
                 const ctx = root._canvas.getContext('2d');
                 const renderContext = {
                     canvasContext: ctx,
                     viewport: viewport
                 };
                 
                 root._renderTask = page.render(renderContext);
                 root._renderTask.promise.then(() => {
                     this.pageRendering = false;
                     if (this.pageNumPending !== null) {
                         const nextNum = this.pageNumPending;
                         this.pageNumPending = null;
                         this.renderPage(nextNum);
                     }
                 }).catch(err => {
                     this.pageRendering = false;
                     if (err && err.name !== 'RenderingCancelledException') {
                         console.error('Error rendering PDF page:', err);
                     }
                     if (this.pageNumPending !== null) {
                         const nextNum = this.pageNumPending;
                         this.pageNumPending = null;
                         this.renderPage(nextNum);
                     }
                 });
             }).catch(err => {
                 this.pageRendering = false;
                 console.error('Error getting PDF page:', err);
                 this.pdfError = 'Error al renderizar página del PDF: ' + err.message;
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
             this.scale = Math.round((this.scale + 0.2) * 10) / 10;
             this.queueRenderPage(this.pageNum);
         },
         
         zoomOut() {
             if (this.scale <= 0.4) return;
             this.scale = Math.round((this.scale - 0.2) * 10) / 10;
             this.queueRenderPage(this.pageNum);
         },
         
         resetZoom() {
             this.scale = 1.2;
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
     class="relative w-full h-full flex-1 flex flex-col bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden transition-all duration-150 border border-gray-200/80 dark:border-gray-800 shadow-sm"
     style="min-height: 100%;">

    <!-- Agarraderas (Handles) para redimensionar -->
    <div x-show="!maximized" @mousedown="startResize($event, 'right')" class="absolute top-0 right-[-10px] w-4 h-full cursor-e-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'left')" class="absolute top-0 left-[-10px] w-4 h-full cursor-w-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom')" class="absolute bottom-[-10px] left-0 w-full h-4 cursor-s-resize z-30"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom-right')" class="absolute bottom-[-10px] right-[-10px] w-6 h-6 cursor-se-resize z-40"></div>
    <div x-show="!maximized" @mousedown="startResize($event, 'bottom-left')" class="absolute bottom-[-10px] left-[-10px] w-6 h-6 cursor-sw-resize z-40"></div>
    
    <!-- Barra de herramientas unificada (Top Toolbar) -->
    <div class="w-full bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 z-20 shrink-0 shadow-sm">
        <!-- Controles de navegación de página (Solo para PDF) -->
        <div class="flex items-center space-x-2">
            @if($type === 'pdf')
                <div class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-900/70 p-1 rounded-lg border border-gray-200/60 dark:border-gray-700/60" x-show="!pdfError">
                    <button type="button" @click="onPrevPage" class="p-1.5 text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-white dark:hover:bg-gray-800 rounded-md transition-all disabled:opacity-40 disabled:hover:bg-transparent disabled:cursor-not-allowed shadow-sm hover:shadow" :disabled="pageNum <= 1" title="Página anterior">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>
                    <div class="px-2 text-xs font-semibold text-gray-700 dark:text-gray-200 tracking-wide flex items-center space-x-1">
                        <span>Pág.</span>
                        <span class="text-primary-600 dark:text-primary-400 font-bold" x-text="pageNum"></span>
                        <span class="text-gray-400 dark:text-gray-500">/</span>
                        <span x-text="totalPages"></span>
                    </div>
                    <button type="button" @click="onNextPage" class="p-1.5 text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-white dark:hover:bg-gray-800 rounded-md transition-all disabled:opacity-40 disabled:hover:bg-transparent disabled:cursor-not-allowed shadow-sm hover:shadow" :disabled="pageNum >= totalPages" title="Página siguiente">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                </div>

                <div class="hidden sm:block h-5 w-px bg-gray-200 dark:bg-gray-700 mx-1" x-show="!pdfError"></div>

                <!-- Controles de Zoom (Solo para PDF) -->
                <div class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-900/70 p-1 rounded-lg border border-gray-200/60 dark:border-gray-700/60" x-show="!pdfError">
                    <button type="button" @click="zoomOut" class="p-1.5 text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-white dark:hover:bg-gray-800 rounded-md transition-all disabled:opacity-40 disabled:hover:bg-transparent disabled:cursor-not-allowed shadow-sm hover:shadow" :disabled="scale <= 0.4" title="Reducir zoom">
                        <x-heroicon-o-magnifying-glass-minus class="w-4 h-4" />
                    </button>
                    <button type="button" @click="resetZoom" class="text-xs font-bold px-2 py-1 min-w-[3.5rem] text-center text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-white dark:hover:bg-gray-800 rounded transition-all tabular-nums" title="Restaurar zoom (120%)">
                        <span x-text="Math.round(scale * 100) + '%'"></span>
                    </button>
                    <button type="button" @click="zoomIn" class="p-1.5 text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-white dark:hover:bg-gray-800 rounded-md transition-all shadow-sm hover:shadow" title="Aumentar zoom">
                        <x-heroicon-o-magnifying-glass-plus class="w-4 h-4" />
                    </button>
                </div>
            @endif
        </div>

        <!-- Controles de acción (Imprimir, Descargar, Maximizar) -->
        <div class="flex items-center space-x-1.5 ml-auto">
            @if(isset($isPrintable) && $isPrintable && $type === 'pdf')
                <button type="button" @click="printPdf" class="p-1.5 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all flex items-center space-x-1.5 font-medium" title="Imprimir documento">
                    <x-heroicon-o-printer class="w-4 h-4" />
                    <span class="text-xs hidden sm:inline">Imprimir</span>
                </button>
            @endif
            
            @if(isset($isDownloadable) && $isDownloadable)
                <a href="{{ $downloadUrl }}" download="{{ $fileName ?? 'archivo' }}" class="p-1.5 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all flex items-center space-x-1.5 font-medium" title="Descargar archivo">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    <span class="text-xs hidden sm:inline">Descargar</span>
                </a>
            @endif
            
            @if((isset($isPrintable) && $isPrintable && $type === 'pdf') || (isset($isDownloadable) && $isDownloadable))
                <div class="h-4 w-px bg-gray-200 dark:bg-gray-700 mx-1"></div>
            @endif

            <button type="button" @click="maximized = !maximized" class="p-1.5 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all flex items-center space-x-1.5 font-medium" :title="maximized ? 'Restaurar ventana' : 'Maximizar ventana'">
                <x-heroicon-o-arrows-pointing-out x-show="!maximized" class="w-4 h-4" />
                <x-heroicon-o-arrows-pointing-in x-show="maximized" x-cloak class="w-4 h-4" />
                <span class="text-xs hidden md:inline" x-text="maximized ? 'Restaurar' : 'Maximizar'"></span>
            </button>
        </div>
    </div>

    <!-- Viewer Content Area -->
    <div class="w-full flex-1 overflow-hidden relative bg-gray-100/50 dark:bg-gray-900/50 flex flex-col">
        @if($type === 'pdf')
            <div class="flex-1 overflow-auto flex justify-center items-start p-4 custom-scrollbar relative w-full h-full">
                <canvas x-show="!pdfError" id="pdf-render" class="shadow-xl bg-white rounded transition-transform duration-100 ease-out max-w-none"></canvas>
                <div x-show="pdfError" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-50 dark:bg-red-900/80 border border-red-200 dark:border-red-700 text-red-600 dark:text-red-300 p-6 rounded-xl shadow-lg z-50 text-center max-w-sm">
                    <x-heroicon-o-exclamation-triangle class="w-12 h-12 mx-auto mb-3 text-red-500" />
                    <h4 class="font-bold text-base mb-1" x-text="pdfError"></h4>
                    <p class="text-xs text-red-500 dark:text-red-400 mt-2">Intenta descargarlo directamente con el botón de la barra superior.</p>
                </div>
            </div>
        @elseif($type === 'image')
            <div class="flex-1 overflow-auto flex justify-center items-center p-4 custom-scrollbar relative w-full h-full">
                <img src="{{ $url }}" alt="Imagen" class="max-w-full max-h-full object-contain mx-auto rounded shadow-md">
            </div>
        @elseif($type === 'txt')
            <div class="flex-1 overflow-hidden w-full h-full p-2">
                <iframe src="{{ $url }}" class="w-full h-full bg-white dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700 shadow-inner" frameborder="0"></iframe>
            </div>
        @else
            <div class="text-center my-auto p-8">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-200 dark:bg-gray-800 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 shadow-inner">
                    <x-heroicon-o-document class="w-10 h-10" />
                </div>
                <h4 class="font-bold text-gray-700 dark:text-gray-300 text-base">Vista previa no disponible</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">Este tipo de archivo no se puede visualizar directamente en el navegador.</p>
                @if(isset($isDownloadable) && $isDownloadable)
                    <a href="{{ $downloadUrl }}" download="{{ $fileName ?? 'archivo' }}" class="mt-5 inline-flex items-center space-x-2 px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white font-medium text-sm rounded-lg shadow transition-all hover:shadow-md">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
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
