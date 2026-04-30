# PUBLIMARKET — Plataforma Web
**Agencia de Marketing Digital | Bogotá, Colombia · Desde 2024**

---

## 📁 Estructura de archivos

```
publimarket/
├── config/
│   ├── app.php          ← Configuración global, helpers, sesión
│   └── database.php     ← Conexión PDO (singleton)
├── includes/
│   ├── header.php       ← Navbar reutilizable
│   └── footer.php       ← Footer + cierre HTML
├── assets/
│   ├── css/main.css     ← Design system completo (Apple-esque)
│   └── js/main.js       ← Calendario, animaciones, carrito, WhatsApp
├── auth/
│   ├── login.php        ← Inicio de sesión
│   ├── register.php     ← Registro de clientes
│   └── logout.php       ← Cierre de sesión seguro
├── admin/
│   ├── dashboard.php    ← Panel principal con stats y citas
│   ├── appointments.php ← Gestión de citas (confirmar/cancelar)
│   ├── clients.php      ← Base de clientes + toggle membresía
│   ├── products.php     ← CRUD de productos
│   ├── plans.php        ← (extendible) Gestión de planes
│   └── api/
│       └── membership.php ← API: toggle membresía vía AJAX
├── api/
│   └── appointments.php ← API: slots ocupados + reservar cita
├── sql/
│   └── schema.sql       ← Esquema completo de BD + seed data
└── index.php            ← Landing page principal
```

---

## ⚙️ Instalación

### 1. Requisitos
- PHP 8.1+
- MySQL 8.0+ (o MariaDB 10.6+)
- Servidor web: Apache + mod_rewrite o Nginx

### 2. Base de datos

```bash
mysql -u root -p < sql/schema.sql
```

Esto crea la base de datos `publimarket`, todas las tablas y datos de ejemplo.

### 3. Configuración

Edita `config/app.php` o crea variables de entorno:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=publimarket
DB_USER=root
DB_PASS=tu_password
APP_URL=http://localhost/publimarket
APP_ENV=development
WA_PHONE=573001234567   # Sin + ni espacios
```

### 4. Apache (`.htaccess` en raíz)

```apache
RewriteEngine On
RewriteBase /publimarket/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
```

---

## 👤 Credenciales de prueba

| Rol           | Email                  | Contraseña   |
|---------------|------------------------|--------------|
| Administrador | admin@publimarket.co   | Admin2024!   |

> ⚠️ Cambia la contraseña del admin en producción:
> ```php
> password_hash('TuNuevaContraseña', PASSWORD_BCRYPT, ['cost'=>12])
> ```
> Actualiza en la tabla `users`.

---

## 🚀 Flujo de negocio

```
Visitante → Landing → Elige plan → Calendario Interactivo
    → Selecciona fecha/hora → Llena sus datos
    → POST /api/appointments.php (guarda en BD)
    → Redirige a WhatsApp con mensaje prellenado
    → Admin ve la cita en dashboard
    → Admin confirma reunión → Marca como "done"
    → Admin activa membresía del cliente (toggle)
```

---

## 🎨 Design System

| Token         | Valor             |
|---------------|-------------------|
| Fondo         | `#FFFFFF`         |
| Fondo suave   | `#F5F5F7`         |
| Acento rojo   | `#E30000`         |
| Texto         | `#1D1D1F`         |
| Border radius | 20px – 30px       |
| Fuente        | Outfit + DM Serif |

---

## 📱 Características

- ✅ 100% responsive (mobile-first)
- ✅ Navbar con efecto glassmorphism al scroll
- ✅ Animaciones fade-in + slide-up con IntersectionObserver
- ✅ Calendario interactivo vanilla JS
- ✅ Redirección dinámica a WhatsApp
- ✅ Toggle de membresía en tiempo real (AJAX)
- ✅ Carrito de compras simulado (sessionStorage)
- ✅ CSRF protection en todos los formularios
- ✅ Contraseñas hasheadas con bcrypt
- ✅ Sesiones seguras (httponly, strict mode)
- ✅ Prepared statements en todas las queries
