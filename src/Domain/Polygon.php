<?php
namespace Amir\MapParcel\Domain;

final class Polygon
{
    private readonly array $body;

    public function __construct(
        array $body,
    )
    {
        if (count($body) < 4) {
            throw new \InvalidArgumentException("Polygon musí mít alespoň 4 body (uzavřený tvar).");
        }
        $this->body = $body;
    }

    public function body(): array
    {
        return $this->body;
    }
}