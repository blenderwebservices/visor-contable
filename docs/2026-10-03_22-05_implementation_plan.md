# Plan de Implementación: Auditoría y Endurecimiento de Seguridad

**Fecha y Hora:** 2026-10-03 22:05  
**Documento base de referencia:** `docs/AuditoriaDeSeguridad.md`  
**Objetivo:** Auditar la base de código de `visor-contable` contra los 8 pilares y la lista de verificación rápida (15 puntos) establecidos en la guía metodológica de seguridad, identificando puntos de mejora, vulnerabilidades potenciales y plan de remediación.

---

## 1. Resumen Ejecutivo del Diagnóstico

El análisis exhaustivo del proyecto `visor-contable` (Laravel 11 + Filament v3 + Livewire + PDF.js + PhpSpreadsheet) frente a `docs/AuditoriaDeSeguridad.md` arroja los siguientes hallazgos prioritarios:

1. **Pilar 1 & 2 (CSP y XSS - Recursos Externos sin SRI / CDNs Abiertas):**
   - En `resources/views/filament/app/components/file-viewer.blade.php`:
     - Se inyecta dinámicamente un tag `<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js">` y se carga el worker remoto `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js`.
     - No cuenta con integridad de subrecursos (SRI) ni vendoring local.
     - Incumple el Principio de Vendoring Local (Pilar 3) y la directiva de CSP estricta `script-src 'self'`.
2. **Pilar 1 (Mitigación de XSS en Avisos del Sistema):**
   - En `resources/views/filament/widgets/announcements-widget.blade.php`:
     - Línea 38: `{!! $announcement->content !!}` renderiza HTML sin sanitizar ni filtrar tags peligrosos (ej. `<script>`, `<iframe>`, `onerror=`). Si bien proviene de un RichEditor gestionado por admin, ante un compromiso de cuenta o importación de respaldo JSON, existe riesgo de Stored XSS persistente en el Dashboard.
3. **Pilar 5 (Mitigación de Prototype Pollution / Deserialización en Respaldos JSON):**
   - En `app/Filament/Pages/Settings.php`:
     - Al restaurar estructura mediante `restoreStructureData()`, se deserializa un archivo JSON arbitrario mediante `json_decode($jsonContent, true)`. No se valida la presencia de claves maliciosas ni se limpian propiedades reservadas (`__proto__`, `constructor`, `prototype`).
4. **Pilar 6 (Resiliencia DoS, Cuotas y Validación Estricta de Archivos):**
   - En `app/Filament/Resources/FileDocumentResource/Pages/ListFileDocuments.php` (`addFolderItemAction`):
     - La subida del archivo `new_file` no restringe `acceptedFileTypes` ni `maxSize` en el formulario directo, permitiendo potencialmente subidas de archivos ejecutables (`.php`, `.phtml`, `.exe`) o de tamaños descomunales.
   - En `app/Filament/Resources/FileDocumentResource.php`:
     - `file_path` y `new_file` tienen `maxSize(51200)` pero carecen de `acceptedFileTypes` estricto a nivel de frontend/backend, confiando únicamente en la extensión deducida.
5. **Control de Acceso y Autorización en Operaciones Sensibles (IDOR / Scope Leakage):**
   - En `routes/web.php` (`/documents/view/{fileDocument}`):
     - La ruta solo verifica `middleware(['web'])` pero **no exige autenticación** (`auth`) ni valida la Policy `FileDocumentPolicy::view()`. Cualquier usuario (o visitante anónimo que adivine/enumere el ID secuencial) puede visualizar documentos contables de cualquier empresa.
   - En `app/Filament/App/Pages/FileExplorer.php`:
     - Métodos `viewFileAction` y `viewNotesAction`: aceptan `file` (ID de documento) y no comprueban si el usuario autenticado tiene permisos sobre la carpeta o empresa asociada a dicho archivo antes de renderizar la vista previa o añadir/listar notas.
     - En `getFiles()`: obtiene archivos de la carpeta sin validar previamente si `currentFolderId` pertenece al scope del usuario actual.
   - En `app/Filament/Resources/UserResource.php`:
     - El campo de contraseña no utiliza `dehydrated(fn ($state) => filled($state))` ni `required(fn (string $context): bool => $context === 'create')`, lo que puede provocar sobreescritura accidental o comportamientos inseguros en la edición de usuarios.
6. **Pilar 8 (Cabeceras de Servidor Web y Aislamiento):**
   - No existe un Middleware global en Laravel que emita cabeceras de endurecimiento (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, y `Content-Security-Policy`).

---

## 2. Plan Detallado de Remediación Paso a Paso

### Fase 1: Control de Acceso y Blindaje de Endpoints de Documentos (Prioridad Alta)
- [ ] **1.1. Proteger ruta `/documents/view/{fileDocument}`:**
  - Agregar middleware `auth` a la ruta en `routes/web.php`.
  - Validar explícitamente autorización (`abort_unless(auth()->user()->can('view', $fileDocument), 403)`).
- [ ] **1.2. Reforzar `FileDocumentPolicy`:**
  - Actualizar `FileDocumentPolicy::view()` para verificar que el usuario tenga acceso a la carpeta del documento (`$user->isAdmin()`, supervisor asignado a la empresa de la carpeta, o reader con pertenencia de empresa y sin exclusión de carpetas restringidas).
- [ ] **1.3. Blindar acciones en `FileExplorer`:**
  - Validar acceso en `openFolder($folderId)` y en `getFiles()` para garantizar que la carpeta solicitada cumpla con el scope del usuario actual (`scopeForCurrentUser`).
  - En `viewFileAction` y `viewNotesAction`, verificar autorización sobre el archivo/carpeta antes de devolver el modal.

### Fase 2: Eliminación de CDNs y Vendoring Local de PDF.js (Pilares 2, 3 y 4)
- [ ] **2.1. Vendoring Local de PDF.js:**
  - Descargar los archivos estáticos de `pdf.js` y `pdf.worker.js` (o compilar localmente en `public/vendor/pdfjs/`).
  - Modificar `resources/views/filament/app/components/file-viewer.blade.php` para apuntar exclusivamente a rutas locales (`/vendor/pdfjs/...`) con SRI o vendoring local garantizado.
- [ ] **2.2. Mitigar XSS en Visor de Anuncios:**
  - Sanitizar el contenido del anuncio en `announcements-widget.blade.php` o asegurar un filtrado estricto con HTMLPurifier/DOMPurify antes de renderizar con `{!! !!}`.

### Fase 3: Validación Estricta de Archivos y Mitigación DoS (Pilar 6)
- [ ] **3.1. Restricción de Tipos MIME y Extensiones:**
  - En `FileDocumentResource` y `ListFileDocuments` (`addFolderItemAction` y `new_version`), agregar `acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/jpeg', 'image/png', 'text/plain'])` y limitar el tamaño a cuotas controladas (ej. 25MB - 50MB).
- [ ] **3.2. Sanitización en Deserialización de Respaldos (Pilar 5):**
  - Implementar en `Settings.php` una función de limpieza recursiva de llaves sospechosas (`__proto__`, `constructor`, `prototype`) al importar archivos JSON de estructura.

### Fase 4: Cabeceras de Seguridad HTTP (Pilar 8)
- [ ] **4.1. Middleware de Seguridad HTTP (`SecurityHeadersMiddleware`):**
  - Crear e integrar middleware en `bootstrap/app.php` que configure:
    - `X-Frame-Options: SAMEORIGIN` (para permitir la vista de iframes locales de Excel/HTML dentro del propio visor).
    - `X-Content-Type-Options: nosniff`
    - `Referrer-Policy: strict-origin-when-cross-origin`
    - `Permissions-Policy: geolocation=(), camera=(), microphone=(), payment=()`

---

## 3. Plan de Verificación y Testing

1. **Test de Acceso No Autenticado a Documentos:** Probar `curl -I http://localhost/documents/view/1` y verificar respuesta 401 / redirección al login.
2. **Test de Control de Acceso por Roles (Reader vs Supervisor vs Admin):** Validar que un usuario `reader` no pueda acceder ni ver notas de documentos de carpetas ajenas o no asignadas.
3. **Test de Vendoring Local:** Inspeccionar la consola de red del navegador y confirmar que no se descargan scripts externos desde CDNs.
4. **Test de Subida de Archivos:** Intentar subir archivos ejecutables o no permitidos y verificar su bloqueo inmediato por validación.
