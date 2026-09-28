<?php

namespace Amir\MapParcel\Infrastructure\Cuzk;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\Parcela;
use Amir\MapParcel\Domain\ParcelaRepository;

final class CuzkParcelaRepository implements ParcelaRepository
{
    public function __construct(
        private readonly CuzkWfsClient $client,
        private readonly CuzkParcelaParser $parser,
    ) {
    }

    public function najdiVOhranicujicimObdelniku(
        BoundingBox $bbox
    ): array
    {
        $xml = $this->client->stahniSurovaData($bbox);

        return $this->parser->parsuj($xml);
    }
}