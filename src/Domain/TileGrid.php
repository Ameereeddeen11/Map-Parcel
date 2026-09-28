<?php

namespace Amir\MapParcel\Domain;

final class TileGrid
{
    public function __construct(
        private readonly float $velikostStupne = 0.01,
    ) {
        if ($velikostStupne <= 0) {
            throw new \InvalidArgumentException('Velikost dlaždice musí být kladná.');
        }
    }

    public function dlazdicePokryvajici(
        BoundingBox $bbox
    ): array
    {
        $yOd = (int) floor($bbox->jih / $this->velikostStupne);
        $yDo = (int) floor($bbox->sever / $this->velikostStupne);
        $xOd = (int) floor($bbox->zapad / $this->velikostStupne);
        $xDo = (int) floor($bbox->vychod / $this->velikostStupne);

        $dlazdice = [];
        for ($y = $yOd; $y <= $yDo; $y++) {
            for ($x = $xOd; $x <= $xDo; $x++) {
                $dlazdice[] = new BoundingBox(
                    jih: round($y * $this->velikostStupne, 6),
                    zapad: round($x * $this->velikostStupne, 6),
                    sever: round(($y + 1) * $this->velikostStupne, 6),
                    vychod: round(($x + 1) * $this->velikostStupne, 6),
                );
            }
        }

        return $dlazdice;
    }
}