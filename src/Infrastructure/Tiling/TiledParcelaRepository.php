<?php

namespace Amir\MapParcel\Infrastructure\Tiling;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\ParcelaRepository;
use Amir\MapParcel\Domain\PrilisVelkaOblastException;
use Amir\MapParcel\Domain\TileGrid;
use Amir\MapParcel\Infrastructure\Cache\CachedParcelaRepository;

final class TiledParcelaRepository implements ParcelaRepository
{
    private const MAX_DLAZDIC = 16;

    public function __construct(
        private readonly CachedParcelaRepository $cachedRepository,
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

        $podleDlazdic = $this->cachedRepository->najdiVeVicerechObdelnicich($dlazdice);

        $vysledek = [];
        foreach ($podleDlazdic as $parcely) {
            foreach ($parcely as $parcela) {
                $vysledek[$parcela->id()] = $parcela;
            }
        }

        return array_values($vysledek);
    }
}