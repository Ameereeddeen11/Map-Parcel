<?php

namespace Amir\MapParcel\Tests\Domain;

use Amir\MapParcel\Domain\ParcelniCislo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ParcelniCisloTest extends TestCase
{
    #[DataProvider('platnaCisla')]
    public function test_prijme_platne_formaty(string $label, bool $stavebni): void
    {
        $cislo = new ParcelniCislo($label);

        $this->assertSame($label, (string) $cislo);
        $this->assertSame($stavebni, $cislo->jeStavebni());
    }

    public static function platnaCisla(): array
    {
        return [
            'pozemkova s poddelenim' => ['845/6', false],
            'pozemkova bez poddeleni' => ['1203', false],
            'stavebni' => ['st. 4559', true],
            'stavebni s poddelenim' => ['st. 12/1', true],
        ];
    }

    public function test_odmitne_nesmysl(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ParcelniCislo('abc');
    }
}