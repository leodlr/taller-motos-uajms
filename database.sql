-- ============================================================================
-- Proyecto: Sistema Web de Control de Órdenes de Trabajo e Inventario de Repuestos
-- Institución: Universidad Autónoma Juan Misael Saracho (UAJMS)
-- Facultad: Facultad de Ingeniería Recursos Naturales y Tecnología
-- Carrera: Ingeniería Informática — Asignatura: Taller I
-- Motor: MySQL 8.x (InnoDB, utf8mb4_unicode_ci) — Normalizado en 3FN
-- ============================================================================

CREATE DATABASE IF NOT EXISTS taller_motos_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE taller_motos_db;

-- Limpieza ordenada respetando integridad referencial
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS detalle_repuestos;
DROP TABLE IF EXISTS ordenes_trabajo;
DROP TABLE IF EXISTS repuestos;
DROP TABLE IF EXISTS motos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. TABLA: usuarios
-- ============================================================================
CREATE TABLE usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('ADMIN', 'MECANICO') NOT NULL DEFAULT 'MECANICO',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT uq_usuarios_username UNIQUE (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABLA: clientes
-- ============================================================================
CREATE TABLE clientes (
    id_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ci VARCHAR(20) NOT NULL,
    nombre_completo VARCHAR(120) NOT NULL,
    telefono VARCHAR(25) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_clientes_ci UNIQUE (ci)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TABLA: motos
-- ============================================================================
CREATE TABLE motos (
    id_moto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNSIGNED NOT NULL,
    placa VARCHAR(15) NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(60) NOT NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    color VARCHAR(30) NOT NULL,
    CONSTRAINT uq_motos_placa UNIQUE (placa),
    CONSTRAINT fk_motos_cliente FOREIGN KEY (id_cliente)
        REFERENCES clientes (id_cliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. TABLA: repuestos
-- ============================================================================
CREATE TABLE repuestos (
    id_repuesto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    CONSTRAINT uq_repuestos_codigo UNIQUE (codigo),
    CONSTRAINT chk_repuestos_precio CHECK (precio_unitario >= 0),
    CONSTRAINT chk_repuestos_stock CHECK (stock_actual >= 0),
    CONSTRAINT chk_repuestos_minimo CHECK (stock_minimo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. TABLA: ordenes_trabajo
-- ============================================================================
CREATE TABLE ordenes_trabajo (
    id_orden INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_correlativo VARCHAR(20) NOT NULL,
    id_moto INT UNSIGNED NOT NULL,
    id_mecanico INT UNSIGNED NOT NULL,
    fecha_ingreso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega DATETIME NULL,
    falla_reportada TEXT NOT NULL,
    diagnostico TEXT NULL,
    costo_mano_obra DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estado ENUM('Recibida', 'En diagnóstico', 'En reparación', 'Lista para entrega', 'Entregada')
        NOT NULL DEFAULT 'Recibida',
    CONSTRAINT uq_ordenes_correlativo UNIQUE (codigo_correlativo),
    CONSTRAINT chk_ordenes_mano_obra CHECK (costo_mano_obra >= 0),
    CONSTRAINT chk_ordenes_total CHECK (total >= 0),
    CONSTRAINT fk_ordenes_moto FOREIGN KEY (id_moto)
        REFERENCES motos (id_moto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_ordenes_mecanico FOREIGN KEY (id_mecanico)
        REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    INDEX idx_ordenes_estado (estado),
    INDEX idx_ordenes_fecha_ingreso (fecha_ingreso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. TABLA: detalle_repuestos
-- ============================================================================
CREATE TABLE detalle_repuestos (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_orden INT UNSIGNED NOT NULL,
    id_repuesto INT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    precio_aplicado DECIMAL(10, 2) NOT NULL,
    CONSTRAINT chk_detalle_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_detalle_precio CHECK (precio_aplicado >= 0),
    CONSTRAINT fk_detalle_orden FOREIGN KEY (id_orden)
        REFERENCES ordenes_trabajo (id_orden)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_detalle_repuesto FOREIGN KEY (id_repuesto)
        REFERENCES repuestos (id_repuesto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATOS DE PRUEBA (SEED)
-- Contraseñas de prueba (bcrypt compatible con PHP password_verify):
--   admin    -> admin123
--   mecanico1 / mecanico2 -> mecanico123
-- ============================================================================

INSERT INTO usuarios (nombre, username, password_hash, rol, activo) VALUES
('Administrador del Taller', 'admin', '$2y$10$jHtYJeb9ZCL4wRe5Qd4JuOUIKy5ywA0TCx9IcpSUdmkVjXVkejFrm', 'ADMIN', 1),
('Carlos Mendoza', 'mecanico1', '$2y$10$e.wkjGg2QF/BWxZPdJ4zv.hWrqzLUVedIxZpWNgbni0Ajd6Qv1HVW', 'MECANICO', 1),
('Luis Fernández', 'mecanico2', '$2y$10$e.wkjGg2QF/BWxZPdJ4zv.hWrqzLUVedIxZpWNgbni0Ajd6Qv1HVW', 'MECANICO', 1);

INSERT INTO clientes (ci, nombre_completo, telefono, fecha_registro) VALUES
('7145820', 'Juan Carlos Rojas', '72914501', '2026-10-12 09:15:00'),
('5823411', 'María Elena Paredes', '71893210', '2026-10-20 14:30:00'),
('6490123', 'Roberto Arce Mamani', '76120455', '2026-11-03 10:05:00'),
('8312456', 'Diego Armando Soto', '74509812', '2026-11-15 16:40:00');

INSERT INTO motos (id_cliente, placa, marca, modelo, anio, color) VALUES
(1, '4821-ABC', 'Honda', 'CG 150 Titan', 2021, 'Rojo'),
(2, '5310-TJA', 'Yamaha', 'YBR 125Z', 2022, 'Azul'),
(3, '3904-KLP', 'Suzuki', 'GN 125', 2020, 'Negro'),
(4, '6122-YCB', 'Bajaj', 'Boxer BM 150', 2023, 'Negro/Gris'),
(1, '5190-MTR', 'Haojue', 'NK 150', 2024, 'Blanco');

-- Incluye repuestos en stock normal y en stock crítico (stock_actual <= stock_minimo)
-- para probar inmediatamente el Reporte de Alerta de Stock Crítico.
INSERT INTO repuestos (codigo, nombre, precio_unitario, stock_actual, stock_minimo) VALUES
('REP-001', 'Aceite de Motor 4T 20W-50 (1L)', 55.00, 18, 6),
('REP-002', 'Bujía NGK DPR8EA-9', 28.00, 4, 5),          -- Stock crítico
('REP-003', 'Kit de Arrastre Completo 150cc', 165.00, 3, 3), -- Stock crítico
('REP-004', 'Pastillas de Freno Delantero', 45.00, 12, 4),
('REP-005', 'Filtro de Aire Estándar', 35.00, 2, 5),       -- Stock crítico
('REP-006', 'Cámara Reforzada Aro 18', 40.00, 9, 4),
('REP-007', 'Cable de Embrague Universal', 30.00, 1, 4),   -- Stock crítico
('REP-008', 'Foco Halógeno Faro Delantero 12V', 25.00, 10, 3);

-- Órdenes de trabajo en distintos estados para probar historial, ingresos y flujo operativo
INSERT INTO ordenes_trabajo (
    codigo_correlativo, id_moto, id_mecanico, fecha_ingreso, fecha_entrega,
    falla_reportada, diagnostico, costo_mano_obra, total, estado
) VALUES
(
    'OT-2026-0001', 1, 2, '2026-11-10 08:45:00', '2026-11-10 15:20:00',
    'Mantenimiento preventivo general y ruido en la cadena de transmisión.',
    'Cambio de aceite 20W-50, reemplazo de kit de arrastre y tensado de cadena.',
    80.00, 300.00, 'Entregada'
),
(
    'OT-2026-0002', 2, 3, '2026-11-14 09:30:00', '2026-11-14 17:00:00',
    'Freno delantero largo y dificultad al encender en frío.',
    'Cambio de pastillas delanteras, reemplazo de bujía NGK y limpieza de carburador.',
    70.00, 143.00, 'Entregada'
),
(
    'OT-2026-0003', 3, 2, '2026-11-22 10:15:00', NULL,
    'Pérdida de potencia en subida y cable de embrague duro.',
    'Filtro de aire obstruido y cable de embrague deshilachado. En proceso de cambio.',
    60.00, 125.00, 'En reparación'
),
(
    'OT-2026-0004', 4, 3, '2026-11-24 08:30:00', NULL,
    'Pinchazo en rueda trasera y foco delantero quemado.',
    NULL,
    0.00, 0.00, 'Recibida'
);

-- Detalle de repuestos usados (los subtotales coinciden exactamente con ordenes_trabajo.total)
-- OT-2026-0001: 80 (mano obra) + 55 (1x aceite) + 165 (1x kit arrastre) = 300.00
-- OT-2026-0002: 70 (mano obra) + 45 (1x pastillas) + 28 (1x bujía) = 143.00
-- OT-2026-0003: 60 (mano obra) + 35 (1x filtro) + 30 (1x cable embrague) = 125.00
INSERT INTO detalle_repuestos (id_orden, id_repuesto, cantidad, precio_aplicado) VALUES
(1, 1, 1, 55.00),
(1, 3, 1, 165.00),
(2, 4, 1, 45.00),
(2, 2, 1, 28.00),
(3, 5, 1, 35.00),
(3, 7, 1, 30.00);
