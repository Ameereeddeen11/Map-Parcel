<?php

namespace Amir\MapParcel\Tests\Domain;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\TileGrid;
use PHPUnit\Framework\TestCase;

final class TileGridTest extends TestCase
{
    public function test_maly_bbox_uvnitr_jedne_dlazdice_vrati_jednu_dlazdici(): void
    {
        $grid = new TileGrid(0.01);

        $dlazdice = $grid->dlazdicePokryvajici(new BoundingBox(50.431, 15.361, 50.434, 15.364));

        $this->assertCount(1, $dlazdice);
    }

    public function test_bbox_pres_hranici_dlazdic_vrati_vic_dlazdic(): void
    {
        $grid = new TileGrid(0.01);

        $dlazdice = $grid->dlazdicePokryvajici(new BoundingBox(50.435, 15.361, 50.445, 15.364));

        $this->assertCount(2, $dlazdice);
    }

    public function test_dlazdice_maji_stabilni_hranice_pro_cache_klic(): void
    {
        $grid = new TileGrid(0.01);

        $a = $grid->dlazdicePokryvajici(new BoundingBox(50.431, 15.361, 50.434, 15.364));
        $b = $grid->dlazdicePokryvajici(new BoundingBox(50.437, 15.366, 50.439, 15.369));

        $this->assertEquals($a[0], $b[0]);
    }
}