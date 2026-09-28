<?php

namespace Amir\MapParcel\Domain;

interface DavkovyParcelaRepository
{
    public function najdiVeVicerechObdelnicich(array $bboxy): array;
}