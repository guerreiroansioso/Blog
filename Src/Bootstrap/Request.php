<?php

declare(strict_types=1);

function normalizeRequest(array|string $value): array|string {
    if (is_array($value)) {
        return array_map('strtolower', $value);
    }

    return strtolower($value);
}
