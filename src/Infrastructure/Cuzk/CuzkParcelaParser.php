<?php

namespace Amir\MapParcel\Infrastructure\Cuzk;

use Amir\MapParcel\Domain\KatastralniUzemi;
use Amir\MapParcel\Domain\Parcela;
use Amir\MapParcel\Domain\ParcelniCislo;
use Amir\MapParcel\Domain\Polygon;
use Amir\MapParcel\Domain\Souradnice;

class CuzkParcelaParser
{
    public function parsuj(
        string $xml
    ): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $xpath = new \DOMXPath($dom);
        $this->registrujNamespacyZDokumentu($xpath, $dom->documentElement);

        $uzly = $xpath->query('//cp:CadastralParcel');

        $parcely = [];
        foreach ($uzly as $uzel) {
            $parcely[] = $this->parsujJedenUzel($xpath, $uzel);
        }

        return $parcely;
    }

    private function registrujNamespacyZDokumentu(
        \DOMXPath $xpath,
        \DOMElement $root
    ): void
    {
        foreach ($xpath->query('namespace::*', $root) as $uzel) {
            $prefix = $uzel->localName;
            if ($prefix === '' || $prefix === 'xml') {
                continue;
            }
            $xpath->registerNamespace($prefix, $uzel->nodeValue);
        }
    }

    private function parsujJedenUzel(
        \DOMXPath $xpath,
        \DOMElement $uzel
    ): Parcela
    {
        $label = $this->text($xpath, 'cp:label', $uzel);
        $nationalRef = $this->text($xpath, 'cp:nationalCadastralReference', $uzel);
        $vymera = (float) $this->text($xpath, 'cp:areaValue', $uzel);

        $hrefZoning = $this->atribut($xpath, 'cp:zoning', $uzel, '@xlink:href');
        $nazevKu = $this->atribut($xpath, 'cp:zoning', $uzel, '@xlink:title') ?? '';

        $posList = $this->text($xpath, './/gml:posList', $uzel) ?? '';

        return new Parcela(
            nationalCadastralReference: $nationalRef ?? '',
            cislo: new ParcelniCislo($label ?? ''),
            katastralniUzemi: new KatastralniUzemi($this->kodZHref($hrefZoning), $nazevKu),
            vymeraM2: $vymera,
            geometrie: $this->parsujPolygon($posList),
        );
    }

    private function text(
        \DOMXPath $xpath,
        string $dotaz,
        \DOMNode $kontext
    ): ?string
    {
        $uzel = $xpath->query($dotaz, $kontext)->item(0);

        return $uzel?->textContent;
    }

    private function atribut(
        \DOMXPath $xpath,
        string $elementDotaz,
        \DOMNode $kontext,
        string $atributDotaz
    ): ?string
    {
        $element = $xpath->query($elementDotaz, $kontext)->item(0);
        if ($element === null) {
            return null;
        }

        return $xpath->query($atributDotaz, $element)->item(0)?->nodeValue;
    }

    private function kodZHref(
        ?string $href
    ): string
    {
        if ($href === null) {
            return '';
        }

        parse_str(parse_url($href, PHP_URL_QUERY) ?? '', $parametry);
        $id = $parametry['Id'] ?? ''; // např. "CZ.659541"

        return str_contains($id, '.') ? substr($id, strrpos($id, '.') + 1) : $id;
    }

    private function parsujPolygon(
        string $posList
    ): Polygon
    {
        $cisla = array_map('floatval', preg_split('/\s+/', trim($posList)));

        $body = [];
        for ($i = 0; $i < count($cisla); $i += 2) {
            $body[] = new Souradnice(lat: $cisla[$i], lon: $cisla[$i + 1]);
        }

        return new Polygon($body);
    }
}