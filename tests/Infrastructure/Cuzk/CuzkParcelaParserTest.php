<?php

namespace Amir\MapParcel\Tests\Infrastructure\Cuzk;

use Amir\MapParcel\Infrastructure\Cuzk\CuzkParcelaParser;
use PHPUnit\Framework\TestCase;

final class CuzkParcelaParserTest extends TestCase
{
    private CuzkParcelaParser $parser;

    protected function setUp(): void
    {
        $this->parser = new CuzkParcelaParser();
    }

    public function test_parsuje_spravny_pocet_parcel(): void
    {
        $xml = $this->nactiFixture('cuzk_sample_response.xml');

        $parcely = $this->parser->parsuj($xml);

        $this->assertCount(3, $parcely);
    }

    public function test_parsuje_zakladni_udaje_prvni_parcely(): void
    {
        $xml = $this->nactiFixture('cuzk_sample_response.xml');

        $parcely = $this->parser->parsuj($xml);
        $prvni = $parcely[0];

        $this->assertSame('845/6', (string) $prvni->cislo);
        $this->assertSame('Jičín', $prvni->katastralniUzemi->nazev);
        $this->assertSame(395.0, $prvni->vymeraM2);
    }

    public function test_parsuje_geometrii_jako_uzavreny_polygon(): void
    {
        $xml = $this->nactiFixture('cuzk_sample_response.xml');

        $parcely = $this->parser->parsuj($xml);
        $body = $parcely[0]->geometrie->body();

        $this->assertGreaterThanOrEqual(4, count($body));

        $prvniBod = $body[0];
        $posledniBod = $body[count($body) - 1];
        $this->assertEqualsWithDelta($prvniBod->lat, $posledniBod->lat, 0.0001);
        $this->assertEqualsWithDelta($prvniBod->lon, $posledniBod->lon, 0.0001);
    }

    private function nactiFixture(string $nazevSouboru): string
    {
        return file_get_contents(__DIR__ . '/../../Fixtures/' . $nazevSouboru);
    }
}