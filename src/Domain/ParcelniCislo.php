<?php

namespace Amir\MapParcel\Domain;

final class ParcelniCislo
{
    private const VZOR = '/^(st\.\s*)?\d+(\/\d+)?$/u';

    public function __construct(
        public readonly string $hodnota,
    ) {
        if (!preg_match(self::VZOR, $hodnota)) {
            throw new \InvalidArgumentException("Neplatný formát parcelního čísla: {$hodnota}");
        }
    }

    public function jeStavebni(): bool
    {
        return str_starts_with($this->hodnota, 'st.');
    }

    public function __toString(): string
    {
        return $this->hodnota;
    }
}