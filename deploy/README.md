# Despliegue del worker y del planificador

El envío de licencias a Legajos depende de **dos procesos** además de la aplicación web:

1. **Worker de cola**: ejecuta `EnviarLicenciaALegajos` (cola `legajos`).
2. **Planificador**: ejecuta `php artisan schedule:run` cada minuto; cada 5 minutos el comando `app:enviar-licencias-legajos` reencola pendientes y recupera envíos interrumpidos.

Sin worker, los envíos quedan en la tabla `jobs` y nunca salen. Sin planificador, los reintentos programados no se reencolan.

> Los comandos usan `--tries=1` a propósito: el job ya maneja sus propios reintentos con backoff. No agregar `--tries` mayores para no duplicar la política.

---

## Linux (recomendado)

### Worker con systemd

1. Copiar `deploy/systemd/licencias-worker.service` a `/etc/systemd/system/`.
2. Ajustar `User`, `WorkingDirectory` y la ruta de `php` si hace falta.
3. Activar:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now licencias-worker
sudo systemctl status licencias-worker
```

### Planificador con systemd timer (o cron)

Opción A, timer:

```bash
sudo cp deploy/systemd/licencias-scheduler.service /etc/systemd/system/
sudo cp deploy/systemd/licencias-scheduler.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now licencias-scheduler.timer
```

Opción B, cron del usuario de la aplicación:

```
* * * * * cd /var/www/licencias && php artisan schedule:run >> /dev/null 2>&1
```

### Permisos

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

---

## Windows

Requisitos: PHP en una ruta estable (por ejemplo `C:\php\php.exe`), [NSSM](https://nssm.cc/) y permisos de administrador.

### Instalación automática

```powershell
powershell -ExecutionPolicy Bypass -File deploy\windows\instalar-servicios.ps1 `
  -AppDir "C:\inetpub\licencias" `
  -Php "C:\php\php.exe" `
  -Nssm "C:\nssm\nssm.exe"
```

El script:

- registra el worker como servicio de Windows con reinicio automático;
- crea una tarea programada cada minuto para `schedule:run`.

### Verificación

```powershell
Get-Service LicenciasWorker
schtasks /query /tn "LicenciasScheduler"
```

Los logs del worker quedan en `storage\logs\worker.log` y `storage\logs\worker-error.log`.

---

## Checklist de cada despliegue

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan schedule:list
```

- `queue:restart` es obligatorio: los workers son procesos de larga vida y siguen con el código viejo en memoria.
- Verificar que `php artisan schedule:list` muestre `app:enviar-licencias-legajos` cada 5 minutos.
- Revisar `failed_jobs` y el panel «Configuración → Envíos de licencias».

---

## Fallback si el servidor no permite servicios de larga vida

Se puede drenar la cola desde el planificador cada minuto. Es menos eficiente y no reemplaza al worker supervisado, pero evita que los envíos queden trabados:

```php
Schedule::command('queue:work --queue=legajos,default --stop-when-empty --tries=1 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();
```

Usarlo solo como puente hasta poder instalar el worker como servicio.
