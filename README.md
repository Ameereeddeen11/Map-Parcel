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

## Věci které mě překvapila
- ČÚZK ignoruje požadovaný SRS v BBOX parametru, ale respektuje ho,
  když je zadaný explicitně jako `srsName` mimo BBOX.
- Souřadnice v `gml:posList` jsou v pořadí lat/lon, což se naštěstí
  shoduje s Leafletovým `LatLng` formátem (GeoJSON by čekal opačně).
- Název katastrálního území (`Jičín`) je dostupný přímo v `xlink:title`
  atributu u `administrativeUnit`/`zoning`, takže nebylo potřeba
  parsovat ho z `nationalCadastralReference`.
- XPath rozlišuje elementz a atributy stejného jména jen prefixem `@` 
  (`xlink:title` = element, `@xlink:title` = atribut) a to vede na snadnou chybu
  k přehlednuti, protože chybějíci `@` nevyhodi vyjimku, jen tiše 
  vrati práydný vysledek.

## Testování
Parser je pokrytý unit test (PHPUnit) nad realnou fixture staženou z ČÚZK WFS
(`tests/Fixtures/cuzk_sample_response.xml`) - testy tak neběží proti 
mockovaným datům, ale proti skutečné struktuře odpovědi, včetně jejich 
specifik (namespaces, xlink atributy)

### Cache
Přístup k ČÚZK je za rozhraním `ParcelaRepository`. `CachedParcelaRepository`
je dekorátor kolem živé implementace (Symfony Cache, TTL 24 h). Cache jde
vypnout přepsáním aliasu v `services.yaml`. Známé omezení: klíč je přesný
bbox, takže cache se netrefuje při posouvání mapy (řeší další krok: dlaždice).

### Dlaždicování
Bbox z mapy se rozdělí na pevnou mřížku 0,01° (`TileGrid`); každá dlaždice
se stahuje a cachuje zvlášť, takže se cache trefuje i při posouvání mapy.
Parcely na hranici dlaždic přijdou dvakrát, proto deduplikace podle
`nationalCadastralReference`. Dotaz přes víc než 100 dlaždic backend
odmítne (`PrilisVelkaOblastException`), aby jedním pohledem na celý okres
nespustil stovky dotazů na ČÚZK. Frontend proto ukazuje parcely až od
určitého přiblížení.

### Velikost dlaždice (měření)
ČÚZK limity (30 000 objektů, 10 000 ha na dotaz) nejsou omezující,
velikost dlaždice jsem proto volil podle měření (studená odpověď,
centrum Jičína = nejhustší oblast):

| Dlaždice | Plocha | Čas | XML | Parcel |
|---|---|---|---|---|
| 0,01° | ≈ 79 ha | 2,1 s | 3,9 MB | 1 803 |
| 0,03° | ≈ 710 ha | 7,1 s | 22,8 MB | 10 422 |

Čas roste zhruba jako 1 s režie + 0,6 ms na parcelu. Zvolil jsem 0,01°
kvůli paměťové náročnosti DOM parseru u velkých odpovědí a kratšímu
času jednoho dotazu. Nejvíc času při prvním načtení zabírá sekvenční
stahování dlaždic, proto paralelizace (další krok).

## Kompromis 
- ČÚZK rozlišuje pozemkové parcely a stavební parcely (`st. 4559`). 
  Původní validace vycházela ze tří vzorových parcel a první dlaždice 
  v centru Jičína ji shodila. Fixture s `count=3` nebyla dostatečně 
  reprezentativní, proto jsem přidal parametrizovaný test na všechny 
  známé formáty.