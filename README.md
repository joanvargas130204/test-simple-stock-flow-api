# Simple Stock Flow — Backend API (Laravel Onion Architecture)

Backend de la plataforma **Simple Stock Flow** desarrollado con **PHP 8.2+ y Laravel 11** bajo una estricta **Arquitectura Onion (4 Anillos)**.

---

## 🏛️ Estructura de Capas (Onion Architecture)

```text
app/
├── Domain/                  <-- Anillo 1: Núcleo Puro (PHP 8.2+, 0 dependencias externas)
│   ├── Exception/           <-- Violaciones de reglas de negocio
│   ├── Model/               <-- Entidades (Product, Sale, SaleItem, Category, User)
│   └── ValueObject/         <-- Money (Brick\Math\BigDecimal), Quantity, Username, Role
│
├── Application/             <-- Anillo 2: Casos de Uso y Puertos
│   ├── DTO/                 <-- Comandos, Vistas y Resultados (ProductView, SaleView, SalesReport...)
│   ├── Exception/           <-- ConcurrencyConflict
│   ├── Model/               <-- DateRange, PageRequest
│   ├── Ports/
│   │   ├── Inbound/         <-- Interfaces de Casos de Uso (Authenticate, ManageProducts, PlaceSale...)
│   │   └── Outbound/        <-- Interfaces de Salida (Repositories, UnitOfWork, TokenGenerator...)
│   └── UseCase/             <-- Implementaciones de Servicios de Aplicación
│
├── Infrastructure/          <-- Anillo 3: Adaptadores de Salida (I/O, Framework, Base de Datos)
│   ├── Adapters/            <-- SystemClock
│   ├── Persistence/         <-- Modelos Eloquent, Mappers, Repositorios, LaravelUnitOfWork, ReportQuery
│   ├── Security/            <-- JwtTokenGenerator (HS256), Argon2PasswordHasher
│   └── Storage/             <-- LocalFileStorage
│
├── Presentation/            <-- Anillo 4: Adaptadores de Entrada (HTTP REST API)
│   └── Http/
│       ├── Controller/      <-- Controladores REST (Auth, Product, Category, Sale, Report, Health, Media)
│       ├── Middleware/      <-- JwtAuthMiddleware, RequireAdminRoleMiddleware
│       ├── ProblemDetails/  <-- Renderizadores RFC 7807 (400, 422, 409, 500 y 401/403/404/405 vacíos)
│       └── Request/         <-- Validaciones de entrada (ApiRequest, FormRequests)
│
└── Bootstrap/               <-- Composición & Inyección de Dependencias
    └── PortBindingsServiceProvider.php
```

---

## 📋 Respuestas a las Preguntas de Evaluación Arquitectónica

### 1. ¿Cómo garantiza la Arquitectura Onion la independencia del dominio respecto a Laravel?
El código dentro de `app/Domain/` está escrito en **PHP 8.2 puro** sin importar ninguna clase ni facade del espacio de nombres `Illuminate\*` ni de librerías externas (excepto `brick/math` para el Value Object `Money`). Las entidades (`Product`, `Sale`, `User`, etc.) no heredan de `Illuminate\Database\Eloquent\Model`. Toda comunicación hacia el exterior se realiza a través de **Puertos Outbound** ubicados en `app/Application/Ports/Outbound/`, y la inyección concreta se resuelve en `PortBindingsServiceProvider` mediante el Principio de Inversión de Dependencias (DIP).

### 2. ¿Cómo se resolvió la concurrencia optimista y el manejo de versiones?
Se implementó un testigo de concurrencia mediante la columna `version INT NOT NULL` en la tabla `product`. Esta columna es un detalle exclusivo de persistencia (`ProductModel`), por lo que la entidad de dominio `Product` no la conoce. En cada actualización, el repositorio ejecuta `UPDATE product SET ... version = version + 1 WHERE id = ? AND version = ?`. Si las filas afectadas son 0, el repositorio lanza `ConcurrencyConflict`. El caso de uso `PlaceSaleService` reintenta la operación completa hasta **3 veces** con retroceso exponencial antes de devolver `409 Conflict`.

### 3. ¿Cómo se estructuraron las migraciones y la semilla de datos?
Las migraciones residen en `database/migrations/`:
- `2026_10_02_000001_initial_schema.php`: Crea las 5 tablas en singular (`category`, `product`, `user`, `sale`, `sale_item`), 25 columnas, 21 restricciones (5 PK, 4 FK con políticas `RESTRICT`/`CASCADE`, 3 UNIQUE y 9 restricciones `CHECK` aplicadas a nivel de motor) y 13 índices dimensionados para los patrones de acceso.
- `2026_10_02_000002_seed_categories.php`: Siembra las 5 categorías fijas con UUIDs literales constantes.
- El usuario `admin` no se siembra por migración SQL, sino que se provisiona de forma segura en el arranque mediante variables de entorno (`ADMIN_EMAIL`, `ADMIN_PASSWORD`) hasheadas con `Argon2id`.

### 4. ¿Cómo se implementó la seguridad JWT y control de acceso basado en roles?
Se utilizó firma HMAC-SHA256 (`HS256`) con clave simétrica en `JwtTokenGenerator` (`firebase/php-jwt`). Las rutas están protegidas por omisión mediante `JwtAuthMiddleware`, salvo `POST /api/auth/login`, `GET /health` y `GET /media/{key}`. El middleware valida el token con 30 segundos de margen (*leeway*) y propaga `auth_user_id`, `auth_username` y `auth_role`. Las operaciones de administración están restringidas con `RequireAdminRoleMiddleware`, devolviendo `403 Forbidden` con cuerpo vacío (`Content-Length: 0`) ante intentos no autorizados.

### 5. ¿Cómo se garantiza la estabilidad y exactitud de los reportes de ventas (DP-01, DP-02, DP-03)?
Al momento de registrar una venta, la línea `sale_item` **congela** una copia inmutable de `product_name`, `unit_price` y `category_name`. La consulta `MySqlSalesReportQuery` ejecuta la agregación directamente en MySQL mediante `GROUP BY sale_item.product_id, sale_item.product_name, sale_item.category_name` y `ORDER BY revenue DESC`. Debido a que los datos están congelados, modificaciones posteriores al catálogo no alteran los resultados históricos de períodos cerrados, cumpliendo las reglas DP-01 a DP-04 y CA-06.

---

## 🚀 Ejecución

```bash
# Instalar dependencias
composer install

# Ejecutar migraciones
php artisan migrate

# Iniciar servidor local
php artisan serve --port=8000
```
