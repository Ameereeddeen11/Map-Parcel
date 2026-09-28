<?php

namespace Amir\MapParcel\Tests\Infrastructure\Cache;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\KatastralniUzemi;
use Amir\MapParcel\Domain\Parcela;
use Amir\MapParcel\Domain\ParcelniCislo;
use Amir\MapParcel\Domain\Polygon;
use Amir\MapParcel\Domain\Souradnice;
use Amir\MapParcel\Infrastructure\Cache\CachedParcelaRepository;
use Amir\MapParcel\Infrastructure\Cuzk\CuzkParcelaParser;
use Amir\MapParcel\Infrastructure\Cuzk\CuzkWfsClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedParcelaRepositoryTest extends TestCase
{
    public function test_stahne_jen_dlazdice_ktere_nejsou_v_cache(): void
    {
        $bboxA = new BoundingBox(50.43, 15.34, 50.44, 15.35);
        $bboxB = new BoundingBox(50.44, 15.34, 50.45, 15.35);
        $parcela = $this->fakeParcela();

        $client = $this->createMock(CuzkWfsClient::class);
        $client->expects($this->once())
            ->method('stahniSurovaDataDavkove')
            ->with([$bboxA])
            ->willReturn([$bboxA->klic() => '<xml/>']);

        $parser = $this->createStub(CuzkParcelaParser::class);
        $parser->method('parsuj')->willReturn([$parcela]);

        $cache = new ArrayAdapter();
        $polozka = $cache->getItem($bboxB->klic());
        $polozka->set([$parcela]);
        $cache->save($polozka);

        $repository = new CachedParcelaRepository($client, $parser, $cache);

        $vysledek = $repository->najdiVeVicerechObdelnicich([$bboxA, $bboxB]);

        $this->assertCount(2, $vysledek);
        $this->assertEquals([$parcela], $vysledek[$bboxA->klic()]);
        $this->assertEquals([$parcela], $vysledek[$bboxB->klic()]);
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