<?php

namespace Amir\MapParcel\Tests\Infrastructure\Cache;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\ParcelaRepository;
use Amir\MapParcel\Infrastructure\Cache\CachedParcelaRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedParcelaRepositoryTest extends TestCase
{
    public function test_druhy_dotaz_na_stejny_bbox_nevola_puvodni_repository(): void
    {
        $inner = new class implements ParcelaRepository {
            public int $pocetVolani = 0;

            public function najdiVOhranicujicimObdelniku(BoundingBox $bbox): array
            {
                $this->pocetVolani++;

                return [];
            }
        };
        $repository = new CachedParcelaRepository($inner, new ArrayAdapter());
        $bbox = new BoundingBox(50.43, 15.34, 50.44, 15.36);

        $repository->najdiVOhranicujicimObdelniku($bbox);
        $repository->najdiVOhranicujicimObdelniku($bbox);

        $this->assertSame(1, $inner->pocetVolani);
    }
}