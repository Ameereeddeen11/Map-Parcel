<?php
namespace Amir\MapParcel\Domain;

final class Souradnice
{
    public function __construct(
        public readonly string $lat,
        public readonly string $lon,
    )
    {}
}