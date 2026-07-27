# Sistema de Gestión Académica

PHP puro (PDO) + PostgreSQL, sin frameworks. Módulos: Alumnos, Titulaciones,
Asignaturas, Matrículas y Convocatorias, con login y CRUD completo en cada uno.

## Arquitectura (versión compacta)

En vez de un archivo por cada acción (insertar/actualizar/eliminar/editar
por módulo, como en la primera versión), cada módulo ahora es solo **3
archivos**:

- `modulo.php` — la página (sidebar + tabla + modal de crear/editar).
- `backend/api_modulo.php` — un único endpoint que atiende **GET** (listar/buscar),
  **POST** (crear), **PUT** (editar) y **DELETE** (eliminar), devolviendo JSON.
- `frontend/js/modulo.js` — pide los datos a la API con `fetch` y pinta la
  tabla y el modal (Bootstrap) sin recargar la página.

```
gestionAcademica/
├── index.php                 Login
├── panel_principal.php       Panel tras iniciar sesión
├── alumnos.php / titulaciones.php / asignaturas.php / matriculas.php / convocatorias.php
├── backend/
│   ├── conexion.php          Conexión PDO a PostgreSQL
│   ├── procesar_login.php / logout.php
│   ├── api_alumnos.php / api_titulaciones.php / api_asignaturas.php
│   ├── api_matriculas.php / api_convocatorias.php
│   └── includes/api_helpers.php   Helpers comunes a todos los api_*.php
├── includes/
│   ├── helpers.php           requerirSesion()
│   └── sidebar.php           Menú lateral (todas las páginas internas)
├── frontend/
│   ├── css/style.css
│   └── js/
│       ├── sidebar.js        Comportamiento del menú (hover/colapsar)
│       ├── api-utils.js      fetch genérico + mensajes de éxito/error
│       ├── validaciones.js   Utilidades de formularios (login, etc.)
│       └── alumnos.js / titulaciones.js / asignaturas.js / matriculas.js / convocatorias.js
├── database/schema.sql       DDL + datos de prueba
├── Dockerfile                Para desplegar en Render
└── render.yaml
```

Cada página de módulo ya no navega a una "página de editar" aparte: el
mismo modal de Bootstrap sirve para crear y editar, y la tabla se actualiza
sola después de guardar/eliminar, sin recargar el navegador.

## 1. Probar en local (localhost)

1. Instala PostgreSQL y crea la base:
   ```sql
   CREATE DATABASE gestion_academica;
   ```
2. Carga el esquema y los datos de prueba:
   ```bash
   psql -U postgres -d gestion_academica -f database/schema.sql
   ```
3. Ajusta, si hace falta, el usuario/contraseña locales en
   `backend/conexion.php` (por defecto usuario `postgres`, contraseña
   `postgres`, host `localhost`, puerto `5432`) o expórtalos como
   variables de entorno `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`.
4. Levanta el servidor embebido de PHP desde la carpeta del proyecto:
   ```bash
   php -S localhost:8000
   ```
5. Abre `http://localhost:8000/index.php`.

**Usuario de prueba:** `admin` / `admin123`

## 2. Desplegar en Render (con PostgreSQL)

1. Sube este proyecto a un repositorio de GitHub.
2. En Render: **New > Blueprint**, apunta al repo (usa `render.yaml`
   incluido) o crea manualmente:
   - Un **PostgreSQL** (Free plan).
   - Un **Web Service** con **Runtime: Docker** apuntando a este repo
     (usa el `Dockerfile` incluido).
   - En el Web Service, agrega la variable de entorno `DATABASE_URL`
     con el "Internal Database URL" que te da el Postgres de Render.
3. Una vez creada la base, ejecuta `database/schema.sql` sobre ella:
   ```bash
   psql "postgresql://usuario:password@host/nombre_bd" -f database/schema.sql
   ```
4. `backend/conexion.php` detecta automáticamente `DATABASE_URL` y se
   conecta usando SSL, tal como Render lo requiere. No hay que tocar
   código para pasar de local a producción.

## Diseño

Bootstrap 5.3.8 (CDN) + sidebar fijo colapsable (`includes/sidebar.php` +
`frontend/js/sidebar.js`), con la paleta de colores original de este
proyecto (azul `#0056b3` / `#007bff`, verde `#28a745` para guardar, rojo
`#dc3545` para eliminar), centralizada en variables CSS en
`frontend/css/style.css`.

## Notas de seguridad / validación

- Contraseñas guardadas con `password_hash()` / verificadas con `password_verify()`.
- Todas las consultas usan sentencias preparadas (PDO) — sin inyección SQL.
- Cada `backend/api_*.php` exige sesión iniciada y responde con error 401
  en JSON (no redirige) si no la hay — pensado para ser consumido por `fetch`.
- Validación tanto en JS (HTML5 + validaciones.js) como en cada `api_*.php`
  (nunca confíes solo en el navegador): cédula de 10 dígitos, correo válido,
  fechas no futuras, créditos entre 1 y 20, estados/tipos restringidos a una lista fija.
- Restricciones también a nivel de base de datos (`CHECK`, `UNIQUE`, `FOREIGN KEY`).
