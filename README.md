GestFincas es una aplicación web desarrollada en PHP bajo el patrón de arquitectura MVC (Modelo-Vista-Controlador). Permite la gestión de comunidades de vecinos, facilitando la administración de viviendas, usuarios (vecinos y presidentes) y el control de accesos mediante un sistema de validación por códigos.

🚀 Requisitos Previos
Para ejecutar este proyecto en tu entorno local, necesitarás tener instalado:

Un entorno de servidor local como XAMPP, WAMP, o MAMP.
PHP 8.0 o superior.
MySQL o MariaDB.
Git (opcional, para el control de versiones).
🛠️ Guía de Instalación Paso a Paso
Sigue estos pasos exactamente en orden para desplegar el proyecto en tu máquina local:

Paso 1: Descargar el proyecto
Abre tu terminal o consola.
Navega hasta la carpeta pública de tu servidor local (por ejemplo, htdocs en XAMPP o www en WAMP).
Clona el repositorio ejecutando:
git clone [https://github.com/tu-usuario/JR_M26_ComunidadVecinos.git](https://github.com/tu-usuario/JR_M26_ComunidadVecinos.git)
(Si no usas Git, simplemente descarga el ZIP del proyecto y descomprímelo en esa misma carpeta).

Paso 2: Crear y configurar la Base de Datos
Abre el panel de control de tu servidor (ej. XAMPP) y asegúrate de que Apache y MySQL estén en ejecución.
Ve a tu gestor de base de datos (por ejemplo, entra a http://localhost/phpmyadmin).
Crea una nueva base de datos vacía llamada exactamente: gestfincas.
Ve a la pestaña Importar y selecciona el archivo gestfincas.sql que se encuentra dentro de la carpeta config/ de este proyecto.
Haz clic en "Importar" o "Continuar". Esto creará todas las tablas necesarias y cargará los datos de prueba.
Paso 3: Configurar la conexión (Archivo Local)
Por motivos de seguridad, las credenciales no se suben al repositorio. Debes crear tu archivo de configuración:

Ve a la carpeta config/ del proyecto.
Localiza el archivo llamado config.php.example.
Duplica ese archivo y renombra la copia como config.local.php.
Abre config.local.php en tu editor de código y pon las credenciales de tu base de datos local (por defecto en XAMPP el usuario es "root" y la contraseña se deja vacía):
PHP

'localhost', // o 127.0.0.1 'db' => 'gestfincas', // Nombre de la base de datos 'user' => 'root', // Tu usuario de MySQL 'pass' => '', // Tu contraseña de MySQL 'charset' => 'utf8mb4', ]; ?>
Paso 4: Arrancar la aplicación
Abre tu navegador web. Accede a la ruta pública del proyecto. Dependiendo del nombre de la carpeta, la URL será algo como: http://localhost/JR_M26_ComunidadVecinos-main/public/index.php
¡Listo! Deberías ver la pantalla de inicio de sesión de GestFincas.
🔑 Datos de Prueba para Iniciar Sesión

El archivo SQL ya incluye un usuario de prueba para que puedas probar el sistema inmediatamente:

Nombre de la Vivienda: Planta 2-1B

Correo Electrónico: mariapelaez@gmail.com

Contraseña: casacasa

🏗️ Estructura del Proyecto (Arquitectura MVC) /config: Archivos de configuración de DB, enrutador y scripts SQL.

/public: Contiene el punto de entrada principal (index.php) y los recursos estáticos (assets/ con CSS e imágenes).

/src: Código fuente de la app:

controllers/: Manejan la lógica y peticiones (ej. AuthController.php).

models/: Interactúan con la base de datos (ej. UsuarioModel.php).

views/: Interfaces de usuario y componentes HTML.
