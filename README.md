<p align="center">
  <img src="public/images/brand/iceframe-logo.png" alt="Logo de IceFrame Inventory" width="430">
</p>

---

## Descripción

**IceFrame** es una aplicación web para administrar el inventario de una tienda de productos tecnológicos: drones, cámaras de acción, cámaras 360, accesorios audiovisuales y equipos para creación de contenido.

Este repositorio contiene la **aplicación de inventario del Estudiante 1**, desarrollada en Laravel y ejecutada dentro de un contenedor Docker. La aplicación se conecta a una base de datos PostgreSQL remota ubicada en la máquina del Estudiante 2 mediante una red privada **Tailscale**. Además, permite integrarse con un módulo externo de reportes mediante la variable de entorno `REPORTES_URL`.

El sistema permite consultar productos, registrar nuevos artículos, editar información comercial, actualizar stock, registrar ventas, reportar hurtos, registrar reposiciones, consultar movimientos y exportar información en formatos CSV y JSON.

---

## Contexto Académico

Proyecto de la asignatura **Sistemas Distribuidos**.

La consigna solicita un sistema de inventario compuesto por servicios independientes en contenedores Docker:

| Servicio | Responsable | Descripción |
|---------|-------------|-------------|
| Aplicación de inventario | Estudiante 1 | Aplicación web para gestionar productos, stock y operaciones de inventario. |
| Base de datos | Estudiante 2 | Contenedor PostgreSQL con persistencia mediante volumen Docker. |
| Sistema de reportes | Estudiante 2 | Servicio independiente conectado a la base de datos por red Docker interna. |

Este repositorio corresponde a la **aplicación de inventario**. La base de datos y el sistema de reportes se despliegan desde el repositorio o máquina del segundo integrante.

---

## Cumplimiento de la Consigna

| Requisito solicitado | Implementación en IceFrame |
|---------------------|----------------------------|
| Registrar nuevo producto | Formulario de creación de producto con categoría, marca, proveedor, condición, precio y stock. |
| Actualizar stock de producto existente | Reposición de stock, venta transaccional, registro de hurto y edición controlada. |
| Eliminar producto | Desactivación lógica y eliminación física segura para administradores. |
| Listar productos | Catálogo con filtros, estados de stock y ordenamiento. |
| Consultar producto por ID o nombre | Búsqueda por producto, filtros y vista de detalle. |
| Leer IP y credenciales desde `.env` | Variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. |
| Tener Dockerfile propio | `Dockerfile` incluido en la raíz del proyecto. |
| Exponer puerto accesible por Tailscale | `docker-compose.yml` expone `8000:8000`. |
| Conectarse a la base del Estudiante 2 | Conexión PostgreSQL mediante IP Tailscale. |
| Integrarse con reportes | Botón de redirección configurable con `REPORTES_URL`. |

---

## Arquitectura Distribuida

```text
Usuario en navegador
   ↓
Contenedor de aplicación: iceframe-app
Laravel + Blade + CSS + JavaScript
   ↓
Red privada Tailscale
   ↓
Servidor del Estudiante 2
PostgreSQL + módulo de reportes
   ↓
Persistencia y reportes operativos
```

### Flujo lógico de la aplicación

```text
Vista Blade
   ↓
Rutas Laravel
   ↓
Controllers
   ↓
Modelos Eloquent / Servicios de dominio
   ↓
PostgreSQL remoto por Tailscale
   ↓
Respuesta renderizada en Dashboard, Catálogo, Ventas o Movimientos
```

---

## Stack Tecnológico

| Capa / Módulo | Tecnología |
|--------------|------------|
| Backend | PHP 8.4 · Laravel 13 |
| Frontend | Blade · Tabler UI · Bootstrap 5 · CSS personalizado |
| Interactividad | JavaScript · Tom Select · ApexCharts |
| Base de datos | PostgreSQL remoto |
| Red privada | Tailscale |
| Contenedores | Docker · Docker Compose |
| Autenticación | Guard web de Laravel con modelo `Usuario` |
| Exportaciones | CSV UTF-8 con BOM · JSON con formato legible |

---

## Justificación del Motor de Base de Datos

Se utiliza **PostgreSQL** porque es un motor relacional robusto, estable y adecuado para un sistema de inventario con relaciones entre productos, categorías, marcas, proveedores, ventas, detalles de venta y movimientos de stock.

PostgreSQL permite trabajar con integridad referencial, transacciones, bloqueos de filas y consultas agregadas. Esto es importante porque el sistema registra operaciones que modifican stock y deben mantenerse consistentes, como ventas, reposiciones y hurtos.

Además, PostgreSQL funciona correctamente en contenedores Docker, permite persistencia mediante volúmenes y puede exponerse por la red privada Tailscale sin depender de una base local en la aplicación.

---

## Estructura del Repositorio

```text
app-inventario/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── InventarioController.php
│   │   │   ├── MovimientoController.php
│   │   │   ├── ProductoController.php
│   │   │   └── VentaController.php
│   │   └── Middleware/
│   │       └── EnsureAdmin.php
│   ├── Models/
│   │   ├── Categoria.php
│   │   ├── Cliente.php
│   │   ├── DetalleVenta.php
│   │   ├── Marca.php
│   │   ├── MovimientoInventario.php
│   │   ├── Producto.php
│   │   ├── Proveedor.php
│   │   ├── Rol.php
│   │   ├── Usuario.php
│   │   └── Venta.php
│   └── Services/
│       └── GraficosService.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   └── session.php
├── public/
│   ├── css/
│   │   └── iceframe.css
│   ├── js/
│   │   └── iceframe.js
│   └── images/
│       └── brand/
│           ├── favicon.png
│           ├── iceframe-icon.png
│           ├── iceframe-logo.png
│           └── iceframe-logo-sidebar.png
├── resources/
│   └── views/
│       ├── auth/
│       ├── inventario/
│       ├── layouts/
│       ├── movimientos/
│       ├── partials/
│       ├── productos/
│       ├── ventas/
│       └── dashboard.blade.php
├── routes/
│   ├── console.php
│   └── web.php
├── storage/
├── docs/
│   ├── screenshots/
│   └── exports/
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
└── README.md
```

---

## Modelo de Datos

La aplicación consume un esquema PostgreSQL administrado desde el servicio de base de datos. Las tablas principales del dominio son:

| Tabla | Propósito |
|------|-----------|
| `roles` | Define perfiles de usuario, como administrador y trabajador. |
| `usuarios` | Usuarios internos que pueden iniciar sesión y operar el sistema. |
| `categorias` | Clasificación de productos. |
| `marcas` | Marcas comerciales asociadas al catálogo. |
| `proveedores` | Proveedores vinculados a productos. |
| `productos` | Catálogo principal con descripción, precio, stock, condición y estado. |
| `clientes` | Clientes registrados durante operaciones de venta. |
| `ventas` | Cabecera de ventas completadas o anuladas. |
| `detalle_venta` | Productos vendidos, cantidades, precios históricos y subtotales. |
| `movimientos_inventario` | Bitácora de ventas, reposiciones, hurtos y ajustes. |

---

## Roles y Permisos

| Rol | Acceso |
|-----|--------|
| Administrador | Dashboard, catálogo, ventas, movimientos, reposiciones, hurtos, desactivación, reactivación y eliminación segura de productos. |
| Trabajador | Dashboard, consulta de inventario, registro de ventas, reposición de stock y revisión de reportes operativos. |

Las acciones sensibles se protegen mediante middleware. Por ejemplo, el registro de hurtos y la eliminación física de productos solo están disponibles para administradores.

---

## Funcionalidades Principales

### Dashboard operativo

El dashboard presenta una vista general del inventario y las ventas:

- Productos activos.
- Productos con bajo stock.
- Valor total del inventario.
- Ventas del día.
- Comparación contra el día anterior.
- Gráfico de ventas por día.
- Top de productos más vendidos.
- Distribución de movimientos.
- Valor de inventario por categoría.

### Catálogo de inventario

Permite consultar todos los productos registrados con filtros por nombre, categoría, marca, estado de stock y ordenamiento.

Estados calculados:

| Estado | Regla |
|-------|-------|
| Disponible | Producto activo con stock mayor al mínimo. |
| Bajo stock | Producto activo con stock menor o igual al mínimo y mayor que cero. |
| Agotado | Producto activo con stock igual o menor que cero. |
| Desactivado | Producto oculto del catálogo regular por `activo = false`. |

### Registro de productos

El sistema permite registrar productos con:

- Nombre.
- Descripción.
- Categoría.
- Marca.
- Proveedor.
- Condición.
- Precio unitario.
- Stock actual.
- Stock mínimo.

También incluye creación rápida de categorías, marcas y proveedores mediante modales.

### Edición de productos

Permite actualizar información comercial del producto y reflejar los cambios en el catálogo. La edición mantiene trazabilidad cuando el producto ya tiene operaciones asociadas.

### Venta transaccional

El registro de venta valida cliente, producto, cantidad y método de pago. La operación descuenta stock y registra el movimiento de inventario dentro de una transacción.

```text
Formulario de venta
   ↓
Validación Laravel
   ↓
DB::transaction()
   ↓
Bloqueo del producto con lockForUpdate()
   ↓
Creación o reutilización del cliente
   ↓
Creación de venta y detalle
   ↓
Descuento de stock
   ↓
Movimiento de inventario tipo Venta
   ↓
Confirmación de operación
```

Este flujo evita inconsistencias cuando el stock cambia durante una operación crítica.

### Reposición de stock

Permite sumar unidades al inventario y registrar un movimiento tipo `Reposicion`. La observación es obligatoria para mantener trazabilidad del ingreso.

### Registro de hurto

Permite descontar unidades por pérdida o hurto. Esta operación está restringida al rol administrador y valida que no se descuente más stock del disponible.

### Historial de movimientos

El historial funciona como bitácora del inventario. Registra ventas, reposiciones, hurtos y ajustes. Incluye filtros y exportación en CSV o JSON.

### Resumen de ventas

Permite revisar ventas completadas por día, semana, mes, año o total. También presenta totales por método de pago y exportaciones.

---

## Datos de Prueba y Exportaciones

Los archivos ubicados en `docs/exports/` evidencian el funcionamiento de las exportaciones del sistema.

| Reporte | Formatos | Datos validados |
|--------|----------|-----------------|
| Catálogo de inventario | CSV · JSON | 47 productos registrados. |
| Movimientos de inventario | CSV · JSON | 101 movimientos entre ventas, reposiciones, hurtos y ajustes. |
| Resumen de ventas | CSV · JSON | 7 ventas completadas en el periodo diario de prueba. |

### Indicadores exportados

| Indicador | Valor |
|----------|-------|
| Productos registrados | 47 |
| Movimientos registrados | 101 |
| Ventas completadas en el periodo de prueba | 7 |
| Total vendido en el periodo de prueba | $6,334.87 |
| Valor total aproximado del inventario | $110,453.72 |

### Distribución de estado del catálogo

| Estado | Cantidad |
|-------|----------|
| Disponible | 30 |
| Bajo stock | 15 |
| Agotado | 2 |

---

## Rutas de la Aplicación

| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/login` | Pantalla de inicio de sesión. |
| `POST` | `/login` | Validación de credenciales. |
| `POST` | `/logout` | Cierre de sesión. |
| `GET` | `/` | Redirección al dashboard. |
| `GET` | `/dashboard` | Panel principal con KPIs y gráficos. |
| `GET` | `/reportes` | Redirección al módulo externo configurado en `REPORTES_URL`. |
| `GET` | `/productos` | Catálogo de inventario con filtros. |
| `GET` | `/productos/csv` | Exportación CSV del catálogo. |
| `GET` | `/productos/json` | Exportación JSON del catálogo. |
| `GET` | `/productos/create` | Formulario de registro de producto. |
| `POST` | `/productos` | Guardar producto nuevo. |
| `GET` | `/productos/{producto}` | Detalle del producto. |
| `GET` | `/productos/{producto}/edit` | Formulario de edición. |
| `PUT/PATCH` | `/productos/{producto}` | Actualizar producto. |
| `DELETE` | `/productos/{producto}` | Desactivar producto. Solo administrador. |
| `PATCH` | `/productos/{producto}/reactivar` | Reactivar producto. Solo administrador. |
| `DELETE` | `/productos/{producto}/eliminar` | Eliminación física segura. Solo administrador. |
| `GET` | `/ventas/create` | Formulario de venta. |
| `POST` | `/ventas` | Registrar venta y descontar stock. |
| `GET` | `/ventas/resumen` | Resumen de ventas por periodo. |
| `GET` | `/ventas/resumen/csv` | Exportación CSV de ventas. |
| `GET` | `/ventas/resumen/json` | Exportación JSON de ventas. |
| `GET` | `/movimientos` | Historial de movimientos. |
| `GET` | `/movimientos/csv` | Exportación CSV de movimientos. |
| `GET` | `/movimientos/json` | Exportación JSON de movimientos. |
| `GET` | `/inventario/reposicion` | Formulario de reposición. |
| `POST` | `/inventario/reposicion` | Sumar stock y registrar movimiento. |
| `GET` | `/inventario/hurto` | Formulario de hurto. Solo administrador. |
| `POST` | `/inventario/hurto` | Descontar stock por hurto. Solo administrador. |

---

## Capturas de Pantalla

Las capturas utilizadas para documentar el sistema se encuentran en:

```text
docs/screenshots/
```

### Autenticación

| Login vacío | Login con credenciales |
|-------------|------------------------|
| <img src="docs/screenshots/login-vacio.png" alt="Pantalla de login vacía" width="100%"> | <img src="docs/screenshots/login-con-credenciales.png" alt="Login con credenciales ingresadas" width="100%"> |

### Dashboard

| Dashboard - parte 1 | Dashboard - parte 2 |
|--------------------|--------------------|
| <img src="docs/screenshots/dashboard-parte-1.png" alt="Dashboard con KPIs y gráficos principales" width="100%"> | <img src="docs/screenshots/dashboard-parte-2.png" alt="Dashboard con gráficos complementarios" width="100%"> |

### Catálogo y detalle de productos

| Catálogo de inventario | Detalle de producto |
|------------------------|--------------------|
| <img src="docs/screenshots/catalogo-inventario.png" alt="Catálogo de inventario" width="100%"> | <img src="docs/screenshots/detalle-producto.png" alt="Detalle de producto" width="100%"> |

| Edición de producto | Confirmación de edición |
|---------------------|-------------------------|
| <img src="docs/screenshots/edicion-producto.png" alt="Formulario de edición de producto" width="100%"> | <img src="docs/screenshots/confirmacion-edicion-producto.png" alt="Confirmación de producto actualizado" width="100%"> |

| Cambio reflejado en inventario |
|-------------------------------|
| <img src="docs/screenshots/catalogo-tras-edicion.png" alt="Catálogo después de editar producto" width="100%"> |

### Movimientos y ventas

| Historial de movimientos | Resumen de ventas |
|--------------------------|------------------|
| <img src="docs/screenshots/historial-movimientos.png" alt="Historial de movimientos de inventario" width="100%"> | <img src="docs/screenshots/resumen-ventas.png" alt="Resumen de ventas" width="100%"> |

### Registro de producto

| Formulario | Confirmación |
|------------|--------------|
| <img src="docs/screenshots/registro-producto.png" alt="Formulario de registro de producto" width="100%"> | <img src="docs/screenshots/confirmacion-registro-producto.png" alt="Confirmación de registro de producto" width="100%"> |

| Prueba en catálogo |
|--------------------|
| <img src="docs/screenshots/prueba-registro-producto.png" alt="Producto registrado visible en catálogo" width="100%"> |

### Registro de venta

| Formulario | Confirmación |
|------------|--------------|
| <img src="docs/screenshots/registro-venta.png" alt="Formulario de registro de venta" width="100%"> | <img src="docs/screenshots/confirmacion-registro-venta.png" alt="Confirmación de registro de venta" width="100%"> |

| Prueba en resumen de ventas |
|-----------------------------|
| <img src="docs/screenshots/prueba-registro-venta.png" alt="Venta registrada visible en el resumen" width="100%"> |

### Registro de hurto

| Formulario | Confirmación |
|------------|--------------|
| <img src="docs/screenshots/registro-hurto.png" alt="Formulario de registro de hurto" width="100%"> | <img src="docs/screenshots/confirmacion-registro-hurto.png" alt="Confirmación de registro de hurto" width="100%"> |

| Prueba en movimientos |
|-----------------------|
| <img src="docs/screenshots/prueba-registro-hurto.png" alt="Hurto registrado en historial de movimientos" width="100%"> |

### Reposición de stock

| Formulario | Confirmación |
|------------|--------------|
| <img src="docs/screenshots/registro-reposicion.png" alt="Formulario de reposición de stock" width="100%"> | <img src="docs/screenshots/confirmacion-registro-reposicion.png" alt="Confirmación de reposición" width="100%"> |

| Prueba en movimientos |
|-----------------------|
| <img src="docs/screenshots/prueba-registro-reposicion.png" alt="Reposición registrada en historial de movimientos" width="100%"> |

---

## Requisitos

### Para ejecución con Docker

- Docker Desktop o Docker Engine.
- Docker Compose.
- Acceso a la red Tailscale.
- Base de datos PostgreSQL remota disponible.
- Archivo `.env` configurado con IP, puerto y credenciales de la base.

### Para ejecución local sin Docker

- PHP 8.4 recomendado.
- Composer.
- Extensiones PHP: `pdo_pgsql`, `pgsql`, `mbstring`, `bcmath`, `zip`, `openssl`, `fileinfo`.
- PostgreSQL remoto disponible por Tailscale.

---

## Variables de Entorno

Crear el archivo `.env` a partir del ejemplo:

```bash
cp .env.example .env
```

Configuración principal:

```env
APP_NAME=IceFrame
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=es
APP_TIMEZONE=America/Costa_Rica

DB_CONNECTION=pgsql
DB_HOST=IP_TAILSCALE_DE_JUAN_DIEGO
DB_PORT=5433
DB_DATABASE=iceframe
DB_USERNAME=iceframe
DB_PASSWORD=iceframe_dev
DB_PERSISTENT=true

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file

REPORTES_URL=http://IP_TAILSCALE_DE_REPORTES:PUERTO
```

Notas importantes:

- `DB_HOST` debe apuntar a la IP Tailscale de la máquina donde corre PostgreSQL.
- Si la base de datos está en otra máquina, no usar `localhost`.
- `DB_PORT` debe coincidir con el puerto expuesto por el contenedor PostgreSQL.
- `REPORTES_URL` puede quedar vacío si el módulo externo de reportes todavía no está activo.
- `SESSION_DRIVER=file` evita depender de tablas de sesión dentro de la base compartida.

---

## Instalación y Ejecución con Docker

Desde la raíz del proyecto:

```bash
docker compose up -d --build
```

Ver logs:

```bash
docker compose logs -f
```

Ver contenedores activos:

```bash
docker compose ps
```

Abrir la aplicación:

```text
http://localhost:8000
```

Detener el contenedor:

```bash
docker compose down
```

---

## Instalación y Ejecución Local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan serve --host=0.0.0.0 --port=8000
```

La aplicación queda disponible en:

```text
http://localhost:8000
```

---

## Verificación de Conectividad

Confirmar que la máquina está dentro de la red Tailscale:

```bash
tailscale status
```

Comprobar configuración de Laravel:

```bash
php artisan about
```

Listar rutas registradas:

```bash
php artisan route:list
```

Ver logs en Docker:

```bash
docker compose logs -f app
```

Si la aplicación no conecta a PostgreSQL, revisar:

- Que Tailscale esté activo en ambas máquinas.
- Que `DB_HOST` sea la IP Tailscale correcta.
- Que `DB_PORT` coincida con el puerto expuesto por PostgreSQL.
- Que el contenedor de base de datos esté encendido.
- Que las credenciales de `.env` coincidan con las configuradas por el Estudiante 2.

---

## Exportaciones CSV y JSON

Los módulos principales tienen endpoints de descarga:

| Módulo | CSV | JSON |
|-------|-----|------|
| Catálogo | `/productos/csv` | `/productos/json?descargar=1` |
| Movimientos | `/movimientos/csv` | `/movimientos/json?descargar=1` |
| Resumen de ventas | `/ventas/resumen/csv` | `/ventas/resumen/json?descargar=1` |

Las exportaciones CSV se generan con separador `;` y BOM UTF-8 para facilitar su apertura en Excel. Las exportaciones JSON se generan con formato legible para revisión técnica.

---

## Decisiones Técnicas

- **Aplicación separada de la base de datos**: Laravel no depende de una base local; consume PostgreSQL remoto por red privada.
- **Tailscale**: permite comunicación segura entre máquinas sin publicar la base de datos en Internet.
- **Docker**: encapsula la aplicación y sus dependencias.
- **PostgreSQL**: garantiza integridad relacional y soporte para transacciones.
- **Transacciones**: ventas, hurtos y reposiciones actualizan stock y registran movimientos de forma consistente.
- **Bloqueo de fila**: las ventas usan `lockForUpdate()` para reducir inconsistencias de stock.
- **Desactivación lógica**: eliminar normalmente equivale a marcar `activo = false`, conservando historial.
- **Eliminación física segura**: solo se permite cuando el producto no tiene ventas asociadas.
- **Middleware de administrador**: protege acciones sensibles.
- **Exportaciones abiertas**: CSV y JSON facilitan auditoría y entrega académica.
- **Identidad visual propia**: se implementó una interfaz personalizada con estética azul hielo.

---

## Comandos Útiles

Limpiar cachés de Laravel:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

Reconstruir el contenedor:

```bash
docker compose up -d --build
```

Reiniciar el contenedor:

```bash
docker compose restart
```

Ver logs:

```bash
docker compose logs -f app
```

---

## Autoría y Distribución del Trabajo

| Integrante | Rol dentro del sistema |
|------------|------------------------|
| Karel González | Aplicación Laravel, interfaz IceFrame, catálogo, ventas, movimientos, exportaciones, Dockerfile de la app y conexión con servicios externos. |
| Juan Diego Sotomayor | Base de datos PostgreSQL, datos de prueba, persistencia, módulo de reportes e integración por red Tailscale. |

---

## Estado del Proyecto

El proyecto cuenta con:

- Autenticación.
- Dashboard operativo.
- Catálogo de inventario.
- Detalle de producto.
- Registro y edición de productos.
- Desactivación y reactivación de productos.
- Venta simple con descuento de stock.
- Reposición de stock.
- Registro de hurto.
- Historial de movimientos.
- Resumen de ventas.
- Exportaciones CSV y JSON.
- Integración con base PostgreSQL remota.
- Despliegue mediante Docker Compose.
- Documentación con capturas y datos exportados.

---

## Nombre del Repositorio

```text
proyecto-iceframe-app-inventario
```

Este nombre identifica que el repositorio corresponde a la aplicación principal de inventario del proyecto IceFrame.
