# Mapa parcel — Jičín a okolí

Webová aplikace zobrazující katastrální parcely na mapě. Data pocházejí z otevřených služeb
ČÚZK (INSPIRE), jsou jednorázově stažená do lokální SQLite databáze a servírovaná vlastním
PHP backendem.

**Rozsah:** 4 katastrální území — **Jičín** (659541), **Popovice u Jičína** (725838),
**Robousy** (740225), **Valdice** (776530). Dohromady ~24,4 km² a **17 256 parcel**.

---

## Spuštění

```bash
php -S localhost:8000 -t public public/router.php
```

Aplikace pak běží na <http://localhost:8000>.

Databáze je součástí repozitáře, takže **není potřeba nic stahovat ani importovat** — stačí
spustit server. Proč je binární soubor v gitu, viz [zápisník](#zápisník) níže.

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
src/parcels.php         dotazy nad parcelami (výřez, detail)
public/router.php       směrovač pro vestavěný PHP server
public/api/index.php    API — tabulka rout a ošetření vstupu
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

**Data předem stažená, ne živé dotazy na ČÚZK.**
Zadání označuje tuto volbu za součást úlohy. Živý WFS dotaz při každém pohybu mapy je
v přímém rozporu s požadavkem na plynulost a přidává závislost na dostupnosti cizího
serveru. Naměřeno: jedna odpověď WFS pro střed Jičína měla **36 MB XML** na 9 028 parcel.
Lokální SQLite odpoví na stejný výřez za jednotky milisekund.

**Hotová databáze je součástí repozitáře.**
Vědomý kompromis — binární soubor v gitu je jinak antipattern. Zde má 9,1 MB a data jsou
statická. Přínos: aplikace jde spustit jedním příkazem, bez stahování a bez závislosti na
tom, jestli ČÚZK zrovna běží. Importní skript v repozitáři zůstává a je plnohodnotnou
součástí kódu, jen ho není nutné spouštět.

**EPSG:4258 (ETRS89) se používá přímo jako WGS84.**
Předpřipravené sady jsou k dispozici jen v EPSG:5514 (S-JTSK) nebo 4258. Rozdíl mezi ETRS89
a WGS84 je ve střední Evropě zhruba půl metru až metr — obě soustavy byly totožné v roce
1989 a evropská deska se od té doby posouvá ~2,5 cm/rok. To je pod přesností samotných
katastrálních dat a na mapě neviditelné.
Zamítnuta varianta stáhnout S-JTSK a přepočítat Křovákovo zobrazení vlastními silami:
desítky řádků trigonometrie nebo cizí závislost, s reálným rizikem tiché chyby, výměnou za
submetrový zisk, který nikdo nepozná.

**Prostorový index nahrazen čtyřmi sloupci obalového obdélníku.**
SQLite umí prostorový index R-Tree, ale ve Windows buildu PHP zkompilovaný **není**
(ověřeno přes `PRAGMA compile_options`). Místo něj má každá parcela `min_lon`, `min_lat`,
`max_lon`, `max_lat` a obyčejný B-tree index; dotaz na výřez je pak běžný test překryvu
obdélníků. Při 17 256 řádcích plně dostačuje — dotaz na 5 632 parcel trvá **0,5 ms**.
R-Tree se vyplatí až o dva řády výš.

**Geometrie je v databázi uložená jako hotový GeoJSON.**
Převod z GML proběhne jednou při importu, ne při každém požadavku. Mapový endpoint pak jen
skládá řetězce — sestavení odpovědi se všemi 17 256 prvky trvá **8 ms**. Cena: v databázi
je prezentační formát a soubor je větší, než by musel být.

**Primární klíč parcely je `nationalCadastralReference`** (např. `659541-1185/3`).
Nese v sobě kód katastrálního území i parcelní číslo, tedy přesně tu kombinaci, která dává
v katastru jedinečnost. Ověřeno na vzorku 9 028 parcel: `nationalCadastralReference` je
jedinečná, **samotné parcelní číslo ne** (4 kolize přes hranice k.ú.). Navíc je dohledatelná
v `nahlizenidokn.cuzk.cz`, takže obsah API jde ověřit proti oficiálnímu zdroji.

**Při velkém výřezu se parcely nevykreslují.**
Nad nastavenou plochou vrátí API `"zoom_in": true` a prázdný seznam. Bez toho by jeden
posun mapy znamenal **6,75 MB** a 17 256 polygonů k vykreslení. Naměřeno:

| výřez | parcel | velikost odpovědi |
|---|---|---|
| 0,4 × 0,4 km | 473 | 0,24 MB |
| 0,7 × 0,7 km | 1 136 | 0,57 MB |
| 1,0 × 1,0 km | 2 408 | 1,12 MB |
| celý rozsah | 17 256 | 6,75 MB |

Zvažováno i zjednodušování geometrie při menším zoomu (Douglas–Peucker). Zamítnuto: je to
vlastní geometrický kód navíc, a hranice parcel jsou přitom smysl celé aplikace — raději
méně parcel přesně než všechny nepřesně.

**Vlastník parcely v aplikaci není.**
Svobodná data ČÚZK (WFS/INSPIRE) jméno vlastníka neobsahují — to je dostupné jen
v registrovaném/placeném přístupu do ISKN. Zadání říká „zobrazit informace o parcele" a
zároveň „nejednoznačnosti rozhodni sám a zdůvodni". Rozhodnutí: panel ukazuje to, co je
reálně dostupné zdarma. Není to opomenutí, ale hranice zdroje dat.

**Jazyková konvence: identifikátory anglicky, všechno čtené jako text česky.**
Proměnné, funkce, tabulky a sloupce jsou anglicky; komentáře, texty v UI a dokumentace
česky. Hranice nevede napříč projektem náhodně, ale mezi kódem a souvislým textem.
Hlavní důvod: čeština je flektivní a identifikátory se neskloňují — `$parcely` je zároveň
nominativ plurálu i genitiv singuláru, kdežto `$parcels` / `$parcel` tuhle dvojznačnost
nemá. Anglické názvy sloupců navíc mapují 1:1 na zdrojová pole INSPIRE
(`cp-ext:landType` → `land_type`), takže nejde o překlad, ale o převzetí názvosloví zdroje.

**Čisté PHP bez frameworku**, jak zadání preferuje. Routování je jedna tabulka rout
v `public/api/index.php`. Žádná vrstva navíc, žádná abstrakce pro jediného volajícího.

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
