# Průvodce kódem — co si projít před pohovorem

Tenhle dokument není součástí odevzdávané aplikace. Je to **postup, v jakém pořadí si projekt
projít**, aby ses v něm vyznal a uměl ho obhájit. Ke každé části je napsané, co se v ní děje,
proč to tak je, a **na co se tě u pohovoru nejspíš zeptají**.

Poznámka na rovinu: velkou část kódu psal Claude Code (viz `docs/roadmap.md`, kde je u každého
záznamu uvedeno kdo psal a kdo rozhodoval). Ve firmě, která sama používá Claude Max, to není nic,
co by se schovávalo. Co ale schovat nejde je, jestli kódu rozumíš — a přesně k tomu je tenhle
dokument.

---

## 0. Než začneš — spusť si to

```bash
php -S localhost:8000 -t public public/router.php
```

Otevři <http://localhost:8000> a než se podíváš do kódu, **pohraj si s tím**:

- posuň mapu, přibliž, oddal
- klikni na parcelu, pak na jinou
- oddal až na celý Jičín — musí naskočit hláška „Výřez je příliš velký"
- najdi parcelu bez uvedeného způsobu využití (je jich zhruba tři čtvrtiny)

Nejlepší způsob, jak kódu rozumět, je nejdřív vědět, co dělá.

---

## 1. Datový tok — jediná věc, kterou musíš umět nakreslit

Když si zapamatuješ jen jednu věc, tak tuhle:

```
   ČÚZK WFS (INSPIRE CPX)
          │  jednorázově, import.php
          ▼
   db/parcels.sqlite          17 256 parcel, 9,1 MB
          │  dotaz na výřez
          ▼
   PHP API  /api/parcels      GeoJSON pro aktuální bbox
          │  fetch()
          ▼
   Leaflet v prohlížeči       polygony na mapě
          │  klik → /api/parcel?ref=…
          ▼
   info panel
```

**Klíčové rozhodnutí, na které se zeptají skoro jistě:** data se stahují **předem**, ne živě.
Zadání to výslovně označuje za součást úlohy. Argument: jedna živá odpověď WFS pro střed Jičína
měla **36 MB XML** na 9 028 parcel; lokální SQLite odpoví na stejný výřez v jednotkách milisekund.
Navíc by aplikace závisela na tom, jestli ČÚZK v den pohovoru běží.

---

## 2. Pořadí, ve kterém číst soubory

Čti odzadu dopředu podle toho, jak data tečou. Nejdřív to, co je vidět, pak co to obsluhuje.

| # | soubor | řádků | co v něm hledat |
|---|---|---|---|
| 1 | `public/index.html` | ~35 | kostra — obal, mapa, panel |
| 2 | `public/style.css` | ~110 | rozvržení flexboxem |
| 3 | `public/app.js` | ~230 | **jádro klienta** — načítání, klikání, panel |
| 4 | `public/api/index.php` | ~85 | tabulka rout, kontrola vstupu |
| 5 | `src/parcels.php` | ~100 | **dva SQL dotazy + limit výřezu** |
| 6 | `src/Database.php` | ~25 | otevření databáze |
| 7 | `import.php` | ~345 | stažení a parsování GML |

Body **3 a 5** jsou nejdůležitější. Když nebudeš mít čas na všechno, projdi aspoň tyhle dva.

---

## 3. Co si u každého souboru ověřit

### `public/app.js` — klientská logika

Tři věci, které dohromady tvoří odpověď na kritérium „plynulost":

1. **`preferCanvas: true`** při vytváření mapy. Leaflet by jinak vytvořil **jeden SVG uzel na
   každou parcelu** — při tisících polygonů se prohlížeč utopí ve správě DOM. S canvasem se
   všechny nakreslí do jediného plátna. Změřeno: i všech 17 256 parcel se vykreslí za **402 ms**.
2. **Debounce 200 ms na `moveend`.** Během tažení mapy se nic nenačítá — Leaflet už vykreslené
   vrstvy jen posouvá transformací, takže *samotný pohyb je plynulý z principu*. Dotaz jde až
   po dotažení pohybu.
3. **`AbortController`.** Když popojedeš dřív, než odpověď dorazí, je odpověď pro výřez, ze
   kterého už jsi odjel — zruší se. Bez toho by se dotazy hromadily a mapa by blikla starým
   obsahem.

Další věci, které stojí za pohled:

- **`styleFor()` a `selectedRef`.** Vrstva parcel se při každém pohybu zahodí a postaví znovu,
  takže zvýraznění vybrané parcely nemůže být uložené v objektu vrstvy — drží se stranou v
  `selectedRef` a styl se z něj odvozuje. Ověřeno: po posunu mapy zůstane vybraná parcela
  zvýrazněná.
- **Panel se staví přes `textContent`, ne `innerHTML`.** Data jdou z databáze do HTML; kdyby
  se skládal řetězec, byl by to prostor pro vložení cizího kódu. `textContent` nic
  neinterpretuje.
- **`plural()`** — „1 parcela / 2–4 parcely / 5+ parcel". Detail, ale je to vidět na první pohled.

### `src/parcels.php` — kde se rozhoduje o výkonu

- **`MAX_BBOX_AREA = 0.001`.** Nad touhle plochou výřezu se místo parcel vrátí příznak
  `zoom_in`. Hodnota **není odhad** — je doladěná měřením celého řetězce v prohlížeči
  (viz tabulka v komentáři přímo u konstanty i v README). Zvolena tak, aby pauza po dotažení
  pohybu zůstala kolem 200 ms.
- **Dotaz na výřez netestuje průnik polygonů, ale překryv obdélníků.** Každá parcela má v
  databázi `min_lon`, `min_lat`, `max_lon`, `max_lat` a obyčejný B-tree index. Důvod: SQLite
  umí prostorový index R-Tree, ale ve Windows buildu PHP **zkompilovaný není** (ověřeno přes
  `PRAGMA compile_options`). Při 17 256 řádcích je to plně dostačující — dotaz na 5 632 parcel
  trvá 0,5 ms.
- **Odpověď se skládá spojováním řetězců**, ne `json_encode()` nad velkým polem. Geometrie je
  v databázi uložená už jako hotový GeoJSON, takže by ji `json_encode()` musel rozebrat a znovu
  složit.
- **`parcelDetail()` připojuje číselníky přes `LEFT JOIN`**, ne `JOIN`. Druh pozemku i způsob
  využití mohou chybět a taková parcela musí přesto jít zobrazit.

### `public/api/index.php` — routování a vstup

- **Jedna tabulka rout** (`switch` nad cestou), ne soubor na endpoint. Zadání preferuje čisté PHP;
  tohle je nejmenší věc, která splní úkol.
- **Chybný vstup vrací HTTP 400 s popisem v JSONu.** Vědomá reakce na to, čím nás celou dobu mátl
  server ČÚZK — ten na chybný dotaz vrací HTTP 200 a prázdný výsledek. Naše API to dělat nemá.
- **Výjimka jde do `error_log`, do prohlížeče jde jen „Chyba serveru."** SQL ani cesty na disku
  do odpovědi nepatří.
- **`ref` jde jako query parametr, ne jako část cesty.** Klíč parcely může obsahovat mezeru
  a tečku (`659541-st. 2344`). Ověřeno reálným dotazem na `776530-st. 96/7`.

### `import.php` — kde se ušetřila paměť

- Čte se **přímo ze zipu** přes wrapper `zip://`, nic se nerozbaluje na disk. Rozbalená data
  jsou 30× větší než zipovaná (318 MB proti 10,5 MB). Vrchol paměti celého importu: **4 MB**.
- Parsuje se **streamovaně** přes `XMLReader`, ne `SimpleXML` — ten by si celý dokument nacpal
  do paměti.
- **České popisky se stahují z registru číselníků ČÚZK**, nepíšou se ručně. Znění „Zastavěná
  plocha a nádvoří" je tak oficiální, ne náš překlad.

---

## 4. Otázky, na které měj odpověď

Seřazeno podle toho, jak pravděpodobně padnou.

**„Proč SQLite a ne Postgres/PostGIS?"**
Zadání vyžaduje spustitelnost lokálně. SQLite je soubor — recenzent nemusí instalovat a
konfigurovat databázový server. PostGIS by dal prostorové funkce, které při 17 tisících řádcích
nepotřebujeme.

**„Proč je binárka databáze v gitu?"**
Vědomý kompromis a jinak antipattern. Zde: 9,1 MB, data statická, aplikace jde spustit jedním
příkazem. Importní skript v repozitáři zůstává a je plnohodnotný, jen se nemusí spouštět.

**„Co se stane, když oddálím na celý okres?"**
Nevykreslí se nic a naskočí výzva k přiblížení. Je to rozhodnutí, ne opomenutí — jinak by jeden
posun mapy znamenal 6,75 MB a 17 256 polygonů. Alternativa, kterou jsem zvažoval a zamítl:
zjednodušování geometrie (Douglas–Peucker) — vlastní geometrický kód navíc, a hranice parcel
jsou přitom smysl celé aplikace.

**„Proč tam není vlastník?"**
Svobodná data ČÚZK (WFS/INSPIRE) jméno vlastníka neobsahují — je jen v registrovaném/placeném
přístupu do ISKN. Zadání říká „nejednoznačnosti rozhodni sám a zdůvodni", takže panel ukazuje to,
co je reálně dostupné zdarma. Není to mezera, je to hranice zdroje dat.

**„Jak jsi ověřil, že jsou souřadnice správně?"**
Vizuálně proti OSM podkladu a proti `nahlizenidokn.cuzk.cz`. Past: stejný server vrací přes
`GetFeature` pořadí lon,lat, ale v předpřipravených sadách lat,lon. Prohození nezpůsobí chybu —
mapa jen tiše vykreslí parcely v Somálsku.

**„Proč `nationalCadastralReference` jako klíč?"**
Nese kód katastrálního území i parcelní číslo, tedy přesně tu kombinaci, která dává v katastru
jedinečnost. Ověřeno na 9 028 parcelách: je jedinečná, **samotné parcelní číslo ne** (4 kolize
přes hranice k.ú.).

**„Co bys udělal s víc časem?"**
Celý okres Jičín (postup je připravený, stačí doplnit kódy do konstanty `ZONINGS`), zjednodušování
geometrie podle zoomu, vyhledávání parcely podle čísla, vektorové dlaždice, a hlavně **automatické
testy parseru** — zejména na pořadí souřadnic a parcely s dírami, tedy přesně ta místa, kde se
chyba neprojeví výjimkou, ale tichým nesmyslem na mapě.

**„Kolik ti to zabralo?"**
Viz `docs/roadmap.md` — je tam časová stopa každé session od 2026-08-03.

---

## 5. Co si zkontrolovat těsně před odevzdáním

- [ ] `git status` je čistý, všechno podstatné je commitnuté
- [ ] po `git clone` do prázdné složky jde aplikace spustit jen tím jedním příkazem
- [ ] **ruční test ve Firefoxu** — zadání vyžaduje Chrome i Firefox, v Chrome ověřeno
- [ ] `README.md` sedí se skutečným stavem kódu
- [ ] konzole prohlížeče je po načtení prázdná

---

## 6. Slabá místa — vědět o nich dřív než recenzent

Poctivější než čekat, jestli si toho všimnou:

- **Žádné automatické testy.** Vědomé, ale je to první věc, kterou by šlo vytknout. Odpověď:
  vím o tom, vím i kde by byly nejcennější (parser GML — pořadí souřadnic a díry v parcelách).
- **Vykreslování je omezené plochou výřezu, ne počtem parcel.** Kdyby přibylo hustěji zastavěné
  území, stejná plocha by znamenala víc polygonů. Limit podle počtu by byl přesnější, ale
  server by musel nejdřív spočítat, kolik jich je.
- **Jen 4 katastrální území ze 4 povinných.** Splňuje minimum, ne bonus. Vědomá volba: čas šel
  do plynulosti, kterou zadání zmiňuje dvakrát, místo do rozsahu, který je označený za nepovinný.
- **Frontend jsem nepsal celý sám.** Když padne dotaz, odpověď je ta pravdivá: rozhodnutí jsou
  moje a zdůvodněná v `docs/roadmap.md`, kód psal z velké části Claude Code a prošel jsem si ho
  podle tohohle dokumentu.
