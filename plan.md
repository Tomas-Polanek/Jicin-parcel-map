# Plán: Mapa parcel okresu Jičín (Viagem interview úloha)

> Tento dokument vychází přímo ze zadání (`Interview_uloha_Programator.pdf`) a je rozdělený podle
> jednotlivých cílů, které zadání explicitně uvádí. U každého cíle je uvedeno, podle čeho poznám,
> že je splněný, a jak ho plánuji naplnit.

**Repozitář:** https://github.com/Tomas-Polanek/Jicin-parcel-map

## Cíle z části "Zadání"

| # | Cíl (ze zadání) | Jak poznám, že je splněný | Jak to naplním |
|---|---|---|---|
| 1 | Webová aplikace zobrazí parcely okresu Jičín na mapě | Po načtení stránky se na mapě vykreslí polygony parcel pro zvolená katastrální území | Leaflet mapa + PHP endpoint vracející parcely jako GeoJSON pro aktuální výřez |
| 2 | Kliknutí na parcelu zobrazí informace o ní | Klik na libovolný vykreslený polygon otevře panel/popup s atributy dané parcely | Klik → přečtení ID parcely → dotaz na detail endpoint → vykreslení panelu |
| 3 | Aplikace zůstane plynulá i při zobrazení celého zvoleného rozsahu | Posun/zoom mapy nad celým zvoleným rozsahem bez viditelného sekání či zamrznutí | Načítání jen podle výřezu mapy (bbox), debounce na pohyb mapy, zjednodušení geometrie při menším zoomu |

## Cíle z části "Stack a omezení"

| # | Cíl | Jak poznám, že je splněný | Plán |
|---|---|---|---|
| 4 | Backend musí být v PHP, nejlépe čisté PHP; framework je na mně | Veškerá serverová logika je v PHP, bez frameworku typu Laravel/Symfony | Čisté PHP s malým vlastním routerem (několik explicitních route) |
| 5 | Frontend a ostatní technologie jsou otevřené | — | JS (případně TS), Leaflet — jde o volnou volbu, ne o omezení |
| 6 | Povinné minimum: katastrální území Jičína + alespoň 3 další v okrese | V datech i na mapě existují a fungují alespoň 4 pojmenovaná katastrální území, jedno z nich Jičín | Předem vybrat Jičín + 3 sousední území, uvést je jmenovitě v README |
| 7 | Pokrytí celého okresu je bonus, není povinné | — | Vědomě odloženo do sekce "co bych udělal s víc časem" v README, aby zbyl čas na výkon (viz #3) |
| 8 | Musí fungovat v Chrome a Firefoxu | Ruční otestování v obou prohlížečích proběhlo před odevzdáním | Manuální kontrola před dokončením |
| 9 | Musí jít spustit lokálně, hostování není podmínka | Recenzent aplikaci spustí jen s lokálním PHP, bez nasazeného serveru | SQLite (soubor, žádný DB server) místo Postgres/PostGIS; přesné kroky spuštění v README |

## Cíle z části "Zdroje dat"

| # | Cíl | Jak poznám, že je splněný | Plán |
|---|---|---|---|
| 10 | Použít ČÚZK WFS/WMS (services.cuzk.cz), výdejní služby RÚIAN nebo INSPIRE | Geometrie a atributy parcel prokazatelně pocházejí z jednoho z těchto zdrojů, zdokumentováno v README | **Potvrzeno:** WFS INSPIRE Cadastral Parcels **Extended (CPX)**, `services.cuzk.gov.cz/wfs/inspire-cpx-wfs.asp`, feature type `CadastralParcel`. Důvod volby CPX místo základní CP: ověřeno přes `DescribeFeatureType`, že CPX navíc obsahuje atribut `landType`/`hilucsLandType` (druh pozemku), který základní CP schéma nemá vůbec — bez toho by nešlo naplnit cíl #11/info panel. CPX je nadmnožina CP se stejným veřejným/bezplatným přístupem, takže bez reálné nevýhody. |
| 11 | Použít nahlizenidokn.cuzk.cz k ověření, jaké údaje o parcele vůbec existují | README uvádí, jaká pole byla ověřena a která byla vybrána do info panelu | Ruční kontrola několika reálných parcel v Jičíně v tomto prohlížeči před návrhem panelu |
| 12 | Zvolit mapový podklad: OSM / Mapy.cz / Mapbox | Zvolen jeden podklad s odůvodněním | OSM dlaždice — zdarma, bez API klíče, bez rizika limitu při pohovoru |
| 13 | Explicitně rozhodnout: data živě, nebo předem stažená — zadání to označuje jako součást úlohy | README obsahuje konkrétní rozhodnutí a zdůvodnění právě k této otázce | Data stáhnout jednorázově skriptem, uložit do SQLite, servírovat z vlastního backendu. Důvod: živé WFS dotazy při každém pohybu mapy jsou v rozporu s cílem #3 (plynulost) a přidávají závislost na dostupnosti cizího serveru v den pohovoru |

## Cíle z části "Co odevzdat"

| # | Cíl | Jak poznám, že je splněný | Plán |
|---|---|---|---|
| 14 | Repozitář nebo zip s celým projektem, commit history vítaná | Git repozitář existuje s postupnými commity, ne jedním velkým | Repozitář založit hned na začátku, commitovat po milnících (datový pipeline, API, frontend, výkon) |
| 15 | README: jak spustit, jaké jsou předpoklady (PHP verze, závislosti, DB) | Recenzent spustí aplikaci jen podle README, bez dalších dotazů | README psát průběžně, na konci ověřit spuštěním na čisto |
| 16 | Krátký zápisník: rozhodnutí, co překvapilo, co bych udělal jinak s víc časem | README/zápisník obsahuje záznam ke každému netriviálnímu rozhodnutí z této tabulky (zejména #6, #13, mezera s vlastnictvím níže) | Veden průběžně během práce, ne rekonstruován zpaměti na konci — viz `roadmap.md` |

## Cíle z části "Co hodnotíme" — určují prioritu času, ne samostatné úkoly

| # | Kritérium | Dopad na plán |
|---|---|---|
| 17 | Postup k práci, rozhodování a proč | Ke každému nejednoznačnému bodu (výběr území, live vs. cache, úložiště, framework) existuje zapsané zdůvodnění — tato tabulka se přenese do README |
| 18 | Funkčnost | Cíle 1–2 musí prostě fungovat od začátku do konce |
| 19 | Plynulost a výkon | Zadání to opakuje dvakrát → čas se prioritizuje sem před pokrytím celého okresu (#7) |
| 20 | Kvalita PHP kódu, čitelnost, struktura | Čisté PHP (#4), malé explicitní routy, žádný mrtvý kód, žádná předčasná abstrakce |
| 21 | Vlastní iniciativa nad rámec minima | Řeší se až po zajištění bodů 17–20 — např. širší pokrytí okresu, filtrování, vyhledávání, vektorové dlaždice — uvedeno jako "co dál", pokud nezbyde čas na plné dokončení |

## Cíle z části "Praktické"

| # | Cíl | Plán |
|---|---|---|
| 22 | Čas cca 4–12 h; pokud víc, napsat proč do README | Průběžně sledovat čas po milnících (viz `roadmap.md`), případný přesah zdůvodnit |
| 23 | Nejednoznačnosti: rozhodnout sám, zdůvodnit v README | Platí přímo pro mezeru s vlastnickými údaji níže |
| 24 | Odevzdat link/zip den před pohovorem; společné spuštění a diskuze na pohovoru | Repozitář držet spustitelný po každém commitu, ne jen na úplném konci |

## Otevřená mezera, kterou zadání neřeší — vyřešeno teď, ne za běhu

Zadání říká, že po kliknutí na parcelu se mají zobrazit "informace o ní", ale svobodná data ČÚZK
(WFS/INSPIRE) neobsahují vlastníka — jméno vlastníka je jen v placeném/registrovaném přístupu do
ISKN. Podle bodu #23 ("rozhodni sám, zdůvodni") zní rozhodnutí: info panel zobrazí to, co je reálně
dostupné zdarma — parcelní číslo, výměra, druh pozemku, katastrální území. Omezení ohledně
vlastnictví bude v README uvedeno jako vědomé, zdůvodněné rozhodnutí, ne jako mezera zjištěná pozdě.

## Rozhodnutá konfigurace (shrnutí)

- **Backend:** čisté PHP, vlastní malý router, PDO + SQLite.
- **Data:** jednorázově stažená z ČÚZK WFS INSPIRE **CPX**
  (`services.cuzk.gov.cz/wfs/inspire-cpx-wfs.asp`), uložená v SQLite. Konkrétně přes uložený dotaz
  **`GetSpatialDataSet`**, jeden dotaz na katastrální území (`DataSetIdCode=CPX.SD.<kód k.ú.>`),
  v **EPSG:4258**. Zdůvodnění a přijaté nevýhody viz `roadmap.md` (záznam 2026-08-04 13:20).
- **Frontend:** vanilla JS (případně TS), Leaflet, dotazy na backend podle bbox výřezu mapy.
- **Mapový podklad:** OpenStreetMap.
- **Rozsah (4 katastrální území, potvrzeno; kódy k.ú. ověřeny přes `GetZoningByName`):**
  - **Jičín** — kód **659541** (12,06 km²) — povinné minimum, samotné město.
  - **Popovice u Jičína** — kód **725838** (4,13 km²) — přímo sousedí, součást obce Jičín.
  - **Robousy** — kód **740225** (7,29 km²) — přímo sousedí, součást obce Jičín.
  - **Valdice** — kód **776530** (0,9 km²) — přímo sousedí, samostatná obec (na rozdíl od předchozích dvou jde
    o skutečně nezávislé katastrální území, ne jen část Jičína) — vhodné pro ověření, že aplikace
    zvládá více nezávislých k.ú. najednou.
  - Souhrnná plocha ~24,4 km² — dost na smysluplný test výkonu, málo na to, aby to bránilo
    dokončení v časovém rozpočtu (cíl #22).
- **Stahování (rozhodnuto 2026-08-04):** zipované sady (`zipped=true`), ~10,5 MB proti ~318 MB
  nezipovaně. Vyžaduje rozšíření `zip` v `php.ini` (patří do README mezi předpoklady).
- **Hotová SQLite databáze se commitne do repozitáře** spolu s importním skriptem, aby recenzent
  spustil aplikaci jen přes `php -S` bez stahování dat. Vědomý kompromis (binární soubor v gitu),
  zdůvodnění patří do README.
- **České popisky `landType`/`landUse` (rozhodnuto 2026-08-04):** stahují se při importu z registru
  číselníků ČÚZK (`services.cuzk.cz/registry/codelist/{LandTypeValue,LandUseValue}?format=json`),
  nepíšou se ručně — znění je tak oficiální, ne náš překlad.
- **Objem dat (naměřeno 2026-08-04):** 17 256 parcel ve 4 k.ú. (Jičín 12 284, Robousy 2 444,
  Popovice 1 475, Valdice 1 053).
- **Jazyková konvence (rozhodnuto 2026-08-04):** identifikátory (proměnné, funkce, tabulky,
  sloupce) **anglicky**, komentáře **česky**, texty v UI **česky**, dokumentace **česky**.
  Hranice vede mezi kódem a souvislým textem, ne napříč projektem. Zdůvodnění viz `roadmap.md`
  (záznam 2026-08-04 13:35); do README patří krátký odstavec s vysvětlením.
- **Vlastnická data:** nejsou součástí info panelu — zdůvodněno v README.
- **Souřadnicový systém:** ČÚZK vrací geometrii v S-JTSK (EPSG:5514), Leaflet/OSM potřebují WGS84
  (EPSG:4326). Řešeno požadavkem `srsName=EPSG:4326` přímo ve WFS dotazu (server přepočet udělá
  sám), místo ručního přepočtu v našem skriptu — méně kódu, menší riziko chyby. Ověřit při reálném
  stažení, že to CPX server skutečně podporuje; pokud ne, náhradní plán je přepočet po straně
  klienta/skriptu.

## Vyřešeno 2026-08-04 (ověřeno reálnými dotazy na CPX)

- **Souřadnicový systém potvrzen:** `srsName=EPSG:4326` server podporuje, ruční přepočet ze S-JTSK
  odpadá. Pozor na dvě pasti zjištěné při ověřování: feature type musí být s prefixem
  (`cp-ext:CadastralParcel`) a `bbox` se zadává v pořadí lon,lat — v obou případech vrací server
  při chybě HTTP 200 a prázdný výsledek, ne chybovou hlášku.
- **Unikátní klíč parcely:** `cp:nationalCadastralReference` (např. `659541-1185/3`, u stavebních
  `659541-st. 3047`). Řetězec sám nese kód k.ú. + parcelní číslo + typ parcely, tedy přesně tu
  trojici, která dává v katastru jedinečnost. Ověřeno na 9 028 parcelách: jedinečné, zatímco
  samotné parcelní číslo jedinečné není. Zdůvodnění výběru viz `roadmap.md`.

## Zbývá doladit (další session, před psaním kódu)

- Při reálném stažení dat z CPX vizuálně/datově potvrdit, že hranice zvolených 4 k.ú. skutečně
  na sebe navazují tak, jak předpokládá tento plán (odhad zatím z popisných zdrojů, ne z geometrie).
*(Prázdné — všechna rozhodnutí, která si tento plán vytkl před implementací, jsou uzavřená.
Schéma databáze rozhodnuto 2026-08-04, viz `roadmap.md`, záznam 14:40. Zbývá už jen jedno
ověření, které nejde udělat dřív než nad hotovou mapou: vizuálně potvrdit, že zvolená 4 k.ú.
na sebe skutečně navazují.)*

## Způsob spolupráce s Claude Code (dohodnuto)

- **Míra detailu rozhodování:** Claude se ptá na zdůvodnění a odsouhlasení u **každého**
  implementačního rozhodnutí, ne jen u architektury — viz `CLAUDE.md`.
- **Zápis do roadmapy:** `roadmap.md` se aktualizuje automaticky, s časovým razítkem, při každém
  programování (mém i Claude Code).
