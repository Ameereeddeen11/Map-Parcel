<?php

namespace Amir\MapParcel\Infrastructure\Tiling;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\ParcelaRepository;
use Amir\MapParcel\Domain\PrilisVelkaOblastException;
use Amir\MapParcel\Domain\TileGrid;

final class TiledParcelaRepository implements ParcelaRepository
{
    private const MAX_DLAZDIC = 100;

    public function __construct(
        private readonly ParcelaRepository $inner,
        private readonly TileGrid $grid,
    ) {
    }

    public function najdiVOhranicujicimObdelniku(
        BoundingBox $bbox
    ): array
    {
        $dlazdice = $this->grid->dlazdicePokryvajici($bbox);

        if (count($dlazdice) > self::MAX_DLAZDIC) {
            throw new PrilisVelkaOblastException(
                sprintf('Oblast pokrývá %d dlaždic, maximum je %d.', count($dlazdice), self::MAX_DLAZDIC),
            );
        }

        $vysledek = [];
        foreach ($dlazdice as $dlazdice1) {
            foreach ($this->inner->najdiVOhranicujicimObdelniku($dlazdice1) as $parcela) {
                $vysledek[$parcela->id()] = $parcela;
            }
        }

        return array_values($vysledek);
    }
}