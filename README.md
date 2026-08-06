# Mapa parcel — Jičín a okolí

Webová aplikace zobrazující katastrální parcely na mapě. Data pocházejí z otevřených služeb
ČÚZK (INSPIRE), jsou jednorázově stažená do lokální SQLite databáze a servírovaná vlastním
PHP backendem.

**Rozsah:** 4 katastrální území — **Jičín** (659541), **Popovice u Jičína** (725838),
**Robousy** (740225), **Valdice** (776530). Dohromady ~24,4 km² a **17 256 parcel**.

### Kudy číst

| kde | co tam je | kdy to otevřít |
|---|---|---|
| **tento soubor** | spuštění, struktura, API, krátký zápisník | vždycky — na ostatní se dá vykašlat |
| [`docs/rozhodnuti.md`](docs/rozhodnuti.md) | všechna rozhodnutí tematicky: co, jaké alternativy, proč, co to stálo | když vás zajímá „proč zrovna takhle" |
| [`docs/plan.md`](docs/plan.md) | cíle rozebrané po bodech ze zadání a jak poznám, že jsou splněné | když chcete vidět, jak jsem zadání četl |
| [`docs/roadmap.md`](docs/roadmap.md) | chronologický pracovní log, 29 záznamů | jen jako doklad postupu, není to text ke čtení |

---

## Spuštění

```bash
php -S localhost:8000 -t public public/router.php
```

Aplikace pak běží na <http://localhost:8000>.

Databáze je součástí repozitáře, takže **není potřeba nic stahovat ani importovat** — stačí
spustit server. Proč je binární soubor v gitu, viz [zápisník](#zápisník) níže.

Ověřeno ručně v **Chrome** i **Firefoxu**.

### Předpoklady

- **PHP 8.1 nebo novější** (vyvíjeno na 8.5.9). Nutné kvůli `readonly` vlastnostem,
  návratovému typu `never` a pojmenovaným argumentům.
- Zapnutá rozšíření:

  | rozšíření | k čemu |
  |---|---|
  | `pdo_sqlite`, `sqlite3` | čtení databáze |
  | `xmlreader` | streamované parsování GML při importu |
  | `zip` | rozbalení stažených sad z ČÚZK |
  | `openssl` | HTTPS spojení na ČÚZK |

  Poslední tři jsou potřeba jen pro `import.php`. Pro samotný běh aplikace stačí SQLite.
  Ověření: `php -m`.

### Volitelně: nový import dat

```bash
php import.php
```

Stáhne data z ČÚZK (~9,7 MB), rozbalí je, vyparsuje a znovu postaví `db/parcels.sqlite`.
Trvá zhruba 5 sekund. Stažené archivy se kešují v `data/` (gitem ignorováno), takže opakovaný
běh už znovu nestahuje.

---

## Struktura

```
import.php              jednorázový import dat z ČÚZK do SQLite
src/Parcel.php          jedna parcela připravená k zápisu (readonly objekt)
src/Database.php        otevření databáze
src/parcels.php         dotazy nad parcelami (výřez, detail) a limit výřezu
public/router.php       směrovač pro vestavěný PHP server
public/api/index.php    API — tabulka rout a ošetření vstupu
public/index.html       kostra stránky (mapa + info panel)
public/style.css        rozvržení
public/app.js           klient — mapa, načítání podle výřezu, info panel
public/vendor/          Leaflet 1.9.4 (leaflet.js, leaflet.css)
db/parcels.sqlite       hotová databáze (součást repozitáře)
```

Web root je `public/` — proto se server spouští s `-t public`. Databáze ani importní skript
tak nejsou dostupné přes HTTP.

## API

| endpoint | parametry | vrací |
|---|---|---|
| `GET /api/parcels` | `bbox=west,south,east,north` | GeoJSON `FeatureCollection` parcel ve výřezu |
| `GET /api/parcel` | `ref=659541-1185/3` | detail jedné parcely pro info panel |
| `GET /api/zonings` | — | seznam katastrálních území |

Souřadnice v `bbox` jsou v pořadí **lon,lat** — shodně s GeoJSON i s `toBBoxString()`
v Leafletu, takže klient nemusí nic přehazovat.

Chybný vstup vrací **HTTP 400** s popisem v JSONu, neexistující parcela **404**. Prázdná
odpověď nikdy neznamená chybu — to je vědomá reakce na chování zdrojového serveru (viz
[Co překvapilo](#co-překvapilo)).

Při příliš velkém výřezu vrátí `/api/parcels` prázdný seznam s příznakem `"zoom_in": true`
místo desítek tisíc polygonů. Zdůvodnění níže.

---

## Zdroj dat

**WFS INSPIRE Cadastral Parcels Extended (CPX)** —
`services.cuzk.gov.cz/wfs/inspire-cpx-wfs.asp`

Použit uložený dotaz `GetSpatialDataSet`, který vydává předpřipravené GML sady rozdělené
po katastrálních územích (`DataSetIdCode=CPX.SD.<kód k.ú.>`), zipované, v EPSG:4258.

Volba **CPX místo základní CP** je záměrná: ověřeno přes `DescribeFeatureType`, že jen
rozšířená varianta obsahuje `landType` a `landUse` (druh pozemku a způsob využití). Bez nich
by info panel neměl co zobrazit. CPX je nadmnožina CP se stejným bezplatným přístupem.

České popisky druhů pozemků se stahují z **registru číselníků ČÚZK**
(`services.cuzk.cz/registry/codelist/…?format=json`), nepíší se ručně — znění jako
„Zastavěná plocha a nádvoří" je tak oficiální, ne náš překlad.

Rozsah polí byl ověřen proti `nahlizenidokn.cuzk.cz`. Do info panelu jde parcelní číslo,
výměra, druh pozemku, způsob využití a katastrální území.

---

## Zápisník

### Rozhodnutí a proč

Tady je šest rozhodnutí, která nejvíc určila výslednou podobu. Zbytek — všechna rozhodnutí
i se zamítnutými alternativami a jejich cenou — je tematicky v
[`docs/rozhodnuti.md`](docs/rozhodnuti.md).

**Data předem stažená, ne živé dotazy na ČÚZK.**
Zadání označuje tuhle volbu za součást úlohy. Naměřeno: jedna odpověď WFS pro střed Jičína měla
**36 MB XML** na 9 028 parcel. Živý dotaz při každém pohybu mapy je v přímém rozporu s požadavkem
na plynulost a přidává závislost na tom, jestli cizí server běží. Lokální SQLite odpoví na stejný
výřez v jednotkách milisekund.

**SQLite jako soubor, a hotová databáze je součástí repozitáře.**
Zadání vyžaduje spustitelnost lokálně — SQLite nepotřebuje žádný server k instalaci. Binárka
v gitu je jinak antipattern; tady má 9,1 MB, data jsou statická a přínos je konkrétní: aplikace
se spustí jedním příkazem, bez stahování. Importní skript zůstává plnohodnotnou součástí kódu,
jen ho není nutné spouštět.

**Nad velkým výřezem se parcely nevykreslují.**
API vrátí `"zoom_in": true` a mapa vyzve k přiblížení. Limit není odhad — je doladěný měřením
celého řetězce v prohlížeči (stažení + `JSON.parse` + vykreslení):

| plocha výřezu | šířka | parcel | odpověď | celkem |
|---|---|---|---|---|
| 2,0 × 10⁻⁴ | 1,3 km | 1 331 | 0,55 MB | 46 ms |
| **1,0 × 10⁻³** | **2,9 km** | **5 669** | **2,36 MB** | **209 ms** ← zvolený limit |
| 1,35 × 10⁻² | 10,7 km | 17 256 | 6,75 MB | 559 ms (celý rozsah) |

Pauza kolem 200 ms po dotažení pohybu ještě splyne s pohybem samotným, půl sekundy už je vidět.

**Leaflet kreslí do canvasu, ne do SVG** (`preferCanvas: true`).
Ve výchozím nastavení vytvoří Leaflet jeden SVG uzel na každou parcelu — při tisících polygonů
se prohlížeč utopí ve správě DOM. S canvasem jde všechno do jediného plátna: vykreslení všech
17 256 parcel trvá **402 ms**. Cena: polygony nejdou stylovat přes CSS. Při dvou stylech
(běžná / vybraná parcela) to nevadí.

**Čisté PHP bez frameworku, klient vanilla JS s Leafletem.**
Zadání preferuje čisté PHP; routování je jedna tabulka rout. Na klientovi byl vážně zvažován
Next.js a zamítnut: přibyl by druhý server vedle PHP, README by začínalo instalací Node, a Leaflet
se s Reactem pere o vlastnictví DOM — to všechno kvůli 247 řádkům, kde framework nemá co spravovat.

**Vlastník parcely v aplikaci není.**
Svobodná data ČÚZK (WFS/INSPIRE) jméno vlastníka neobsahují — je jen v registrovaném/placeném
přístupu do ISKN. Zadání říká „zobrazit informace o parcele" a zároveň „nejednoznačnosti rozhodni
sám a zdůvodni". Panel proto ukazuje to, co je reálně dostupné zdarma. Není to opomenutí, ale
hranice zdroje dat.

### Co překvapilo

**Zdrojový server neohlašuje chyby — tiše vrací prázdno.** Tohle se opakovalo pětkrát
a stálo nejvíc času ze všeho:

| chyba ve vstupu | co server udělal |
|---|---|
| feature type bez prefixu (`CadastralParcel`) | HTTP 200, `numberMatched="0"` |
| `bbox` v pořadí lat,lon místo lon,lat | HTTP 200, prázdný výsledek |
| neenkódovaná diakritika (`ZONING_NAME=Jičín`) | HTTP 200, prázdný výsledek |
| element `cp:CadastralParcel` místo `cp-ext:` | 0 nalezených prvků |
| neplatný `DataSetIdCode` | chyba „Unsupported CRS", ačkoliv CRS byl v pořádku |

Přímý důsledek pro tento projekt: **naše API tohle nedělá**. Chybný vstup vrací 400
s popisem, ne prázdný seznam.

**Pořadí souřadnic se liší podle cesty k datům.** Stejný server vrací přes `GetFeature`
s `srsName=EPSG:4326` pořadí lon,lat, ale v předpřipravených sadách v EPSG:4258 pořadí
lat,lon. GeoJSON chce lon,lat. Prohození nezpůsobí chybu — mapa jen tiše vykreslí parcely
někde v Somálsku.

**Soubor uvnitř archivu se jmenuje `.xml`, ne `.gml`**, přestože obsahuje GML. Kód hledající
v archivu `*.gml` by nenašel nic. Proto se jméno čte z archivu, ne odhaduje.

**Registr číselníků a data odkazují na jiný hostitel.** Číselník vydává identifikátory na
`services.cuzk.gov.cz`, zatímco GML odkazuje na `services.cuzk.cz`. Spojení podle celé URL
by nenašlo ani jednu shodu; proto se z obou stran bere jen poslední část za lomítkem.

**Chybějící `landUse` u ~74 % parcel není chyba.** Ověřeno na Valdicích: 241 hodnot
s odkazem + 812 s `xsi:nil="true"` = 1 053 parcel. ČÚZK u části parcel způsob využití
prostě nevede.

**Desetina parcel má díru.** 647 parcel v Jičíně (~10 % celkem) obsahuje `gml:interior` —
například dvůr uvnitř budovy. GeoJSON to umí vyjádřit jako další prstenec za vnějším
obrysem; kdyby se díry zahodily, plochy by se vykreslily jako plné.

**Rozbalená data jsou 30× větší než zipovaná** — 318 MB proti 10,5 MB. Proto se čte přímo
z archivu přes wrapper `zip://` a nic se nerozbaluje na disk. Celý import má vrchol paměti
**4 MB**, přestože rozbalený Jičín má 239 MB.

### Co bych udělal s víc časem

- **Pokrytí celého okresu Jičín**, ne jen 4 katastrálních území. Postup je připravený —
  stačí doplnit kódy do konstanty `ZONINGS`, import je na počtu území nezávislý.
- **Zjednodušování geometrie podle zoomu**, aby šlo zobrazit i celý rozsah najednou.
  Případně předpočítat několik úrovní detailu při importu.
- **Vyhledávání parcely podle čísla.** ČÚZK na to má i uložené dotazy `GetParcel`
  a `GetNeighbourParcels`.
- **Vektorové dlaždice** místo GeoJSON pro výrazně větší rozsah.
- **Automatické testy** parseru — zejména na pořadí souřadnic a na parcely s dírami, tedy
  přesně ta místa, kde se chyba neprojeví výjimkou, ale tichým nesmyslem na mapě.
