<?php

namespace Amir\MapParcel\Infrastructure\Cache;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\Parcela;
use Amir\MapParcel\Infrastructure\Cuzk\CuzkParcelaParser;
use Amir\MapParcel\Infrastructure\Cuzk\CuzkWfsClient;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class CachedParcelaRepository
{
    private const TTL_SEKUND = 86400;

    public function __construct(
        private readonly CuzkWfsClient $client,
        private readonly CuzkParcelaParser $parser,
        private readonly AdapterInterface $cache,
    ) {
    }

    public function najdiVeVicerechObdelnicich(
        array $bboxy
    ): array
    {
        $klice = array_map(fn (BoundingBox $b) => $b->klic(), $bboxy);

        $polozky = [];
        foreach ($this->cache->getItems($klice) as $polozka) {
            $polozky[$polozka->getKey()] = $polozka;
        }

        $chybejici = array_filter($bboxy, fn (BoundingBox $b) => !$polozky[$b->klic()]->isHit());

        $vysledek = [];
        foreach ($polozky as $klic => $polozka) {
            if ($polozka->isHit()) {
                $vysledek[$klic] = $polozka->get();
            }
        }

        if ($chybejici !== []) {
            $xmlDavka = $this->client->stahniSurovaDataDavkove($chybejici);

            foreach ($chybejici as $bbox) {
                $parcely = $this->parser->parsuj($xmlDavka[$bbox->klic()]);

                $polozka = $polozky[$bbox->klic()];
                $polozka->set($parcely);
                $polozka->expiresAfter(self::TTL_SEKUND);
                $this->cache->save($polozka);

                $vysledek[$bbox->klic()] = $parcely;
            }
        }

        return $vysledek;
    }
}