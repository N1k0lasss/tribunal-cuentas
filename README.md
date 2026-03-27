# Sistema de Gestión de Licencias y Permisos

## Configuración

```bash
# Instalar dependencias
composer install

# Copiar .env.example a .env y generar clave de aplicación
cp .env.example .env
php artisan key:generate

# (Modificar .env y configurar base de datos con credenciales validas)

# Recrear base de datos (eliminar tablas y volver a crear)
php artisan migrate:fresh

# Ejecutar seeders (datos de prueba)
php artisan db:seed

# Ejecutar servidor local
php artisan serve
```
# Usuario	Email	Contraseña
```
Admin	admin@test.com (mailto:admin@test.com)	password
Agente	agente@test.com (mailto:agente@test.com)	password
Jefe	jefe@test.com (mailto:jefe@test.com)	password
Secretario	secretario@test.com (mailto:secretario@test.com)	password
Presidente	presidente@test.com (mailto:presidente@test.com)	password

