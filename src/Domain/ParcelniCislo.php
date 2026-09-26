<?php
namespace Amir\MapParcel\Domain;

final class ParcelniCislo
{
    public function __construct(
        public readonly string $hodnota,
    )
    {
        if (!preg_match('/^\d+(\/\d+)?$/', $this->hodnota)) {
            throw new \InvalidArgumentException("Neplatný formát parcelního čísla: {$this->hodnota}");
        }
    }

    public function __toString(): string
    {
        return $this->hodnota;
    }
}