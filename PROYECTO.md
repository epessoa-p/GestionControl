# ControlGestion — Documentación del Proyecto

## Tabla de contenidos

1. [Descripción general](#1-descripción-general)
2. [Stack tecnológico](#2-stack-tecnológico)
3. [Requisitos previos](#3-requisitos-previos)
4. [Instalación y configuración](#4-instalación-y-configuración)
5. [Arquitectura del proyecto](#5-arquitectura-del-proyecto)
6. [Módulos del sistema](#6-módulos-del-sistema)
7. [Modelos y base de datos](#7-modelos-y-base-de-datos)
8. [Rutas](#8-rutas)
9. [Autenticación y permisos](#9-autenticación-y-permisos)
10. [Estructura de vistas](#10-estructura-de-vistas)
11. [Convenciones y patrones de código](#11-convenciones-y-patrones-de-código)
12. [Comandos útiles](#12-comandos-útiles)

---

## 1. Descripción general

**ControlGestion** es un sistema CRM/ERP web para pequeñas y medianas empresas, construido con Laravel 12. Permite gestionar de forma centralizada:

- Control de inventario (entradas y salidas de productos)
- Producción de productos
- Ventas y comisiones a promotores
- Cajas registradoras y caja chica
- Seguimiento de tareas y actividad
- Gestión de personal, sucursales y bodegas
- Reportes exportables (Excel / PDF)
- Plantillas de documentos (Word/PDF)

El sistema es **multi-empresa**: un mismo usuario puede pertenecer a varias empresas y al iniciar sesión selecciona con cuál trabajar. Existe un rol de **super administrador** que tiene acceso global a todas las empresas.

---

## 2. Stack tecnológico

| Capa | Tecnología |
|---|---|
| Framework backend | Laravel 12 (PHP ^8.2) |
| Base de datos | MySQL / MariaDB |
| Frontend | Bootstrap 5 + Bootstrap Icons |
| Gráficos del dashboard | amCharts 5 |
| Build de assets | Vite 7 |
| CSS adicional | Tailwind CSS 4 (vía Vite plugin) |
| HTTP client JS | Axios 1.x |
| Exportación Excel | PhpSpreadsheet 5.x |
| Testing | PHPUnit 11 |

### Dependencias PHP destacadas

```
laravel/framework        ^12.0
laravel/tinker           ^2.10
phpoffice/phpspreadsheet ^5.5
```

### Dependencias JS (dev/build)

```
vite                     ^7.0
laravel-vite-plugin      ^2.0
tailwindcss              ^4.0
@tailwindcss/vite        ^4.0
axios                    ^1.11
concurrently             ^9.0
```

---

## 3. Requisitos previos

- PHP 8.2 o superior con extensiones: `pdo_mysql`, `mbstring`, `xml`, `zip`, `gd`
- Composer 2.x
- Node.js 20+ y npm
- MySQL 8 / MariaDB 10.6+
- Servidor web (Apache / Nginx) o `php artisan serve` para desarrollo

---

## 4. Instalación y configuración

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio> ControlGestion
cd ControlGestion

# 2. Instalar dependencias PHP
composer install

# 3. Copiar y configurar el entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar la base de datos en .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=controlgestion
#    DB_USERNAME=root
#    DB_PASSWORD=

# 5. Ejecutar migraciones y seeders
php artisan migrate --seed

# 6. Instalar dependencias JS y compilar assets
npm install
npm run build

# 7. Iniciar servidor de desarrollo
php artisan serve
```

> **Atajo**: el script `composer run setup` ejecuta los pasos 2–6 automáticamente.

Para desarrollo con hot-reload:

```bash
composer run dev
# Inicia en paralelo: artisan serve, queue:listen, pail (logs), vite dev server
```

---

## 5. Arquitectura del proyecto

```
ControlGestion/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controladores (ver sección 6)
│   │   ├── Middleware/           # Middleware personalizado
│   │   └── Requests/             # Form Requests de validación
│   ├── Models/                   # Modelos Eloquent (ver sección 7)
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   ├── app.php                   # Registro de middleware global
│   └── providers.php
├── config/                       # Configuración de Laravel
├── database/
│   ├── migrations/               # Todas las migraciones
│   ├── seeders/                  # Datos iniciales
│   └── *.sql                     # Scripts SQL de referencia/actualizaciones
├── public/                       # Punto de entrada web (index.php)
├── resources/
│   ├── css/                      # Estilos fuente
│   ├── js/                       # JavaScript fuente (app.js, etc.)
│   └── views/                    # Vistas Blade (ver sección 10)
├── routes/
│   └── web.php                   # Todas las rutas web
├── storage/                      # Logs, archivos generados, caché
└── tests/                        # Tests Feature y Unit
```

### Patrón MVC

El proyecto sigue el patrón MVC estándar de Laravel:

- **Controladores** reciben la petición HTTP, delegan lógica mínima y devuelven una vista o redirigen.
- **Modelos** encapsulan la lógica de negocio, relaciones y métodos de dominio (ej: `recalculateTotal()`, `generateNumber()`).
- **Vistas** Blade con un layout principal (`layouts.app`) que incluye navbar, sidebar y gestión de flash messages.

---

## 6. Módulos del sistema

### Administración

| Módulo | Descripción | Prefijo de ruta |
|---|---|---|
| **Empresas** | Gestión de empresas (solo super admin) | `admin/companies` |
| **Usuarios** | Usuarios del sistema y asignación de roles | `admin/users` |
| **Roles** | Definición de roles y permisos | `admin/roles` |
| **Cargos** | Cargos/puestos de trabajo ligados a roles | `admin/cargos` |
| **Personal** | Empleados de la empresa | `admin/personal` |
| **Sucursales** | Sucursales de la empresa | `admin/branches` |
| **Productos** | Catálogo de productos, costos, stock | `admin/products` |
| **Bodegas** | Almacenes de inventario | `admin/warehouses` |

### Operaciones

| Módulo | Descripción | Prefijo de ruta |
|---|---|---|
| **Seguimientos** | Tareas, actividades y seguimiento de clientes | `trackings` |
| **Entradas** | Ingresos de mercadería al inventario | `entries` |
| **Salidas** | Egresos/despachos de mercadería | `departures` |
| **Producción** | Órdenes de producción con materiales y costos | `productions` |

### Finanzas

| Módulo | Descripción | Prefijo de ruta |
|---|---|---|
| **Cajas** | Cajas registradoras con sesiones y movimientos | `cash-registers` |
| **Caja Chica** | Fondos de caja chica con movimientos | `petty-cash` |
| **Comisiones** | Comisiones generadas por ventas | `commissions` |

### Comercial

| Módulo | Descripción | Prefijo de ruta |
|---|---|---|
| **Promotores** | Vendedores/promotores con tasa de comisión | `promoters` |
| **Ventas** | Registro de ventas con productos, pagos y comisiones | `sales` |

### Reportes y documentos

| Módulo | Descripción | Prefijo de ruta |
|---|---|---|
| **Reportes** | Ventas, inventario, comisiones, movimientos, producción | `reports` |
| **Plantillas** | Plantillas de documentos Word/PDF con placeholders | `document-templates` |

---

## 7. Modelos y base de datos

### Diagrama de relaciones (simplificado)

```
Company ──< Branch ──< CashRegister ──< CashSession ──< CashMovement
   │           └──< PettyCash ──< PettyCashMovement
   │
   ├──< Warehouse ──< (Entry | Departure)
   │        └─< (EntryDetail | DepartureDetail) >─ Product
   │
   ├──< Product
   ├──< Cargo ──< Personal >─ User
   │
   ├──< Production ──< ProductionMaterial >─ Product
   │         └──< ProductionCost
   │
   ├──< Promoter ──< Sale ──< SaleDetail >─ Product
   │                   └──< Commission
   │
   ├──< Tracking
   └──< DocumentTemplate

User >──< Company (many-to-many vía company_user con role_id)
Role >──< Permission (many-to-many vía role_permission)
```

### Modelos principales

#### `User`
- Multi-empresa, con roles por empresa
- `is_super_admin` (bool): acceso global
- Métodos: `hasRoleInCompany()`, `hasPermissionInCompany()`, `getCurrentCompany()`
- Login: acepta **email o nombre de usuario**

#### `Company`
- Entidad raíz del sistema
- `getRoleForUser(User)`: devuelve el rol del usuario en esa empresa
- `getPermissionsForUser(User)`: devuelve los permisos efectivos

#### `Entry` / `Departure`
- Estados: `draft` → `confirmed` / `cancelled`
- Al confirmar, actualiza stock del producto en la bodega
- Números autogenerados: `ENT-YYYYMM-XXXX` / `SAL-YYYYMM-XXXX`

#### `Sale`
- Estados: `pending` → `completed` / `cancelled`
- Métodos: `recalculateTotals()`, `generateNumber()` (prefijo `VTA-`)
- Al completar, puede generar comisión automáticamente si hay promotor asignado
- Métodos de pago: cash, card, transfer, credit, other

#### `CashSession`
- Pertenece a una `CashRegister`
- `calculateExpectedAmount()`: apertura + ingresos - egresos
- `isOpen()`: `status === 'open'`

#### `Production`
- Estados: `planned` → `in_progress` → `completed` / `cancelled`
- `recalculateTotalCost()`: suma materiales + costos adicionales
- Número de lote autogenerado: `PROD-YYYYMM-XXXX`

#### `Promoter`
- Puede estar ligado a un `Personal` (empleado interno)
- `commission_rate`: porcentaje de comisión sobre ventas completadas

#### `DocumentTemplate`
- Tipos: `contrato`, `boleta`, `recibo`, `amortizacion`, `liquidacion`, `otro`
- Soporta placeholders como `{empresa_nombre}`, `{sucursal_nombre}`, `{fecha_actual}`
- Exportable a Word (.docx) y PDF

---

## 8. Rutas

Todas las rutas están definidas en `routes/web.php`. Las rutas de autenticación usan el middleware `guest`; el resto usa `auth`.

### Resumen de grupos de rutas

```
GET  /login                             → LoginController@showLoginForm
POST /login                             → LoginController@login
POST /logout                            → LoginController@logout
GET  /dashboard                         → DashboardController@index

# Admin
GET|POST      admin/companies/...       → CompanyController
GET|POST|PUT  admin/users/...           → UserController
GET|POST|PUT  admin/roles/...           → RoleController
GET|POST|PUT  admin/cargos/...          → CargoController
GET|POST|PUT  admin/personal/...        → PersonalController
GET|POST|PUT  admin/branches/...        → BranchController
GET|POST|PUT  admin/products/...        → ProductController
GET|POST|PUT  admin/warehouses/...      → WarehouseController

# Operaciones
GET|POST|PUT  trackings/...             → TrackingController
GET|POST      entries/...               → EntryController (+confirm, +cancel)
GET|POST      departures/...            → DepartureController (+confirm, +cancel)
GET|POST      productions/...           → ProductionController (+update-status)

# Finanzas
GET|POST|PUT  cash-registers/...        → CashRegisterController
GET|POST      cash-sessions/...         → CashRegisterController (sessionDetail, close, addMovement)
GET|POST      petty-cash/...            → PettyCashController (+add-movement)
GET|POST      commissions/...           → CommissionController (+mark-paid, +mark-paid-bulk)

# Comercial
GET|POST|PUT  promoters/...             → PromoterController
GET|POST      sales/...                 → SaleController (+complete, +cancel)

# Reportes y documentos
GET           reports/...               → ReportController
GET|POST|PUT  document-templates/...    → DocumentTemplateController (+download/word, +export/pdf)
```

> La raíz `/` redirige automáticamente a `/dashboard`.

---

## 9. Autenticación y permisos

### Login

El sistema permite iniciar sesión con **email o nombre de usuario**. La lógica está en `LoginController@login`:

```php
// LoginController: detecta si el campo es email o username
if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
    // busca por email
} else {
    // busca por name (username)
}
```

### Multi-empresa

Tras autenticarse, si el usuario pertenece a más de una empresa, se le presenta una pantalla de selección (`/select-company`). La empresa activa se guarda en sesión. El modelo `User` expone `getCurrentCompany()`.

### Roles y permisos

- Los roles se asignan **por empresa** (tabla pivote `company_user` con columna `role_id`).
- Los permisos se asignan **a roles** (tabla `role_permission`) o directamente a un usuario en una empresa (tabla `user_permission`).
- El super admin (`is_super_admin = true`) bypasea todas las comprobaciones.
- Los cargos (`Cargo`) permiten agrupar puestos de trabajo y asociarlos a un rol de sistema.

---

## 10. Estructura de vistas

Las vistas están en `resources/views/` con la siguiente estructura:

```
views/
├── layouts/
│   └── app.blade.php            # Layout principal (navbar, sidebar, flash messages)
├── auth/
│   └── login.blade.php
├── dashboard/
│   └── index.blade.php          # Dashboard con KPIs y gráficos amCharts 5
├── admin/
│   ├── companies/               # CRUD empresas
│   ├── users/                   # CRUD usuarios
│   ├── roles/                   # CRUD roles y permisos
│   ├── cargos/                  # CRUD cargos
│   ├── personal/                # CRUD empleados
│   ├── branches/                # CRUD sucursales
│   ├── products/                # CRUD productos
│   └── warehouses/              # CRUD bodegas
├── trackings/                   # Seguimientos
├── entries/                     # Entradas de inventario
├── departures/                  # Salidas de inventario
├── productions/                 # Producción
├── cash-registers/              # Cajas registradoras + sesiones
├── petty-cash/                  # Caja chica
├── promoters/                   # Promotores
├── sales/                       # Ventas
├── commissions/                 # Comisiones
├── reports/                     # Reportes (index + 5 sub-reportes)
└── document-templates/          # Plantillas de documentos
```

### Patrón de vistas por módulo

Cada módulo sigue este patrón consistente:

```
modulo/
├── index.blade.php              # Listado paginado con filtros y tabla
├── show.blade.php               # Detalle con KPI cards, info sidebar y tablas relacionadas
├── create.blade.php             # Wrapper: @extends('layouts.app') + @include('modulo.form')
├── edit.blade.php               # Wrapper: @extends('layouts.app') + @include('modulo.form')
└── form.blade.php               # Formulario reutilizable (partial, sin layout propio)
```

> **Importante**: `form.blade.php` es un partial (no tiene `@extends`). El layout lo proveen los wrappers `create.blade.php` y `edit.blade.php`. Los controladores deben retornar `view('modulo.create')` o `view('modulo.edit')`, **nunca** `view('modulo.form')` directamente.

### Guía de diseño (UX)

El proyecto aplica un sistema de diseño consistente en todas las vistas:

- **Layout principal**: `@extends('layouts.app')` + `@section('page')`
- **Flash messages**: gestionados globalmente en `layouts/app.blade.php`, no en vistas individuales
- **Iconografía**: Bootstrap Icons (`bi-*`)
- **KPI cards**: círculo de 48px con icono de color + etiqueta + valor numérico
- **Info sidebars**: `list-unstyled` con `border-bottom` e icono prefijo por campo
- **Formularios simples**: centrados en `col-lg-6` a `col-lg-8` con card
- **Cabeceras de sección**: `<h6 class="fw-bold text-primary"><i class="bi bi-* me-1"></i> Título</h6>` + `<hr>`
- **Tablas dinámicas**: header de columnas descriptivo, campo readonly con `bg-light`
- **Empty states**: `<i class="bi bi-inbox fs-1 d-block mb-2"></i>` centrado

---

## 11. Convenciones y patrones de código

### Controladores

- Usan `private function getCompanyId()` para obtener el ID de la empresa activa según si el usuario es super admin o no.
- Usan `private function authorizeRecord($record)` para verificar que el registro pertenece a la empresa activa.
- Errores de negocio se capturan con `try/catch (\Throwable $e)` y se loguean con `Log::error()`.
- Validaciones usan `$request->validate([...])` o Form Requests en `app/Http/Requests/`.

### Modelos

- Usan `$fillable` (no `$guarded`).
- Relaciones en `camelCase`: `belongsTo`, `hasMany`, `belongsToMany`.
- Constantes de dominio (estados, tipos, colores) definidas como arrays estáticos en el modelo.
- Métodos de generación de números (`generateNumber`, `generateBatchNumber`) usan el formato `PREFIJO-AAAAMM-NNNN`.

### Nomenclatura

| Elemento | Convención |
|---|---|
| Modelos | `PascalCase` singular — `Sale`, `CashSession` |
| Controladores | `PascalCase` + `Controller` — `SaleController` |
| Tablas BD | `snake_case` plural — `sales`, `cash_sessions` |
| Rutas nombradas | `kebab-case` con punto — `cash-registers.show` |
| Vistas | `kebab-case` carpeta, `snake_case` archivo — `cash-registers/form.blade.php` |
| Variables en vistas | `camelCase` — `$cashRegister`, `$saleDetails` |

### Base de datos

- Todas las tablas tienen `id` autoincremental, `created_at`, `updated_at`.
- Las claves foráneas siguen el patrón `tabla_id` (ej: `company_id`, `warehouse_id`).
- Los campos de auditoría (`created_by`, `opened_by`, etc.) apuntan a `users.id`.
- Los campos monetarios son `decimal(12,2)`.

---

## 12. Comandos útiles

```bash
# Servidor de desarrollo
php artisan serve

# Compilar assets (producción)
npm run build

# Compilar assets (desarrollo, con hot-reload)
npm run dev

# Ejecutar migraciones
php artisan migrate

# Ejecutar migraciones con datos de prueba
php artisan migrate --seed

# Revertir y re-ejecutar todas las migraciones
php artisan migrate:fresh --seed

# Limpiar caché de configuración, rutas y vistas
php artisan optimize:clear

# Ver todas las rutas registradas
php artisan route:list

# Consola interactiva (Tinker)
php artisan tinker

# Ejecutar tests
php artisan test
# o
composer run test

# Iniciar todos los servicios de desarrollo en paralelo
composer run dev
```

---

> Documentación generada para el proyecto **ControlGestion** — Laravel 12 / PHP 8.2+
