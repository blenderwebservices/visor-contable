# Visor Contable: Características Relevantes de la Plataforma

Visor Contable no solo es una plataforma para la gestión y distribución de documentos corporativos, sino que ha evolucionado incorporando potentes herramientas de productividad, seguridad y control. A continuación, se detallan las características más relevantes que hacen de esta plataforma una solución robusta y completa.

## 1. Control de Versiones de Documentos
El sistema garantiza que ninguna información se pierda o se sobrescriba accidentalmente gracias a un sistema integral de **Control de Versiones**.
* **Historial Inmutable:** Cada vez que se actualiza un archivo, la plataforma guarda la versión anterior en un historial seguro con fecha, hora y autor.
* **Restauración Inteligente:** Es posible restaurar cualquier versión antigua con un solo clic. El sistema protege al usuario creando un backup automático del archivo actual antes de ejecutar la restauración.
* **Trazabilidad:** Se pueden agregar "Notas de cambio" opcionales para explicar rápidamente a otros miembros del equipo qué se modificó en cada versión.

## 2. Papelera de Reciclaje (Soft Deletes)
Para prevenir la eliminación accidental de información crítica, la plataforma cuenta con un sistema de **Papelera de Reciclaje Global**.
* **Seguridad de Datos:** La eliminación de Usuarios, Carpetas o Documentos no borra la información de la base de datos inmediatamente. Todo es enviado a la papelera.
* **Lógica en Cascada:** Al restaurar una carpeta desde la papelera, el sistema es capaz de restaurar inteligentemente todos sus archivos y subcarpetas contenidos, devolviendo la estructura exactamente a como estaba.
* **Panel de Administración Dedicado:** Los administradores tienen una sección exclusiva para navegar por la papelera, vaciarla definitivamente o restaurar ítems.

## 3. Explorador de Archivos Dinámico e Interactivo (Vista Explorer)
Pensando en la comodidad de los usuarios, se diseñaron distintos modos de visualización para los documentos, destacando la **Vista Explorer**, que simula un sistema de escritorio.
* **Gestión In-Place (En el lugar):** Los usuarios pueden agregar nuevas subcarpetas o subir documentos directamente desde el encabezado de cualquier carpeta sin cambiar de pantalla.
* **Visualización Inteligente:** La vista dibuja la estructura completa, permitiendo ubicar fácilmente carpetas aunque se encuentren vacías.
* **Eliminación Avanzada:** Al eliminar desde el Explorer, el usuario puede elegir borrar toda la estructura, borrar solo los archivos (dejando las carpetas intactas), o borrar las carpetas pero "salvando" los archivos y moviéndolos a un nivel superior.

## 4. Panel de Avisos y Notificaciones Inteligente
Una vía de comunicación directa desde los administradores hacia los clientes o empleados de las empresas.
* **Comunicación Dirigida:** Los avisos pueden configurarse para ser vistos por "Todos los usuarios", o ser segmentados para mostrarse únicamente a "Grupos Específicos" (Empresas) o "Usuarios Específicos".
* **Control de Vigencia:** Cada aviso puede tener una fecha de inicio y caducidad.
* **Gestión de Bandeja Limpia:** Los usuarios que ya han leído un aviso pueden ocultarlo (marcarlo como leído) para limpiar su Dashboard. Cuentan también con la posibilidad de restaurar sus avisos ocultos si necesitan volver a consultarlos.
* **Contenido Enriquecido:** El área de administración permite redactar los avisos usando texto enriquecido (negritas, enlaces, listas, etc.).

## 5. Control de Accesos y Roles Jerárquicos
La plataforma cuenta con un robusto sistema de privilegios diseñado para satisfacer las distintas necesidades corporativas.
* **Roles Definidos:** Se distinguen niveles como `Admin`, `Supervisor` y `Reader` (Cliente final).
* **Supervisión Centralizada:** Todo el sistema se unificó en un único portal (`/portal` o `/admin`), permitiendo restringir o conceder recursos de forma dinámica. Por ejemplo, los clientes (Readers) solo verán las carpetas asignadas a ellos, protegiendo estrictamente la privacidad entre diferentes grupos/empresas.

## 6. Múltiples Modos de Visualización
Para adaptarse al estilo de trabajo de cada persona, la navegación de documentos permite cambiar entre diferentes vistas con un solo clic:
* **Vista de Lista:** Clásica tabla de datos ideal para ordenar por fechas o filtrar rápidamente.
* **Vista de Cuadrícula (Grid):** Visualización más gráfica basada en tarjetas.
* **Vista Explorer:** Navegación por jerarquía de carpetas.

## 7. Herramientas de Mantenimiento Administrativo
Los superadministradores de la plataforma cuentan con herramientas exclusivas (Settings) para realizar labores de mantenimiento estructural.
* **Reindexación y Respaldos:** Posibilidad de ejecutar reindexaciones de base de datos o solicitar operaciones de backup estructural de forma sencilla desde el propio panel.
