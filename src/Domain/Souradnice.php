<?php
namespace Amir\MapParcel\Domain;

final class Souradnice
{
    public function __construct(
        public readonly float $lat,
        public readonly float $lon,
    )
    {}
}