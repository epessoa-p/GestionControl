# Sistema CRM - Gestión y Control Multi-Empresa

Un sistema base de gestión y control desarrollado con **Laravel 12** y **Bootstrap 5**, con soporte para múltiples empresas, control de acceso basado en roles y permisos.

## Características

- **Autenticación segura** con contraseñas bcrypt
- **Sistema multi-empresa** - Un usuario puede pertenecer a varias empresas con roles diferentes
- **Roles flexibles**: Super Admin, Admin, Gerente, Cajero, Empleado
- **Control de permisos granular** por rol y empresa
- **Gestión de personal y cargos**
- **Gestión de sucursales y almacenes**
- **Gestión de productos**
- **Plantillas de documentos** con variables dinámicas
- **Dashboard dinámico** con estadísticas según el rol
- **Interfaz amigable** con Bootstrap 5
- **Arquitectura MVC limpia y escalable**

---

## Instalación Rápida

### Requisitos Previos

- **PHP 8.2+**
- **MySQL 5.7+** o MariaDB
- **Composer**
- **Node.js** (opcional, para assets)

### 1. Instalar Dependencias PHP

```bash
composer install
```

### 2. Configurar Base de Datos

```bash
# Crear base de datos MySQL
mysql -u root -p
CREATE DATABASE controlgestion_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Ejecutar migraciones y seeders
php artisan migrate --seed
```

### 3. Generar la Clave de Aplicación

```bash
php artisan key:generate
```

### 4. Iniciar el Servidor

```bash
php artisan serve
```

Acceder a: **http://localhost:8000**

---

## Credenciales de Prueba

### Super Administrador (Acceso Global)
```
Email: superadmin@sistema.com
Contraseña: Admin@1234
```

### Administrador de Empresa
```
Email: admin@empresademo.com
Contraseña: Admin@1234
```

---

## Estructura del Proyecto

```
ControlGestion/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── DocumentTemplates/DocumentTemplateController.php
│   │   │   └── Admin/
│   │   │       ├── CompanyController.php
│   │   │       ├── UserController.php
│   │   │       ├── RoleController.php
│   │   │       ├── CargoController.php
│   │   │       ├── PersonalController.php
│   │   │       ├── BranchController.php
│   │   │       ├── ProductController.php
│   │   │       └── WarehouseController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   ├── CheckCompany.php
│   │   │   └── CheckPermission.php
│   │   └── Requests/
│   └── Models/
│       ├── User.php
│       ├── Company.php
│       ├── Role.php
│       ├── Permission.php
│       ├── Branch.php
│       ├── Cargo.php
│       ├── Personal.php
│       ├── Product.php
│       ├── Warehouse.php
│       └── DocumentTemplate.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── schema_cleanup_remove_loans.sql
├── resources/views/
│   ├── layouts/
│   ├── auth/
│   ├── dashboard/
│   ├── admin/
│   │   ├── companies/
│   │   ├── users/
│   │   ├── roles/
│   │   ├── cargos/
│   │   ├── personals/
│   │   ├── branches/
│   │   ├── products/
│   │   └── warehouses/
│   ├── document-templates/
│   └── errors/
├── routes/web.php
└── bootstrap/app.php
```

---

## Estructura de Base de Datos

### Tablas Principales

- **users** - Usuarios del sistema
- **companies** - Empresas/Negocios
- **roles** - Roles disponibles
- **permissions** - Permisos granulares
- **company_user** (Pivot) - Relación usuario-empresa-rol
- **role_permission** (Pivot) - Relación rol-permiso
- **user_permission** (Pivot) - Permisos adicionales por usuario/empresa
- **branches** - Sucursales por empresa
- **cargos** - Cargos / puestos de trabajo
- **personals** - Registro de personal
- **products** - Productos
- **warehouses** - Almacenes
- **document_templates** - Plantillas de documentos

---

## Roles y Permisos

| Rol | Descripción |
|-----|-------------|
| **Super Admin** | Acceso completo a todo el sistema |
| **Admin** | Administrador de empresa específica |
| **Gerente** | Gestión operativa |
| **Cajero** | Operaciones de caja |
| **Empleado** | Usuario básico |

### Módulos de Permisos

- **companies** - Ver, Crear, Editar, Eliminar empresas
- **users** - Ver, Crear, Editar, Eliminar usuarios
- **roles** - Ver, Crear, Editar, Eliminar roles

---

## Configuración del .env

```env
APP_NAME="Sistema CRM"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=controlgestion_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## Comandos Útiles

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Ver todas las rutas
php artisan route:list
```

---

## Seguridad

- Autenticación con contraseñas bcrypt
- Middleware de autorización por rol y permiso
- Validación de requests con Form Requests
- CSRF protection
- Control de acceso basado en empresa
- Super Admin para gestión global
