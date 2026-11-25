# Contexto del Proyecto: Sistema Web de Taller de Motos (Taller I - UAJMS)

## Información General
- **Institución académica:** Universidad Autónoma Juan Misael Saracho (UAJMS) — Facultad de Ingeniería Recursos Naturales y Tecnología — Carrera de Ingeniería Informática (Taller I).
- **Objetivo:** Desarrollar el prototipo funcional (Fase IV) de un Sistema Web de Control de Órdenes de Trabajo e Inventario de Repuestos para un taller mecánico de motocicletas, estrictamente alineado con la ERS (Fase II) y Documentación Técnica (Fase III) en `/docs`.

## Stack Tecnológico Obligatorio
- **Backend:** PHP 8.x (arquitectura MVC ligera o modular limpia, usando **PDO** exclusivamente).
- **Base de Datos:** MySQL 8.x (motor InnoDB, codificación `utf8mb4_unicode_ci`).
- **Frontend:** HTML5, CSS3, Bootstrap 5 y JavaScript (validaciones en cliente + fetch si aplica).
- **Entorno / Despliegue:** Docker (`docker-compose.yml` con servicios `app` PHP-Apache y `db` MySQL 8) + script `database.sql` con estructura y datos de prueba.

## Esquema de Base de Datos (3FN — Respetar nombres exactos)
1. `usuarios`: `id_usuario` (PK), `nombre`, `username` (UNIQUE), `password_hash`, `rol` (`ENUM('ADMIN','MECANICO')`), `activo`.
2. `clientes`: `id_cliente` (PK), `ci` (UNIQUE), `nombre_completo`, `telefono`, `fecha_registro`.
3. `motos`: `id_moto` (PK), `id_cliente` (FK), `placa` (UNIQUE), `marca`, `modelo`, `anio`, `color`.
4. `repuestos`: `id_repuesto` (PK), `codigo` (UNIQUE), `nombre`, `precio_unitario`, `stock_actual`, `stock_minimo`.
5. `ordenes_trabajo`: `id_orden` (PK), `codigo_correlativo` (UNIQUE), `id_moto` (FK), `id_mecanico` (FK -> usuarios), `fecha_ingreso`, `fecha_entrega`, `falla_reportada`, `diagnostico`, `costo_mano_obra`, `total`, `estado` (`ENUM('Recibida','En diagnóstico','En reparación','Lista para entrega','Entregada')`).
6. `detalle_repuestos`: `id_detalle` (PK), `id_orden` (FK), `id_repuesto` (FK), `cantidad`, `precio_aplicado`.

## Requisitos Funcionales y Roles
- **Roles:**
  - `ADMIN`: Acceso total (usuarios, clientes, motos, repuestos, precios, órdenes y reportes).
  - `MECANICO`: Solo consulta órdenes asignadas, actualiza estado/diagnóstico, agrega repuestos usados y consulta stock (sin borrar registros ni modificar precios).
- **Módulos:** Login con sesiones seguras, CRUD de Clientes, CRUD de Motos, CRUD de Repuestos, Gestión de Órdenes de Trabajo, y 2 Reportes Operativos:
  1. Historial de reparaciones e ingresos filtrado por rango de fechas y/o placa/cliente.
  2. Alerta de repuestos con stock crítico (`stock_actual <= stock_minimo`).

## Reglas de Negocio Innegociables
1. **Correlativo único:** Cada orden genera un código automático `OT-YYYY-XXXX` (ej. `OT-2026-0001`).
2. **Unicidad:** No permitir duplicar `ci` de clientes ni `placa` de motos.
3. **Descuento transaccional de stock:** Al agregar un repuesto a una orden, ejecutar una transacción MySQL (`BEGIN` / `COMMIT` / `ROLLBACK`), validar `cantidad <= stock_actual`, descontar stock, guardar `precio_aplicado` histórico y recalcular `total` de la orden.
4. **Cierre de orden:** No permitir cambiar estado a `Entregada` sin diagnóstico técnico registrado.

## Reglas de Seguridad y Calidad
- Usar **siempre** consultas preparadas con PDO (prevención de Inyección SQL).
- Escapar todas las salidas HTML con `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` (prevención de XSS).
- Almacenar contraseñas con `password_hash()` (bcrypt) y verificar con `password_verify()`.
- Validar datos tanto en cliente (HTML5/JS) como en servidor (PHP).
- No inventar tablas ni columnas fuera del esquema definido arriba.