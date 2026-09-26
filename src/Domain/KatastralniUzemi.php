<?php
namespace Amir\MapParcel\Domain;

final class KatastralniUzemi
{
    public function __construct(
        public readonly string $kod,
        public readonly string $nazev
    )
    {}
}