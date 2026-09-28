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

    public function klic(): string
    {
        return sprintf(
            'parcely_%.5f_%.5f_%.5f_%.5f',
            $this->jih,
            $this->zapad,
            $this->sever,
            $this->vychod
        );
    }
}