Esta sera un aplicacion web  que tenga un panel de control donde se mostraran los archivos pertenecientes a cada usuario, organizados jerarquicamente segun lo definido por el usuario administrador de sistemas y que permita entender el contexto de los archivos.  Dichos archivos seran provistos por usuarios administradores de sistemas.  El sistema debe ser lo mas rapido y optimizado posible.  Se debe implementar un sistema de caching para mejorar el rendimiento.
    
    Los archivos solo podran ser pdf, word, txt, excel y imagenes.  Dichos archivos se encontraran en una carpeta raiz por usuario, dentro de los cuales el administrador de sistemas podra crear carpetas y subcarpetas para organizar los archivos.  La organizacion de las carpetas sera establecida por el administrador de sistemas.
    El sistema debe implementar un sistema de permisos que permita a los usuarios administradores gestionar los permisos de los usuarios.  El usuario autenticado solo podra ver los archivos que le pertenecen.

En el backend habra cruds para los usuarios, grupos de usuarios, carpetas, subcarpetas y archivos.  Los usuarios podran ser asignados a grupos de usuarios, y los grupos de usuarios podran ser asignados a carpetas y subcarpetas.  Los archivos podran ser asignados a carpetas y subcarpetas.

La plataforma sera en español y tendra dos idiomas alternos ingles y portugues.  Se usara tailwind para el frontend y laravel apoyado en filament para el backend.

En el frontend se mostrara un dashboard con los archivos pertencientes a cada usuario, organizados jerarquicamente segun lo definido por el usuario administrador de sistemas y que permita entender el contexto de los archivos.
La lista de los archivos tendra dos tipos de vista, cards y lista, en cada uno habra un boton de visualizacion, donde se abrira un modal para visualizar contextualmente los diferentes tipos de archivos y opcionalmente la posibilidad de hacer alguna anotacion sobre el archivo, la anotacion estara vinculada al archivo y al usuario que la realizo. Tal vez no sea sobre el archivo, sino en una nota relacionada al archivo.  Esta vista se repetira en cada nivel de la jerarquia de carpetas.

Sobre los archivos:

El modo visualizacion del archivo en el modal dependera del tipo de archivo.  Si es un pdf, se mostrara un visor de pdf.  Si es un word, se mostrara un visor de word.  Si es un txt, se mostrara un visor de txt.  Si es un excel, se mostrara un visor de excel.  Si es una imagen, se mostrara un visor de imagenes. Opcionalmente se permitira la descarga de los archivos, pero no sera la funcionalidad principal.

Cada archivo que se suba tendra atributos que lo definiran para el usuario destino.  Estos atributos seran establecidos por el usuario administrador de sistemas y solo los podra modificar el usuario administrador de sistemas.  El usuario autenticado solo podra ver los archivos que le pertenecen.

Los usuarios seran definidos por los administradores y se les asignara un nombre de usuario, una contraseña, un correo electronico y un rol de usuario lector de documentos. Los usuarios se les asignaran las carpetas y subcarpetas que deseen. 
Los archivos se les asignaran atributos personalizados por el usuario administrador de sistemas que permitirán filtrar la lista de archivos a mostrar, con el objetivo de entender el contexto de los archivos.

