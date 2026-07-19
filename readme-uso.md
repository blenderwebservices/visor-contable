# Manual de Uso - Visor Contable

Este manual explica cómo acceder a cada plataforma dentro de Visor Contable, cómo utilizar sus distintas opciones y cómo navegar de manera general entre los paneles y el sitio web público (Frontend).

Para fines prácticos, utilizaremos dos cuentas de ejemplo en esta guía:
* **Administrador:** `admin@example.com` (Contraseña: `password`)
* **Usuario (Cliente):** `reader@example.com` (Contraseña: `password`)

---

## 1. Panel de Administración (Gestor)

Este panel está reservado para el personal interno encargado de subir información, crear clientes y organizar las estructuras de los archivos.

### ¿Cómo ingresar?
1. Dirígete a la ruta de inicio de sesión: `/admin/login`.
2. En la pantalla verás un texto superior que indica: **"Acceso a Panel de Administración"**.
3. Ingresa con el correo `admin@example.com` y la contraseña `password`.
4. Haz clic en el botón de iniciar sesión.

### ¿Cómo utilizar las opciones?
Una vez dentro, encontrarás un menú lateral con las siguientes opciones:

* **Dashboard (Inicio):** Te dará un resumen de la información general de la plataforma.
* **Groups (Empresas/Grupos):** 
  * Sirve para registrar las empresas a las cuales se les subirá información.
  * *Uso:* Haz clic en "New Group", asigna un nombre y una descripción. Desde esta misma sección o al editar un grupo existente, puedes vincular a los usuarios (clientes) que pertenecen a dicha empresa dando clic en "Attach" (y seleccionándolos de la lista desplegable que carga de inmediato).
* **Users (Usuarios):**
  * Sirve para dar de alta a clientes o miembros del equipo.
  * *Uso:* Haz clic en "New user", ingresa su nombre, nombre de usuario (opcional), email y contraseña.
* **Folders (Carpetas):**
  * Sirve para crear la estructura organizativa (por ejemplo, "Facturas 2024", "Reportes Anuales").
  * *Uso:* Haz clic en "New Folder" y asígnale el grupo (empresa) correspondiente para que sólo los clientes de ese grupo puedan ver dicha carpeta.
* **File Explorer / File Documents (Archivos):**
  * Sirve para subir los documentos contables y reportes.
  * *Uso:* Sube el archivo requerido (PDF, Imagen, etc.) y asígnalo directamente a la carpeta o grupo correspondiente.

### ¿Cómo regresar al Frontend?
Si necesitas volver a la página web principal o pública de la empresa, tienes dos opciones:
1. **Desde la pantalla de Login:** Haz clic en el enlace `"← Volver al Frontend"` situado arriba del título de la pantalla.
2. **Dentro del Panel:** Ve a la esquina superior derecha, haz clic en el botón de tu perfil (donde aparece tu nombre) y en el menú desplegable selecciona la opción **"Ir al Frontend"**.

### ¿Cómo salir (Cerrar sesión)?
Haz clic en tu nombre (perfil de usuario) en la esquina superior derecha y selecciona **"Sign out"**.

---

## 2. Panel de Aplicación (Cliente / Visualizador)

Este panel está diseñado exclusivamente para que los clientes finales consuman la información contable que se les comparte.

### ¿Cómo ingresar?
1. Dirígete a la ruta de inicio de sesión: `/app/login`.
2. En la pantalla verás un texto que indica: **"Acceso a Panel de Aplicación (Empresas)"**.
3. Ingresa con el correo `reader@example.com` y la contraseña `password`.
4. Haz clic en iniciar sesión.

### ¿Cómo utilizar las opciones?
El panel del cliente es muy sencillo y directo:

* **Dashboard:**
  * Al ingresar, verás la pantalla principal. Si el administrador ya ha compartido carpetas y archivos contigo (es decir, con tu grupo o empresa), los verás listados aquí o en el explorador principal.
* **Ver tu Empresa:**
  * Haz clic en tu perfil (esquina superior derecha). El primer elemento del menú te mostrará el nombre de la empresa a la que estás vinculado (por ejemplo, "Francisco Gomez - Herdez"). Si no tienes empresa, aparecerá "Sin Empresa".
* **Visualizar Documentos:**
  * Al dar clic sobre cualquier archivo que te aparezca, se abrirá una vista previa en tu navegador.
* **Descargar e Imprimir:**
  * Dentro de la misma vista previa o tabla de archivos, encontrarás los botones para Descargar (bajar el archivo a tu dispositivo) o Imprimir (para mandarlo directo a tu impresora local).

### ¿Cómo regresar al Frontend?
Al igual que en el panel administrativo:
1. **Desde el Login:** Usa el botón de `"← Volver al Frontend"`.
2. **Desde el Panel:** Ve a tu perfil en la esquina superior derecha y haz clic en **"Ir al Frontend"**.

### ¿Cómo salir (Cerrar sesión)?
Haz clic en tu nombre en la esquina superior derecha y selecciona la opción **"Sign out"**.
