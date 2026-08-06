# Rozhodnutí a proč

Zadání hodnotí „postup k práci, rozhodování a proč". Tenhle dokument je odpověď na to —
**tematicky, ne chronologicky**. U každého rozhodnutí je čtveřice: co se rozhodlo, jaké byly
alternativy, proč padla tahle, a co to stálo.

Chronologický záznam téhož, se zápisem každé session a časovou stopou, je v
[`roadmap.md`](roadmap.md). Ten je podklad, tenhle dokument je shrnutí.

**Obsah:** [Data](#1-data-a-zdroj) · [Úložiště](#2-úložiště) · [Import](#3-import) ·
[API](#4-api) · [Klient](#5-klient) · [Výkon](#6-výkon) · [Hranice](#7-vědomé-hranice) ·
[Konvence](#8-konvence)

---

## 1. Data a zdroj

### Data se stahují jednorázově, nedotazuje se živě
Zadání označuje právě tuhle volbu za součást úlohy.

- **Zamítnuto:** živý WFS dotaz při každém pohybu mapy.
- **Proč:** naměřeno — jedna odpověď WFS pro střed Jičína měla **36 MB XML** na 9 028 parcel.
  To je v přímém rozporu s požadavkem na plynulost a navíc by aplikace závisela na tom, jestli
  ČÚZK v den pohovoru běží. Lokální SQLite odpoví na stejný výřez v jednotkách milisekund.
- **Cena:** data jsou statická, k datu importu. Pro tuhle úlohu nevadí — katastr se nemění
  po hodinách.

### CPX místo základní CP
Použit WFS INSPIRE **Cadastral Parcels Extended**, uložený dotaz `GetSpatialDataSet`,
po katastrálních územích, zipovaně, v EPSG:4258.

- **Zamítnuto:** základní služba CP.
- **Proč:** ověřeno přes `DescribeFeatureType`, že **jen** rozšířená varianta obsahuje
  `landType` a `landUse` — druh pozemku a způsob využití. Bez nich by info panel neměl co
  zobrazit. CPX je nadmnožina CP se stejným bezplatným přístupem.
- **Cena:** žádná reálná.

### EPSG:4258 (ETRS89) se používá přímo jako WGS84
- **Zamítnuto:** stáhnout S-JTSK (EPSG:5514) a přepočítat Křovákovo zobrazení vlastními silami.
- **Proč:** předpřipravené sady jsou jen v 5514 nebo 4258. Rozdíl mezi ETRS89 a WGS84 je ve
  střední Evropě zhruba půl metru až metr — obě soustavy byly totožné v roce 1989 a evropská
  deska se posouvá ~2,5 cm/rok. To je pod přesností samotných katastrálních dat. Vlastní
  přepočet by znamenal desítky řádků trigonometrie nebo cizí závislost, s reálným rizikem
  tiché chyby, výměnou za submetrový zisk, který nikdo nepozná.
- **Cena:** submetrová odchylka, na mapě neviditelná.

### Rozsah: 4 katastrální území
Jičín (659541), Popovice u Jičína (725838), Robousy (740225), Valdice (776530).
Dohromady ~24,4 km² a 17 256 parcel.

- **Proč tahle čtveřice:** Jičín je povinný. Popovice a Robousy s ním přímo sousedí a jsou
  součástí obce Jičín. Valdice jsou **samostatná obec** — schválně, aby se ověřilo, že aplikace
  zvládne i skutečně nezávislé katastrální území, ne jen části jednoho města.
- **Ověřeno nad geometrií:** obalové obdélníky všech tří sousedů se s Jičínem překrývají
  (vzdálenost 0 m), takže tvoří souvislý celek.
- **Cena:** pokrytí celého okresu je v zadání bonus, ne povinnost — vědomě odloženo, viz
  [Hranice](#7-vědomé-hranice).

### České popisky se stahují, nepíšou se ručně
Druhy pozemků a způsoby využití se při importu tahají z registru číselníků ČÚZK.

- **Zamítnuto:** napsat překlady ručně do konstanty.
- **Proč:** znění „Zastavěná plocha a nádvoří" je pak **oficiální**, ne náš překlad. Ručně
  psaný číselník navíc tiše zastarává.
- **Cena:** import závisí na dalším koncovém bodu ČÚZK.

---

## 2. Úložiště

### SQLite, ne Postgres/PostGIS
- **Zamítnuto:** Postgres s PostGIS.
- **Proč:** zadání vyžaduje spustitelnost lokálně. SQLite je **soubor** — recenzent nemusí nic
  instalovat ani konfigurovat. Prostorové funkce PostGIS bychom při 17 tisících řádcích stejně
  nevyužili.
- **Cena:** žádné prostorové dotazy zdarma, viz obalové obdélníky níže.

### Hotová databáze je v gitu
- **Proč:** binární soubor v gitu je jinak antipattern. Tady má 9,1 MB, data jsou statická
  a přínos je konkrétní — aplikace se spustí jedním příkazem, bez stahování a bez závislosti
  na dostupnosti ČÚZK. Importní skript v repozitáři zůstává a je plnohodnotný, jen ho není
  nutné spouštět.
- **Cena:** velikost repozitáře a binárka v historii. Vědomý kompromis.

### Prostorový index nahrazen čtyřmi sloupci
Každá parcela má `min_lon`, `min_lat`, `max_lon`, `max_lat` a obyčejný B-tree index.

- **Zamítnuto:** R-Tree index SQLite.
- **Proč:** R-Tree **ve Windows buildu PHP zkompilovaný není** (ověřeno přes
  `PRAGMA compile_options`). Test překryvu obdélníků je při 17 256 řádcích plně dostačující —
  dotaz na 5 632 parcel trvá **0,5 ms**. R-Tree se vyplatí až o dva řády výš.
- **Cena:** dotaz vrací parcely, jejichž *obalový obdélník* zasahuje do výřezu, ne jejichž
  *tvar* ho protíná. Pár parcel navíc na okraji — na mapě nepoznatelné.

### Geometrie uložená jako hotový GeoJSON
- **Zamítnuto:** ukládat GML nebo vlastní binární formát a převádět při dotazu.
- **Proč:** převod proběhne jednou při importu, ne při každém požadavku. Mapový endpoint pak
  jen skládá řetězce.
- **Cena:** v databázi je prezentační formát a soubor je větší, než by musel být.

### Klíč parcely je `nationalCadastralReference`
Například `659541-1185/3`, u stavebních `659541-st. 3047`.

- **Zamítnuto:** samotné parcelní číslo, nebo umělé auto-increment ID.
- **Proč:** nese v sobě kód katastrálního území i parcelní číslo, tedy přesně tu kombinaci,
  která dává v katastru jedinečnost. Ověřeno na 9 028 parcelách: `nationalCadastralReference`
  je jedinečná, **samotné parcelní číslo ne** (4 kolize přes hranice k.ú.). Navíc je
  dohledatelná v `nahlizenidokn.cuzk.cz`, takže obsah API jde ověřit proti oficiálnímu zdroji.
- **Cena:** obsahuje mezeru a tečku, což si vyžádalo rozhodnutí u API (viz níže).

---

## 3. Import

### Čte se přímo ze zipu, nic se nerozbaluje
- **Proč:** rozbalená data jsou **30× větší** než zipovaná — 318 MB proti 10,5 MB. Přes wrapper
  `zip://` se čte rovnou z archivu.
- **Cena:** žádná.

### Streamované parsování přes `XMLReader`
- **Zamítnuto:** `SimpleXML` nebo `DOMDocument`.
- **Proč:** ty by si celý dokument nacpaly do paměti. Rozbalený Jičín má 239 MB. Takto má
  **celý import vrchol paměti 4 MB**.
- **Cena:** kód je upovídanější než `SimpleXML`.

### Jméno souboru se čte z archivu, neodhaduje
- **Proč:** soubor uvnitř archivu se jmenuje `.xml`, ne `.gml`, přestože obsahuje GML. Kód
  hledající `*.gml` by nenašel nic.

### Číselníky se spojují přes `basename()`, ne přes celou URL
- **Proč:** registr číselníků vydává identifikátory na `services.cuzk.gov.cz`, zatímco GML
  odkazuje na `services.cuzk.cz`. Spojení podle celé URL by nenašlo ani jednu shodu.

---

## 4. API

### Čisté PHP, jedna tabulka rout
- **Zamítnuto:** framework (Laravel/Symfony), i vlastní soubor na endpoint.
- **Proč:** zadání preferuje čisté PHP. Tři endpointy nepotřebují vrstvu navíc ani abstrakci
  pro jediného volajícího.
- **Cena:** vestavěný server neumí sám poslat požadavek na neexistující cestu, proto se spouští
  se směrovačem `public/router.php`.

### Chybný vstup vrací HTTP 400 s popisem, ne prázdný výsledek
- **Proč:** tohle je přímá reakce na to, čím nás server ČÚZK mátl celou dobu — na chybný dotaz
  odpovídá **HTTP 200 a prázdným výsledkem**. Stalo se to pětkrát a stálo to nejvíc času ze
  všeho. Naše API to dělat nemá: prázdná odpověď u nás nikdy neznamená chybu.
- **Cena:** víc kontrol vstupu v kódu.

### Výjimka jde do `error_log`, do prohlížeče jen „Chyba serveru."
- **Proč:** SQL dotazy ani cesty na disku do odpovědi nepatří.

### `ref` jako query parametr, ne jako část cesty
`/api/parcel?ref=659541-st.%2096/7`

- **Zamítnuto:** `/api/parcel/659541-st. 96/7`.
- **Proč:** klíč obsahuje mezeru, tečku i lomítko — v cestě by se musel dvakrát enkódovat
  a rozbíjel by routování. Ověřeno reálným dotazem, že `776530-st. 96/7` projde celou cestou
  od URL po JSON.

### `bbox` v pořadí lon,lat
- **Proč:** shodné s GeoJSON i s `toBBoxString()` v Leafletu, takže **klient nemusí nic
  přehazovat**. Prohození pořadí je u těchhle dat nejčastější tichá chyba — mapa se nerozbije,
  jen vykreslí parcely někde v Somálsku.

### Mapový endpoint vrací jen geometrii a `ref`, `properties` zůstávají prázdné
- **Proč:** popisky se dotahují až kliknutím. Drží to nejčastější dotaz mimo JOIN na číselníky
  a nenafukuje odpověď textem, který uživatel čte vždy jen pro jednu parcelu.

### Odpověď se skládá spojováním řetězců, ne `json_encode()`
- **Proč:** geometrie je v databázi už jako hotový GeoJSON, takže by ji `json_encode()` musel
  nejdřív rozebrat a znovu složit.
- **Cena:** kód, který skládá JSON ručně, se musí psát opatrně.

---

## 5. Klient

### Vanilla JS + Leaflet, bez frameworku a bez build kroku
- **Zamítnuto:** Next.js (zvažován vážně).
- **Proč:** Next.js je framework nad Reactem běžící na Node.js — k PHP backendu by přibyl druhý
  server a s ním CORS nebo proxy, README by začínalo instalací Node a `npm install` (přesně ta
  závislost, kvůli které je v repozitáři hotová databáze), a Leaflet se s Reactem pere
  o vlastnictví DOM. Klientská část má přitom **247 řádků** — rozsah, kde framework nemá co
  spravovat.
- **Cena:** žádná abstrakce, kdyby aplikace výrazně narostla.

### Leaflet stažený v repozitáři, ne z CDN
- **Proč:** stejný argument jako u databáze — spustit jedním příkazem a nezáviset na cizím
  serveru. Verze 1.9.4, poslední stabilní 1.x (2.0 je v beta a distribuuje se jen jako ES moduly).
- **Cena:** 160 kB cizího kódu v gitu.

### Mapový podklad OpenStreetMap
- **Zamítnuto:** Mapy.cz, Mapbox.
- **Proč:** zdarma, bez API klíče, bez rizika, že v den pohovoru dojde limit.

### Info panel vedle mapy, ne bublina nad parcelou
- **Zamítnuto:** Leaflet popup.
- **Proč:** bublina by překryla právě tu parcelu, na kterou uživatel klikl, u delších popisků
  („Zastavěná plocha a nádvoří") by se roztáhla přes kus mapy, a zavírala by se při pohybu mapy.
- **Cena:** rozvržení flexboxem a ošetření stavu „nic není vybráno".

### Panel se staví přes `textContent`, ne `innerHTML`
- **Proč:** data jdou z databáze do HTML. Skládaný řetězec by byl prostor pro vložení cizího
  kódu; `textContent` nic neinterpretuje.

### Zvýraznění vybrané parcely se drží mimo vrstvu
Klíč je v proměnné `selectedRef`, styl se z něj odvozuje funkcí `styleFor()`.

- **Proč:** vrstva parcel se při každém pohybu zahodí a postaví znovu, takže si zvýraznění
  nemůže pamatovat sama. Díky tomuhle zůstane vybraná parcela zvýrazněná i po posunu mapy.

### Chybějící údaj se vypíše jako „neuvedeno"
- **Zamítnuto:** vynechat řádek.
- **Proč:** způsob využití chybí zhruba u tří čtvrtin parcel. Prázdné místo v panelu by
  vypadalo jako chyba aplikace; „neuvedeno" říká, že se to neví — a že to víme.
- **Detail:** testuje se i prázdný řetězec, ne jen `null` — registr ČÚZK u části položek vrací
  definici jako prázdný řetězec.

---

## 6. Výkon

Zadání zmiňuje plynulost dvakrát, takže sem šel čas přednostně před rozsahem pokrytí.

### Leaflet kreslí do canvasu, ne do SVG
`preferCanvas: true`

- **Proč:** ve výchozím nastavení vytvoří Leaflet **jeden SVG uzel na každou parcelu** — při
  tisících polygonů se prohlížeč utopí ve správě DOM. S canvasem jde všechno do jediného plátna:
  vykreslení všech 17 256 parcel trvá **402 ms**.
- **Cena:** jednotlivé polygony nejdou stylovat přes CSS, styl se nastavuje jen z JavaScriptu.
  Pro dva styly (běžná / vybraná parcela) nevadí.

### Načítání na `moveend` s prodlevou 200 ms, bez cache
- **Zamítnuto:** cache už načtených parcel; načítání většího výřezu, než je vidět.
- **Proč:** během tažení mapy se nenačítá nic — Leaflet už vykreslené vrstvy jen posouvá
  transformací, takže **pohyb je plynulý z principu** a řeší se až pauza po něm. Cache by
  pomohla jen u malého posunu do už viděné oblasti; drahý případ (oddálení, skok jinam) je
  celý z nových dat, kde cache nemá co nabídnout — zaplatila by se složitost a stejně by se
  stálo.
- **Cena:** malý posun stáhne i to, co už na mapě bylo.

### Rozdělaný dotaz se ruší
`AbortController`

- **Proč:** když uživatel popojede dřív, než odpověď dorazí, je odpověď pro výřez, ze kterého
  už odjel. Bez zrušení by se dotazy hromadily a mapa by blikla starým obsahem.

### Nad velkým výřezem se parcely nevykreslují
`MAX_BBOX_AREA = 0.001` ve `src/parcels.php`; API vrátí `"zoom_in": true` a prázdný seznam.

- **Zamítnuto:** posílat všechno; zjednodušovat geometrii podle zoomu (Douglas–Peucker).
- **Proč:** hodnota **není odhad** — je doladěná měřením celého řetězce v prohlížeči, tedy
  stažení + `JSON.parse` + vykreslení:

  | plocha | šířka | parcel | odpověď | celkem |
  |---|---|---|---|---|
  | 2,0 × 10⁻⁴ | 1,3 km | 1 331 | 0,55 MB | 46 ms |
  | 4,0 × 10⁻⁴ | 1,8 km | 2 523 | 1,05 MB | 85 ms |
  | **1,0 × 10⁻³** | **2,9 km** | **5 669** | **2,36 MB** | **209 ms** ← zvoleno |
  | 2,0 × 10⁻³ | 4,1 km | 11 121 | 4,58 MB | 350 ms |
  | 1,35 × 10⁻² | 10,7 km | 17 256 | 6,75 MB | 559 ms |

  Pauza kolem 200 ms po dotažení pohybu ještě splyne s pohybem samotným, půl sekundy už je
  vidět. Měřeno na rychlém stroji, takže je v hodnotě schválně rezerva.

  Douglas–Peucker byl zamítnut proto, že je to vlastní geometrický kód navíc — a hranice
  parcel jsou přitom smysl celé aplikace. Radši méně parcel přesně než všechny nepřesně.
- **Cena:** při oddálení nad celý rozsah uživatel parcely nevidí a musí přiblížit.

### Limit je na ploše výřezu, ne na počtu parcel
- **Proč:** plocha je známá **před** dotazem, počet až po něm. Díky tomu se limit sám
  přizpůsobí velikosti okna — na velkém monitoru je stejný zoom větším výřezem.
- **Cena:** hustěji zastavěné území by při stejné ploše znamenalo víc polygonů. Limit podle
  počtu by byl přesnější, ale server by ho musel nejdřív spočítat.

---

## 7. Vědomé hranice

Co v aplikaci **není**, a proč to není opomenutí.

### Vlastník parcely
Svobodná data ČÚZK (WFS/INSPIRE) jméno vlastníka neobsahují — je jen v registrovaném/placeném
přístupu do ISKN. Zadání říká „zobrazit informace o parcele" a zároveň „nejednoznačnosti rozhodni
sám a zdůvodni". Rozhodnutí: panel ukazuje to, co je reálně dostupné zdarma. **Není to mezera,
je to hranice zdroje dat.**

### Celý okres Jičín
Zadání to označuje za bonus, ne povinnost; povinné minimum jsou 4 katastrální území a ta jsou
splněná. Čas šel přednostně do plynulosti, kterou zadání zmiňuje dvakrát. Postup je připravený —
stačí doplnit kódy do konstanty `ZONINGS`, import je na počtu území nezávislý.

### Automatické testy
Nejsou. Je to první věc, kterou by šlo vytknout, a vím i kde by byly nejcennější: **parser GML**,
konkrétně pořadí souřadnic a parcely s dírami (`gml:interior`, má je 647 parcel v Jičíně, ~10 %).
To jsou přesně místa, kde se chyba neprojeví výjimkou, ale tichým nesmyslem na mapě.

### Vyhledávání parcely podle čísla
Není. ČÚZK na to má uložené dotazy `GetParcel` a `GetNeighbourParcels` — připravená cesta,
kdyby byl čas.

---

## 8. Konvence

### Identifikátory anglicky, souvislý text česky
Proměnné, funkce, tabulky a sloupce anglicky; komentáře, texty v UI a dokumentace česky.

- **Proč:** čeština je flektivní a identifikátory se neskloňují — `$parcely` je zároveň nominativ
  plurálu i genitiv singuláru, kdežto `$parcels` / `$parcel` tuhle dvojznačnost nemá. Anglické
  názvy sloupců navíc mapují 1:1 na zdrojová pole INSPIRE (`cp-ext:landType` → `land_type`),
  takže nejde o překlad, ale o převzetí názvosloví zdroje.
- **Hranice** nevede napříč projektem náhodně, ale mezi kódem a souvislým textem.
