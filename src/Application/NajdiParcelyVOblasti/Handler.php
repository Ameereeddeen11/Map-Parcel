<?php

namespace Amir\MapParcel\Application\NajdiParcelyVOblasti;

use Amir\MapParcel\Domain\BoundingBox;
use Amir\MapParcel\Domain\Parcela;
use Amir\MapParcel\Domain\ParcelaRepository;

final class Handler
{
    public function __construct(
        private readonly ParcelaRepository $repository,
    ) {
    }

    public function handle(
        BoundingBox $bbox
    ): array
    {
        return $this->repository->najdiVOhranicujicimObdelniku($bbox);
    }
}