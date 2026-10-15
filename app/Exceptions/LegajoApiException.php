<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Error al consultar la API del Sistema de Legajos
 * (servicio caído, token inválido, respuesta inesperada, etc.).
 *
 * `getMessage()` es el texto apto para mostrar al usuario (sin datos internos
 * como la URL o el DNI); el detalle técnico va en `detalle()`, destinado a los
 * logs y al comando `legajos:sync`.
 */
class LegajoApiException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly ?string $detalle = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Detalle técnico para diagnóstico (no se muestra en la interfaz).
     */
    public function detalle(): ?string
    {
        return $this->detalle;
    }
}
