# Map-Parcel

### Backend architektura
Zvolil jsem Symfony, ale **bez `symfony/skeleton`** — jen minimální kernel
(`framework-bundle`, `routing`, `runtime`). Cílem bylo mít plnou kontrolu
nad tím, co aplikace dělá, bez zbytečných závislostí (Twig, Doctrine ORM
apod.), které bych nevyužil.

Kód je strukturovaný podle DDD principů (Domain / Application /
Infrastructure / Presentation vrstvy) — přístup, který znám ze zkušenosti
se Spring Bootem a rozhodl jsem se ho aplikovat i tady, i když je to moje
první PHP aplikace.

### Zdroj dat
ČÚZK poskytuje INSPIRE WFS službu (`services.cuzk.cz/wfs/inspire-cp-wfs.asp`)
s typem `cp:CadastralParcel`. Rozhodnutí live dotazy vs. předstažení dat:
*(doplním, až to rozhodneme)*.

### Souřadnicový systém
ČÚZK defaultně vrací geometrii v `EPSG:5514` (S-JTSK/Křovák), i když je
dotaz ve WGS84 (BBOX parametr). Řešení: explicitní parametr
`&srsName=http://www.opengis.net/def/crs/EPSG/0/4326` v `GetFeature`
požadavku donutí server vrátit souřadnice rovnou v `EPSG:4326` (lat/lon) —
nemuseli jsme tak psát vlastní transformátor souřadnic.

### Rozsah
Katastrální území Jičín + [doplnit počet] sousedních území v okrese
(povinné minimum jsou 4 KÚ včetně Jičína).

## ## Věci které mě překvapila
- ČÚZK ignoruje požadovaný SRS v BBOX parametru, ale respektuje ho,
  když je zadaný explicitně jako `srsName` mimo BBOX.
- Souřadnice v `gml:posList` jsou v pořadí lat/lon, což se naštěstí
  shoduje s Leafletovým `LatLng` formátem (GeoJSON by čekal opačně).
- Název katastrálního území (`Jičín`) je dostupný přímo v `xlink:title`
  atributu u `administrativeUnit`/`zoning`, takže nebylo potřeba
  parsovat ho z `nationalCadastralReference`.