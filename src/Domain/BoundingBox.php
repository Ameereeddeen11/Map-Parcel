<?php

namespace Amir\MapParcel\Domain;

final class BoundingBox
{
    public function __construct(
        public readonly float $jih,
        public readonly float $zapad,
        public readonly float $sever,
        public readonly float $vychod,
    ) {
        if ($jih >= $sever || $zapad >= $vychod) {
            throw new \InvalidArgumentException('Neplatný bounding box.');
        }
    }
}