<?php

namespace Amir\MapParcel\Domain;

interface ParcelaRepository
{
    public function najdiVOhranicujicimObdelniku(): array;
}