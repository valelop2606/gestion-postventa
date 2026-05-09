CREATE DATABASE gestion-postventa;

-- =========================
-- USUARIO
-- =========================

CREATE TABLE usuario (
    id_usuario SERIAL PRIMARY KEY,

    correo VARCHAR(100) UNIQUE NOT NULL,

    contrasena VARCHAR(255) NOT NULL,

    rol VARCHAR(20) NOT NULL
);

-- =========================
-- CLIENTE
-- =========================

CREATE TABLE cliente (
    id_cliente SERIAL PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NOT NULL,

    ciudad VARCHAR(50),
    pais VARCHAR(50),

    direccion VARCHAR(150),

    id_usuario INT UNIQUE,

    FOREIGN KEY (id_usuario)
    REFERENCES usuario(id_usuario)
);

-- =========================
-- TECNICO
-- =========================

CREATE TABLE tecnico (
    id_tecnico SERIAL PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    fecha_nacimiento DATE,

    correo VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NOT NULL,

    ciudad VARCHAR(50),
    pais VARCHAR(50),

    direccion VARCHAR(150),

    fecha_contratacion DATE
);

-- =========================
-- PRODUCTO CATALOGO
-- =========================

CREATE TABLE producto_catalogo (
    id_producto_catalogo SERIAL PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    codigo_producto VARCHAR(30) NOT NULL,

    descripcion TEXT
);

-- =========================
-- PRODUCTO CLIENTE
-- =========================

CREATE TABLE producto_cliente (
    id_producto_cliente SERIAL PRIMARY KEY,

    fecha_compra DATE NOT NULL,
    fecha_fin_garantia DATE,

    numero_factura VARCHAR(50),

    id_cliente INT NOT NULL,
    id_producto_catalogo INT NOT NULL,

    FOREIGN KEY (id_cliente)
    REFERENCES cliente(id_cliente),

    FOREIGN KEY (id_producto_catalogo)
    REFERENCES producto_catalogo(id_producto_catalogo)
);

-- =========================
-- RECLAMO
-- =========================

CREATE TABLE reclamo (
    id_reclamo SERIAL PRIMARY KEY,

    descripcion_problema TEXT NOT NULL,

    fecha_recepcion DATE NOT NULL,
    fecha_revision DATE,
    fecha_resolucion DATE,

    estado VARCHAR(30) DEFAULT 'pendiente',

    accion_garantia VARCHAR(50),

    id_producto_cliente INT NOT NULL,
    id_tecnico INT,

    FOREIGN KEY (id_producto_cliente)
    REFERENCES producto_cliente(id_producto_cliente),

    FOREIGN KEY (id_tecnico)
    REFERENCES tecnico(id_tecnico)
);

-- =========================
-- REPUESTO
-- =========================

CREATE TABLE repuesto (
    id_repuesto SERIAL PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    codigo_repuesto VARCHAR(30) NOT NULL,

    stock INT DEFAULT 0
);

-- =========================
-- PRODUCTO - REPUESTO
-- =========================

CREATE TABLE producto_repuesto (
    id_producto_catalogo INT,
    id_repuesto INT,

    PRIMARY KEY (
        id_producto_catalogo,
        id_repuesto
    ),

    FOREIGN KEY (id_producto_catalogo)
    REFERENCES producto_catalogo(id_producto_catalogo),

    FOREIGN KEY (id_repuesto)
    REFERENCES repuesto(id_repuesto)
);

-- =========================
-- RECLAMO - REPUESTO
-- =========================

CREATE TABLE reclamo_repuesto (
    id_reclamo INT,
    id_repuesto INT,

    cantidad_usada INT NOT NULL,

    PRIMARY KEY (
        id_reclamo,
        id_repuesto
    ),

    FOREIGN KEY (id_reclamo)
    REFERENCES reclamo(id_reclamo),

    FOREIGN KEY (id_repuesto)
    REFERENCES repuesto(id_repuesto)
);
