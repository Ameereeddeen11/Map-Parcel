<?php

namespace Amir\MapParcel\Infrastructure\Cache;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\ParcelaRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedParcelaRepository implements ParcelaRepository
{
    private const TTL_SEKUND = 86400; // parcely se mění zřídka, den je bezpečný

    public function __construct(
        private readonly ParcelaRepository $inner,
        private readonly CacheInterface $cache,
    ) {
    }

    public function najdiVOhranicujicimObdelniku(
        BoundingBox $bbox
    ): array
    {
        return $this->cache->get(
            $this->klic($bbox),
            function (ItemInterface $item) use ($bbox): array {
                $item->expiresAfter(self::TTL_SEKUND);

                return $this->inner->najdiVOhranicujicimObdelniku($bbox);
            },
        );
    }

    private function klic(
        BoundingBox $bbox
    ): string
    {
        return sprintf('parcely_%.5f_%.5f_%.5f_%.5f', $bbox->jih, $bbox->zapad, $bbox->sever, $bbox->vychod);
    }
}