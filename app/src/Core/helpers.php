<?php
declare(strict_types=1);

/** Escapa una salida HTML (prevención de XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
