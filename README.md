# Map-Parcel

Webová aplikace zobrazující katastrální parcely okresu Jičín na mapě.
Zadání pro druhé kolo pohovoru na pozici Junior programátor, VIAGEM a.s.

## Jak spustit

### Docker (doporučeno)
```bash
docker compose up --build
```
- Backend: http://localhost:8000
- Frontend: http://localhost:5173

### Lokálně, bez Dockeru

Backend:
```bash
composer install
php -S localhost:8000 -t public
```

Frontend (v druhém terminálu):
```bash
cd frontend
npm install
npm run dev
```

## Architektura

### Backend
Zvolil jsem Symfony, ale bez `symfony/skeleton` — jen minimální kernel
(`framework-bundle`, `routing`, `runtime`). Chtěl jsem mít pod kontrolou
přesně to, co appka dělá, bez závislostí (Twig, Doctrine ORM...), které
bych stejně nevyužil.

Kód je rozdělený podle DDD principů (Domain / Application / Infrastructure
/ Presentation) — přístup, co znám ze Spring Bootu, a rozhodl jsem se ho
použít i tady, přestože je to moje první PHP aplikace vůbec.

### Frontend
React + Vite + TypeScript, mapa přes `react-leaflet`. Komunikuje s
backendem přes jediný GeoJSON endpoint (`GET /api/parcely?jih=&zapad=&sever=&vychod=`).

## Jak appka získává a servíruje data

Tohle byla nejzajímavější část úlohy, takže krátký příběh, jak jsem se
k finálnímu řešení dostal.

### Zdroj dat
ČÚZK nabízí INSPIRE WFS službu (`services.cuzk.cz/wfs/inspire-cp-wfs.asp`,
typ `cp:CadastralParcel`). Zvažoval jsem dvě cesty: tahat data živě při
každém requestu, nebo si je předem stáhnout a servírovat z lokálního
souboru. Zvolil jsem třetí, kompromisní cestu — **živé dotazy s cache a
dlaždicováním** (popsáno níže) — protože kombinuje výhody obou: appka
vždy vidí aktuální data z katastru, ale opakované pohledy na stejné
místo jsou rychlé díky cache, a dotaz na celý okres najednou backend
rovnou odmítne, než by se pokusil stáhnout desetitisíce parcel.

### Souřadnicový systém
ČÚZK defaultně vrací geometrii v `EPSG:5514` (S-JTSK/Křovák), i když je
dotaz ve WGS84 (BBOX parametr). Řešení: explicitní parametr
`&srsName=http://www.opengis.net/def/crs/EPSG/0/4326` v `GetFeature`
požadavku donutí server vrátit souřadnice rovnou v `EPSG:4326` (lat/lon)
— nemuseli jsme tak psát vlastní transformátor souřadnic.

### Cache
Přístup k ČÚZK je schovaný za rozhraním `ParcelaRepository`.
`CachedParcelaRepository` je dekorátor kolem živé implementace (Symfony
Cache, TTL 24 h) — jde vypnout přepsáním aliasu v `services.yaml`.
Samotná cache na bbox mapy ale nestačí: klíč cache musí být něco
stabilního, jinak se netrefí při každém sebemenším posunu mapy. Proto:

### Dlaždicování
Bbox z mapy se rozdělí na pevnou mřížku 0,01° (`TileGrid`) — každá
dlaždice se stahuje a cachuje zvlášť, takže se cache trefuje i při
posouvání mapy (dva různé pohledy přes stejné místo dají stejné
dlaždice). Parcely na hranici dvou dlaždic přijdou dvakrát, proto
deduplikace podle `nationalCadastralReference`. Dotaz přes víc než 16
dlaždic backend odmítne (`PrilisVelkaOblastException`), aby jeden
pohled na celý okres nespustil stovky dotazů na ČÚZK — frontend proto
ukazuje parcely až od určitého přiblížení.

**Jak jsem k číslu 16 došel:** `GetServiceProperties` sice udává limit
30 000 objektů a 10 000 ha na dotaz (naše dlaždice na to má obrovskou
rezervu), takže technicky by šlo dlaždice zvětšit. Změřil jsem to ale
na reálných datech (centrum Jičína, nejhustší oblast v okrese):

| Dlaždice | Plocha | Síť | Parsing | XML | Parcel |
|---|---|---|---|---|---|
| 0,01° | ≈ 79 ha | 2,1 s | 0,04 s | 3,9 MB | 1 803 |
| 0,03° | ≈ 710 ha | 7,1 s | 0,24 s | 22,8 MB | 10 422 |

Parsing je vůči síti zanedbatelný (pod 1 % celkového času) — úzké
hrdlo je čistě čekání na ČÚZK. Menší dlaždice s víc paralelními
dotazy tak vychází lépe než pár velkých sekvenčních.

### Paralelní stahování
`CachedParcelaRepository` nejdřív zjistí, které dlaždice chybí v cache
(bez volání sítě), a teprve ty chybějící stáhne přes
`CuzkWfsClient::stahniSurovaDataDavkove()`. Ta metoda pošle všechny HTTP
požadavky, než začne číst první odpověď — Symfony HttpClient je díky
tomu provede souběžně, bez ručního psaní async/await. První načtení
pohledu s ~12 dlaždicemi tak netrvá 12 × 2 s po sobě.

## Frontend — poznámky k implementaci
- `preferCanvas: true` na Leaflet mapě — stovky až tisíce polygonů na
  jeden pohled by ve výchozím SVG rendereru zamrzly.
- Odhad počtu dlaždic (`bboxLimit.ts`) na frontendu zrcadlí backendový
  `TileGrid`/`MAX_DLAZDIC` limit — frontend tak nikdy neodešle dotaz,
  o kterém předem ví, že skončí chybou 422. Je to vědomé zdvojení
  logiky mezi frontendem a backendem kvůli rychlejší odezvě pro
  uživatele; backend zůstává finálním rozhodčím a limit stejně
  vynucuje i on.
- API adresa (`http://localhost:8000`) je zatím natvrdo v kódu — pro
  lokální spuštění, jak úloha vyžaduje, to stačí. V reálném nasazení
  by šla přes environment proměnnou.
- Testováno ručně proti běžícímu backendu (zoom, pan, výběr parcely,
  stav při velkém oddálení) — automatizované e2e testy (Playwright)
  jsem nechal stranou, nevešly se do časového rámce úlohy.

## Testování
Parser je pokrytý unit testy (PHPUnit) nad reálnou fixture staženou z
ČÚZK WFS (`tests/Fixtures/cuzk_sample_response.xml`) — testy tak neběží
proti vymyšleným datům, ale proti skutečné struktuře odpovědi, včetně
jejích specifik (namespaces, xlink atributy).

## Rozsah
Katastrální území Jičín + *(doplnit, až vybereme sousední území — zadání
chce minimálně 4 KÚ včetně Jičína)*.

## Co mě cestou překvapilo
- ČÚZK ignoruje požadovaný souřadnicový systém v BBOX parametru, ale
  respektuje ho, když je zadaný samostatně jako `srsName`.
- Souřadnice v `gml:posList` jsou v pořadí lat/lon, což se naštěstí
  shoduje s Leafletovým `LatLng` (GeoJSON by čekal opačné pořadí).
- Název katastrálního území (`Jičín`) je dostupný přímo v atributu
  `xlink:title` u `administrativeUnit`/`zoning` — nebylo potřeba ho
  pracně parsovat z `nationalCadastralReference`.
- XPath rozlišuje element a atribut stejného jména jen prefixem `@`
  (`xlink:title` = element, `@xlink:title` = atribut) — snadná chyba
  k přehlédnutí, protože chybějící `@` nevyhodí výjimku, jen tiše
  vrátí prázdný výsledek.
- Souřadnice se do GeoJSON odpovědi propsaly jako řetězce místo čísel
  (`"15.339"` místo `15.339`) — PHP bez `declare(strict_types=1)`
  typové chyby tiše toleruje, takže se to neprojevilo výjimkou, jen
  špatným výstupem. Opraveno castem na `float`, na dvou místech
  (parser i GeoJSON transformace), pro jistotu.
- `react-leaflet`'s `<GeoJSON>` komponenta znovu nenačte data ani
  nepřeregistruje event handlery po prvním vykreslení — bez opravy by
  se druhé a další volání API vůbec nepromítlo do mapy. Řešeno
  remountem komponenty podle verze dat a `ref` pro živý stav výběru.

## Další kompromis
Katastr rozlišuje pozemkové parcely a stavební parcely (`st. 4559`).
Původní validace parcelního čísla vycházela jen ze tří vzorových parcel
a první reálná dlaždice v centru Jičína (plná stavebních parcel) ji
shodila. Fixture s `count=3` nebyla dostatečně reprezentativní — poučení
na příště. Přidal jsem parametrizovaný test na všechny známé formáty.

## Debugování DI kontejneru
Přidal jsem `bin/console` (Symfony Console) hlavně kvůli
`debug:container` — ukázalo se to jako nutné při ladění záhadné
"circular reference" chyby, která nakonec byla způsobená starým,
zapomenutým souborem třídy na špatném místě (nesoulad mezi cestou a
namespace). Bez tohoto nástroje bych to jen hádal.

## CORS
Backend a frontend běží na různých portech (`nelmio/cors-bundle`,
povoleno pro libovolný `localhost` port). Tohle mimochodem odhalilo i
chybějící řádek v `Kernel.php` — protože kernel je psaný ručně, bez
`symfony/skeleton`, `config/packages/*.yaml` se nenačítalo automaticky,
takže konfigurace nainstalovaného bundlu byla úplně ignorovaná, bez
jakékoliv chybové hlášky.

## Co bych s větším časem řešil jinak
- **E2e testy** (Playwright) pro frontend — teď je ověřené jen ručně.
- **Cache v Redisu místo souborů** — pro jeden proces na jednom
  stroji souborová cache stačí, ale nesdílí se mezi víc instancemi
  appky.
- **PostGIS** místo cache po dlaždicích — kdybych chtěl pokrýt celý
  okres, dávalo by smysl data jednou naimportovat do prostorové
  databáze a dotazovat se nad ní, místo spoléhat na ČÚZK při každém
  cache missu.
- **Konzolový příkaz na předehřátí cache** pro zvolená katastrální
  území — řešilo by to pomalé úplně první načtení nové oblasti.
- **Environment proměnná pro API adresu** na frontendu místo natvrdo
  napsané `localhost:8000`.