<?php

namespace Amir\MapParcel\Presentation\Http;

use Amir\MapParcel\Application\NajdiParcelyVOblasti\Handler;
use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\PrilisVelkaOblastException;
use Amir\MapParcel\Presentation\Http\GeoJson\ParcelaGeoJsonTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ParcelyController
{
    public function __construct(
        private readonly Handler $handler,
        private readonly ParcelaGeoJsonTransformer $transformer,
    ) {
    }

    #[Route('/api/parcely', name: 'parcely', methods: ['GET'])]
    public function __invoke(
        Request $request
    ): JsonResponse
    {
        $bbox = new BoundingBox(
            jih: (float) $request->query->get('jih'),
            zapad: (float) $request->query->get('zapad'),
            sever: (float) $request->query->get('sever'),
            vychod: (float) $request->query->get('vychod'),
        );

        try {
            $parcely = $this->handler->handle($bbox);
        } catch (PrilisVelkaOblastException $e) {
            return new JsonResponse(['chyba' => $e->getMessage()], 422);
        }

        return new JsonResponse($this->transformer->transformuj($parcely));
    }
}