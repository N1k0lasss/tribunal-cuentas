<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * El envío falló por una causa transitoria (red, timeout o 5xx). El job de
 * cola lo reintenta con su propia espera; el outbox deja el estado pendiente
 * para que el planificador pueda recuperarlo si el job se pierde.
 */
class LegajoEnvioReintentableException extends RuntimeException {}
