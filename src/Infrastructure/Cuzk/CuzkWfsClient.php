<?php

namespace Amir\MapParcel\Infrastructure\Cuzk;

use Amir\MapParcel\Domain\BoundingBox;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CuzkWfsClient
{
    private const ENDPOINT = 'https://services.cuzk.cz/wfs/inspire-cp-wfs.asp';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function stahniSurovaData(BoundingBox $bbox): string
    {
        return $this->pozadavek($bbox)->getContent();
    }

    public function stahniSurovaDataDavkove(
        array $bboxy
    ): array
    {
        $responses = [];
        foreach ($bboxy as $bbox) {
            $responses[$bbox->klic()] = $this->pozadavek($bbox);
        }

        $vysledek = [];
        foreach ($responses as $klic => $response) {
            $vysledek[$klic] = $response->getContent();
        }

        return $vysledek;
    }

    private function pozadavek(
        BoundingBox $bbox
    ): ResponseInterface
    {
        return $this->httpClient->request('GET', self::ENDPOINT, [
            'query' => [
                'service' => 'WFS',
                'version' => '2.0.0',
                'request' => 'GetFeature',
                'typeNames' => 'cp:CadastralParcel',
                'BBOX' => sprintf(
                    '%f,%f,%f,%f,http://www.opengis.net/def/crs/EPSG/0/4326',
                    $bbox->jih, $bbox->zapad, $bbox->sever, $bbox->vychod,
                ),
                'srsName' => 'http://www.opengis.net/def/crs/EPSG/0/4326',
            ],
        ]);
    }
}