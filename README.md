# Sistema Web de Control de Órdenes de Trabajo e Inventario de Repuestos — Taller de Motos

Taller I — Ingeniería Informática, UAJMS. Prototipo funcional (Fase IV), alineado con la ERS (Fase II) y la Documentación Técnica (Fase III) de la carpeta [`docs/`](docs/).

**Stack:** PHP 8.x (PDO, MVC ligero) · MySQL 8.x / MariaDB (InnoDB, `utf8mb4_unicode_ci`) · HTML5 + Bootstrap 5 + JavaScript · Docker Compose o XAMPP.

## Credenciales de prueba

| Rol | Usuario | Contraseña |
|---|---|---|
| ADMIN | `admin` | `admin123` |
| MECANICO | `mecanico1` / `mecanico2` | `mecanico123` |

## Puesta en marcha

### Opción A — XAMPP (MySQL local)
1. Inicie **MySQL** desde el panel de XAMPP.
2. Importe la base: `mysql -uroot --default-character-set=utf8mb4 < database.sql`
   (el script crea `taller_motos_db`, las tablas y los datos de prueba).
3. Inicie el servidor PHP (ajuste la ruta de XAMPP):
   ```
   d:\programas\xampp\php\php.exe -S localhost:8080 -t app\public
   ```
4. Abra http://localhost:8080

Por defecto la app se conecta a `127.0.0.1:3306` con `root` sin contraseña. Para cambiarlo defina las variables de entorno `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

### Opción B — Docker
```
docker compose up -d --build
```
App en http://localhost:8080 · MySQL en el puerto 3307. `database.sql` se carga automáticamente la primera vez (para reiniciar la base: `docker compose down -v`).

## Estructura

```
app/
  config/db.php          Conexión PDO (excepciones, prepares nativos, utf8mb4)
  public/                Document root: index.php (router) y assets/
  src/
    Core/                Auth (sesiones, roles, CSRF), Controller base, helpers (e())
    Controllers/         Auth, Usuario, Cliente, Moto, Repuesto, Orden, Reporte
    Models/              Acceso a datos con consultas preparadas
    Views/               Plantillas PHP + layout Bootstrap (navbar según rol)
database.sql             Estructura (3FN) + datos de prueba
docker-compose.yml, Dockerfile
docs/                    Fases I a III y guía del proyecto
```

## Roles

| Módulo | ADMIN | MECANICO |
|---|---|---|
| Usuarios y roles (RF-02) | CRUD / desactivar | — |
| Clientes y Motos (RF-03/04) | CRUD | Solo consulta |
| Repuestos (RF-05) | CRUD y precios | Consulta de stock (sin precios) |
| Órdenes de trabajo (RF-06/07) | Todas: crear, asignar, editar, cerrar | Solo las asignadas: estado, diagnóstico y repuestos |
| Reportes (RF-08/09) | Historial e ingresos; stock crítico | — |

## Reglas de negocio implementadas

- **RN-01** Correlativo automático `OT-AAAA-NNNN`, no editable.
- **RN-02** `ci` de clientes, `placa` de motos (y `codigo` de repuestos, `username`) únicos: validación previa + restricción UNIQUE.
- **RN-03** Al agregar un repuesto: transacción (`beginTransaction`/`commit`/`rollBack`), cantidad > 0 y ≤ `stock_actual`, descuento de stock y recálculo del total.
- **RN-04** Repuestos con `stock_actual <= stock_minimo` marcados como críticos (listado y reporte).
- **RN-05** No se puede pasar a `Entregada` sin diagnóstico técnico y sin mano de obra > 0 o al menos un repuesto.
- **RN-06** `precio_aplicado` guarda el precio vigente al consumir el repuesto.

## Seguridad

Consultas preparadas PDO en todo el código · salidas escapadas con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` · contraseñas con `password_hash()` / `password_verify()` (bcrypt) · sesiones con cookie `HttpOnly` + `SameSite=Strict`, regeneración de ID al iniciar sesión y expiración por inactividad · token CSRF en todos los formularios POST · autorización por rol validada en el servidor en cada petición · cuentas desactivadas pierden el acceso de inmediato · validación en cliente (HTML5/JS) y en servidor (PHP).

## Respaldo (RNF-04)

```
mysqldump -uroot --default-character-set=utf8mb4 --routines taller_motos_db > respaldo.sql
```
