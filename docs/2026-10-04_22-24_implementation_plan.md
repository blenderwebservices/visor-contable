# Plan de Implementación: Corrección de Falla en Restauración de Respaldos (Límite al 24%)

**Fecha y Hora:** 2026-10-04 22:24  
**Problema Reportado:** La restauración de "Todo y documentos" (y documentos físicos) falla aproximadamente al 24% con el error: `The mountedActionsData.0.documents_file... failed to upload` ("Error durante la subida (7.3 MB)").

---

## 1. Causa Raíz Identificada

El análisis técnico determinó con exactitud la causa del fallo:

1. **Límite Estricto de PHP en Herd (`upload_max_filesize` y `post_max_size` en 2M):**
   - El archivo de configuración activa de PHP (`/Users/franciscogomezbarragan/Library/Application Support/Herd/config/php/84/php.ini`) tiene configurado:
     ```ini
     upload_max_filesize=2M
     post_max_size=2M
     ```
   - Al intentar subir un archivo de **7.3 MB**, el navegador transmite datos hasta alcanzar exactamente el límite de **2 MB** (`1.75 MB / 7.3 MB ≈ 24%`). En ese instante, PHP aborta la subida arrojando el código de error `UPLOAD_ERR_INI_SIZE`.
   - Livewire detecta que `$file->isValid()` es falso y dispara la regla de validación `uploaded` (`validation.uploaded`):
     `The mountedActionsData.0.documents_file.<hash> failed to upload`.

2. **Límite de Nginx en Herd (`client_max_body_size` en 2M):**
   - En `/Users/franciscogomezbarragan/Library/Application Support/Herd/config/nginx/herd.conf` la directiva está configurada en:
     ```nginx
     client_max_body_size 2M;
     ```
   - Si la aplicación se consulta vía el dominio de Herd (`*.test`), Nginx interrumpe la conexión con un error `413 Request Entity Too Large` al superar 2MB.

3. **Límite por Defecto de Livewire (12 MB):**
   - En `FileUploadConfiguration::rules()`, Livewire limita las subidas temporales a `max:12288` (12 MB) si no se especifica otra regla en `config/livewire.php`. Aunque un archivo de 7.3 MB pasa esta regla, un respaldo completo con cientos de documentos (ej. 30MB, 50MB o 200MB) fallaría de inmediato.

4. **Componentes FileUpload de Filament en `Settings.php`:**
   - No contaban con la directiva explícita `->maxSize(512000)` para permitir archivos de hasta 500 MB.

---

## 2. Plan de Remediación

### Fase 1: Ajuste de Directivas en PHP (`php.ini`)
- [ ] Modificar `/Users/franciscogomezbarragan/Library/Application Support/Herd/config/php/84/php.ini` (y versiones complementarias en Herd):
  - `upload_max_filesize = 512M`
  - `post_max_size = 512M`
  - `memory_limit = 512M`
  - `max_execution_time = 300`

### Fase 2: Ajuste de Nginx en Herd (`herd.conf`)
- [ ] Actualizar `/Users/franciscogomezbarragan/Library/Application Support/Herd/config/nginx/herd.conf`:
  - Cambiar `client_max_body_size 2M;` a `client_max_body_size 512M;`.

### Fase 3: Configuración de Livewire (`config/livewire.php`)
- [ ] Configurar `temporary_file_upload.rules` a `['required', 'file', 'max:512000']` (500 MB).
- [ ] Configurar `temporary_file_upload.max_upload_time` a `30` minutos para permitir subidas de archivos pesados con conexiones lentas.

### Fase 4: Refuerzo en Filament (`Settings.php`)
- [ ] Agregar `->maxSize(512000)` en:
  - `FileUpload::make('full_backup_file')`
  - `FileUpload::make('documents_file')`
  - `FileUpload::make('backup_file')`

### Fase 5: Reinicio y Verificación
- [ ] Reiniciar servicios de Herd (`herd restart`).
- [ ] Verificar con CLI que los límites de PHP reflejen `512M`.
- [ ] Probar prueba sintética de subida/validación de archivo ZIP > 7.3 MB.
- [ ] Elaborar el reporte de entrega (Walkthrough) en `docs/`.
