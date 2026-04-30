-- ============================================================
--  PUBLIMARKET — Database Schema
--  Bogotá, Colombia | Activa desde 2024
-- ============================================================

CREATE DATABASE IF NOT EXISTS publimarket
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE publimarket;

-- ─── USUARIOS ────────────────────────────────────────────────
CREATE TABLE users (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  name          VARCHAR(120)     NOT NULL,
  email         VARCHAR(180)     NOT NULL UNIQUE,
  password_hash VARCHAR(255)     NOT NULL,
  phone         VARCHAR(20)      DEFAULT NULL,
  role          ENUM('client','admin') NOT NULL DEFAULT 'client',
  membership    ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
  avatar_url    VARCHAR(500)     DEFAULT NULL,
  created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                   ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_email (email),
  KEY idx_role  (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── PLANES / MEMBRESÍAS ──────────────────────────────────────
CREATE TABLE membership_plans (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100)     NOT NULL,
  slug          VARCHAR(100)     NOT NULL UNIQUE,
  description   TEXT             DEFAULT NULL,
  price_cop     DECIMAL(12,2)    NOT NULL,
  features      JSON             DEFAULT NULL,   -- array de strings
  is_active     TINYINT(1)       NOT NULL DEFAULT 1,
  sort_order    INT              NOT NULL DEFAULT 0,
  created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── CITAS ───────────────────────────────────────────────────
CREATE TABLE appointments (
  id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  user_id         INT UNSIGNED   DEFAULT NULL,   -- NULL si es anónimo
  guest_name      VARCHAR(120)   DEFAULT NULL,
  guest_email     VARCHAR(180)   DEFAULT NULL,
  guest_phone     VARCHAR(20)    DEFAULT NULL,
  plan_id         INT UNSIGNED   NOT NULL,
  appointment_at  DATETIME       NOT NULL,
  notes           TEXT           DEFAULT NULL,
  status          ENUM('pending','confirmed','cancelled','done')
                                 NOT NULL DEFAULT 'pending',
  whatsapp_sent   TINYINT(1)     NOT NULL DEFAULT 0,
  created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                   ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_date    (appointment_at),
  KEY idx_status  (status),
  CONSTRAINT fk_appt_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_appt_plan FOREIGN KEY (plan_id)
    REFERENCES membership_plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── PRODUCTOS ────────────────────────────────────────────────
CREATE TABLE products (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  name          VARCHAR(200)     NOT NULL,
  slug          VARCHAR(200)     NOT NULL UNIQUE,
  description   TEXT             DEFAULT NULL,
  price_cop     DECIMAL(12,2)    NOT NULL,
  stock         INT              NOT NULL DEFAULT 0,
  image_url     VARCHAR(500)     DEFAULT NULL,  -- Unsplash URL o local
  category      VARCHAR(80)      DEFAULT NULL,
  is_active     TINYINT(1)       NOT NULL DEFAULT 1,
  created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                   ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_category (category),
  KEY idx_active   (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── ÓRDENES (flujo de pago simulado) ────────────────────────
CREATE TABLE orders (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED     DEFAULT NULL,
  guest_name    VARCHAR(120)     DEFAULT NULL,
  guest_email   VARCHAR(180)     DEFAULT NULL,
  total_cop     DECIMAL(14,2)    NOT NULL,
  status        ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_order_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
  id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  order_id    INT UNSIGNED   NOT NULL,
  product_id  INT UNSIGNED   NOT NULL,
  qty         INT            NOT NULL DEFAULT 1,
  unit_price  DECIMAL(12,2)  NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders(id),
  CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── SEED DATA ────────────────────────────────────────────────

-- Admin por defecto (password: Admin2024!)
INSERT INTO users (name, email, password_hash, role, membership) VALUES
('Administrador', 'admin@publimarket.co',
 '$2y$12$Q5rQ5rQ5rQ5rQ5rQ5rQ5OeBz9o8Z8Nz7H3K2JdY4mXvP1LwS0uT6',
 'admin', 'active');

-- Planes de membresía
INSERT INTO membership_plans (name, slug, description, price_cop, features, sort_order) VALUES
('Básico', 'basico',
 'Ideal para emprendedores que están comenzando su camino en marketing digital.',
 290000,
 '["1 red social gestionada","4 publicaciones mensuales","Diseño de contenido","Reporte mensual"]',
 1),
('Profesional', 'profesional',
 'Para marcas que buscan crecer con estrategia y creatividad constante.',
 590000,
 '["3 redes sociales gestionadas","12 publicaciones mensuales","Diseño + copywriting","Pauta básica incluida","Reunión quincenal"]',
 2),
('Premium', 'premium',
 'Solución integral para empresas que exigen resultados medibles y escalables.',
 990000,
 '["5 redes sociales gestionadas","30 publicaciones mensuales","Producción audiovisual","Pauta avanzada","Asesoría semanal","Informe de métricas detallado"]',
 3);

-- Productos de muestra
INSERT INTO products (name, slug, description, price_cop, stock, image_url, category) VALUES
('Kit de Branding Corporativo', 'kit-branding-corporativo',
 'Diseño completo de identidad visual: logo, paleta, tipografía y manual de marca.',
 450000, 20,
 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=800&q=80',
 'Diseño'),
('Fotografía de Producto (10 fotos)', 'fotografia-producto',
 'Sesión profesional en estudio con edición y entrega en alta resolución.',
 320000, 15,
 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=800&q=80',
 'Fotografía'),
('Video Corporativo 60s', 'video-corporativo-60s',
 'Producción de video institucional de 60 segundos para redes y web.',
 850000, 8,
 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=800&q=80',
 'Video'),
('Pack Social Media 30 días', 'pack-social-media-30',
 '30 artes listos para publicar, adaptados a tu marca y audiencia.',
 280000, 50,
 'https://images.unsplash.com/photo-1611926653458-09294b3142bf?w=800&q=80',
 'Marketing'),
('Campaña Google Ads', 'campana-google-ads',
 'Configuración y optimización de campaña en Google Ads por 30 días.',
 520000, 10,
 'https://images.unsplash.com/photo-1432888622747-4eb9a8efbc07?w=800&q=80',
 'Pauta'),
('Diseño de Landing Page', 'landing-page',
 'Landing page optimizada para conversión, entregada en 5 días hábiles.',
 680000, 12,
 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=800&q=80',
 'Web');
