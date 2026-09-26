<?php
namespace Amir\MapParcel\Domain;

final class Parcela
{
    public function __construct(
        private readonly string $nationalCadastralReference,
        public readonly ParcelniCislo $cislo,
        public readonly KatastralniUzemi $katastralniUzemi,
        public readonly Polygon $geometrie,
        public readonly float $vymeraM2
    )
    {}

    public function id(): string
    {
        return $this->nationalCadastralReference;
    }
}