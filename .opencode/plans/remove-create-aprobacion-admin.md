# Plan: Remover función "Crear nueva aprobación" para administradores

## Contexto
- Recurso Filament: `app/Filament/Admin/Resources/Aprobacions/AprobacionResource.php`
- Página listado: `app/Filament/Admin/Resources/Aprobacions/Pages/ListAprobacions.php` - actualmente muestra `CreateAction` en header
- Política: `app/Policies/AprobacionPolicy.php` - ya tiene `create()` retornando `false`, pero `before()` da acceso total a administradores
- También hay ruta de create registrada en el recurso

## Objetivo
Quitar la posibilidad de crear nuevas aprobaciones desde la vista del administrador.

## Cambios propuestos

### 1. `app/Policies/AprobacionPolicy.php`
Modificar el método `before()` para denegar explícitamente la habilidad `create` incluso para administradores:
```php
public function before(User $user, string $ability): bool|null
{
    if ($user->hasRole('administrador')) {
        if (in_array($ability, ['create'])) {
            return false;
        }
        return true;
    }

    return null;
}
```

### 2. `app/Filament/Admin/Resources/Aprobacions/Pages/ListAprobacions.php`
Remover la acción de crear del header:
```php
protected function getHeaderActions(): array
{
    return [];
}
```

### 3. Opcional: `app/Filament/Admin/Resources/Aprobacions/AprobacionResource.php`
También se puede remover la ruta `create` del array `getPages()` para evitar acceso directo por URL. Esto es más estricto pero completo.

Recomendado: remover `create` para asegurar que no se pueda crear ni por política ni por ruta.

## Verificación
- Como administrador, no debe aparecer botón "Nueva aprobación" / "Create"
- Acceso directo a `/admin/aprobacions/create` debe redirigir o dar 403
- Otros roles no deben poder crear (ya estaba denegado)

## Notas
Las aprobaciones se generan automáticamente por el flujo de workflow (`WorkflowSolicitudesService`), por eso no debe existir creación manual.
