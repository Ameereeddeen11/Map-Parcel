<?php

namespace Amir\MapParcel\Presentation\Http\GeoJson;

use Amir\MapParcel\Domain\Parcela;

final class ParcelaGeoJsonTransformer
{
    public function transformuj(
        array $parcely
    ): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => array_map($this->jedenPrvek(...), $parcely),
        ];
    }

    private function jedenPrvek(
        Parcela $parcela
    ): array
    {
        $souradnice = array_map(
            fn ($bod) => [$bod->lon, $bod->lat],
            $parcela->geometrie->body(),
        );

        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [$souradnice],
            ],
            'properties' => [
                'id' => $parcela->id(),
                'cislo' => (string) $parcela->cislo,
                'katastralniUzemi' => $parcela->katastralniUzemi->nazev,
                'vymeraM2' => $parcela->vymeraM2,
            ],
        ];
    }
}