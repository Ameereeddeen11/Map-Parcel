<?php

namespace Amir\MapParcel\Tests\Infrastructure\Tiling;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\KatastralniUzemi;
use Amir\MapParcel\Domain\Parcela;
use Amir\MapParcel\Domain\ParcelniCislo;
use Amir\MapParcel\Domain\Polygon;
use Amir\MapParcel\Domain\PrilisVelkaOblastException;
use Amir\MapParcel\Domain\Souradnice;
use Amir\MapParcel\Domain\TileGrid;
use Amir\MapParcel\Infrastructure\Cache\CachedParcelaRepository;
use Amir\MapParcel\Infrastructure\Cuzk\CuzkParcelaParser;
use Amir\MapParcel\Infrastructure\Cuzk\CuzkWfsClient;
use Amir\MapParcel\Infrastructure\Tiling\TiledParcelaRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class TiledParcelaRepositoryTest extends TestCase
{
    public function test_deduplikuje_parcelu_na_hranici_dlazdic(): void
    {
        $parcela = $this->fakeParcela();

        $client = $this->createStub(CuzkWfsClient::class);
        $client->method('stahniSurovaDataDavkove')->willReturnCallback(
            fn (array $bboxy) => array_fill_keys(array_map(fn ($b) => $b->klic(), $bboxy), '<xml/>'),
        );

        $parser = $this->createStub(CuzkParcelaParser::class);
        $parser->method('parsuj')->willReturn([$parcela]);

        $cachedRepository = new CachedParcelaRepository($client, $parser, new ArrayAdapter());
        $repository = new TiledParcelaRepository($cachedRepository, new TileGrid(0.01));

        $vysledek = $repository->najdiVOhranicujicimObdelniku(new BoundingBox(50.430, 15.340, 50.445, 15.355));

        $this->assertCount(1, $vysledek);
    }

    public function test_prilis_velka_oblast_vyhodi_vyjimku(): void
    {
        $cachedRepository = $this->createStub(CachedParcelaRepository::class);
        $repository = new TiledParcelaRepository($cachedRepository, new TileGrid(0.01));

        $this->expectException(PrilisVelkaOblastException::class);

        $repository->najdiVOhranicujicimObdelniku(new BoundingBox(50.0, 15.0, 51.0, 16.0));
    }

    private function fakeParcela(): Parcela
    {
        return new Parcela(
            nationalCadastralReference: '659541-845/6',
            cislo: new ParcelniCislo('845/6'),
            katastralniUzemi: new KatastralniUzemi('659541', 'Jičín'),
            vymeraM2: 395.0,
            geometrie: new Polygon([
                new Souradnice(50.43, 15.34),
                new Souradnice(50.43, 15.35),
                new Souradnice(50.44, 15.35),
                new Souradnice(50.43, 15.34),
            ]),
        );
    }
}