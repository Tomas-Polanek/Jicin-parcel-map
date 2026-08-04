# Roadmap / průběžný log

Tento soubor vede Claude Code automaticky. Účel: dohledatelný záznam toho, kdy a kdo psal/měnil
kód, jaká rozhodnutí padla a proč — pro potřeby zápisníku v `plan.md`/README a pro to, aby bylo
kdykoliv vidět, v jakém stavu projekt je.

## Pravidla zápisu (viz `CLAUDE.md`)

- Nový záznam vzniká automaticky při **každé** programovací session (moje i Tomášovy), bez nutnosti
  o to žádat.
- Každý záznam obsahuje: časové razítko, kdo psal kód, co se dělo, jaká rozhodnutí byla po cestě
  udělána a proč (u každého, i drobného implementačního rozhodnutí — ne jen architektura).
- Žádný záznam se nemaže ani nepřepisuje zpětně — pokud se rozhodnutí později změní, přidává se
  nový záznam s odkazem na ten starý, ne úprava historie.

## Formát záznamu

```
## [YYYY-MM-DD HH:MM] – [Claude / Tomáš]
**Co se dělo:** ...
**Rozhodnutí a proč:**
- rozhodnutí 1 — zdůvodnění
- rozhodnutí 2 — zdůvodnění
**Stav repozitáře:** spustitelný / nespustitelný, proč
```

---

## [2026-08-03 20:38] – Claude

**Co se dělo:** Přečteno zadání (`Interview_uloha_Programator.pdf`), sestaven `plan.md` rozdělený
podle jednotlivých cílů ze zadání. Založena spolupráce s pravidly řízení rozhodnutí a logování.
Kód aplikace zatím nebyl psán.

**Rozhodnutí a proč:**
- Plán strukturovat podle explicitních cílů ze zadání (ne volně) — aby šlo snadno ověřit, že nic
  z požadavků nechybí, a aby šlo zadání použít jako checklist při finální kontrole.
- Data stahovat jednorázově a ukládat lokálně (SQLite), místo živých WFS dotazů — kvůli požadavku
  na plynulost a kvůli riziku výpadku cizí služby v den pohovoru.
- Backend v čistém PHP bez frameworku, kvůli výslovné preferenci v zadání a hodnocenému kritériu
  kvality PHP kódu.
- Vlastnická data (jméno vlastníka parcely) nebudou v info panelu — nejsou v otevřených ČÚZK datech
  zdarma; rozhodnutí zapsáno teď, ne objeveno pozdě při psaní kódu.
- Rozsah zůstane na povinném minimu (Jičín + 3 území), ušetřený čas půjde do výkonu/plynulosti,
  protože to zadání zmiňuje jako klíčové kritérium dvakrát.
- Dohodnut způsob spolupráce: Claude se ptá na zdůvodnění u každého implementačního rozhodnutí
  (ne jen architektonického) a `roadmap.md` se aktualizuje automaticky při každé session.

**Stav repozitáře:** Zatím žádný kód, jen plánovací soubory (`plan.md`, `roadmap.md`, `CLAUDE.md`).
Git repozitář ještě nezaložen.

---

## [2026-08-03 20:58] – Claude

**Co se dělo:** Dořešeny oba zbývající otevřené body z `plan.md` — konkrétní WFS endpoint a výběr
3 dalších katastrálních území. Ověřeno reálným dotazem na ČÚZK (`DescribeFeatureType`), ne jen
odhadem. Kód aplikace stále nebyl psán.

**Rozhodnutí a proč:**
- Zvolen WFS **INSPIRE Cadastral Parcels Extended (CPX)**, ne základní CP varianta —
  `services.cuzk.gov.cz/wfs/inspire-cpx-wfs.asp`, feature type `CadastralParcel`. Ověřeno přímým
  dotazem na `DescribeFeatureType`, že CPX na rozdíl od základní CP obsahuje atribut
  `landType`/`hilucsLandType` (druh pozemku) — bez něj by info panel nemohl splnit, co si `plan.md`
  už dřív předsevzal (cíl #11). CPX je nadmnožina CP se stejným bezplatným přístupem, takže žádná
  reálná nevýhoda.
- Potvrzeny 3 doplňkové katastrální území k Jičínu: **Popovice u Jičína**, **Robousy**, **Valdice**.
  Kritérium výběru: přímá sousednost s Jičínem (souvislé území pro mapu, ne roztroušené ostrůvky) a
  udržení plochy přiměřeně malé (~24 km² celkem), aby zbyl čas na výkonovou práci (cíl #19 má v
  hodnocení prioritu před rozšiřováním rozsahu, cíl #7 je bonus, ne povinnost).
- Valdice vybrána vědomě jako jediné ze tří skutečně samostatné katastrální území (Popovice a
  Robousy jsou správně součástí obce Jičín) — otestuje se tak reálné zvládnutí více nezávislých k.ú.
  najednou, ne jen podčástí jednoho města.
- Vedlejší zjištění při ověřování: firma MASO Jičín (Konecchlumského 1075/1146) sídlí ve Valdickém
  Předměstí, což je čtvrť spadající do k.ú. Jičín (mandatorní území) na straně směrem k Valdicím —
  čtvrť je po Valdicích historicky pojmenovaná a leží jim nejblíž. Nemění to výběr území, jen
  potvrzuje, že zvolená čtveřice bude na mapě smysluplně souvislá i v tomto konkrétním bodě.
  Zjištěno z veřejných obchodních rejstříků a Wikipedie, ne z přesného parcelního dotazu — přesná
  poloha se ověří přirozeně při reálném stažení dat.

**Stav repozitáře:** Stále jen plánovací soubory, žádný aplikační kód. Git repozitář zatím
nezaložen.

---

## [2026-08-03 21:44] – Tomáš (za asistence Claude)

**Co se dělo:** Nainstalováno PHP 8.5.9 (NTS, x64) do `C:\php`, přidáno do uživatelské PATH.
Chybějící `php.ini` byl vytvořen z `php.ini-development` šablony a zapnuty extenze `pdo_sqlite`
a `sqlite3` (výchozí instalace je bez `php.ini` neměla vůbec žádné rozšíření aktivní). Ověřeno
přes `php -m`, obě rozšíření se načítají. Kód aplikace stále nebyl psán — jde o přípravu prostředí.

**Rozhodnutí a proč:**
- PHP verze: nejnovější stabilní **8.5.9** místo dříve zvažované 8.3 — v `plan.md` ani v zadání
  není žádný důvod držet se starší verze, takže nejnovější stabilní je jednodušší volba a
  odpovídá tomu, co by pravděpodobně mělo i recenzentovo prostředí.
- Build **Non Thread Safe (NTS)** — správná volba, protože PHP poběží jen jako CLI / vestavěný
  dev server (`php -S`), nikdy jako Apache modul, kde by byl nutný Thread Safe build.
- `php.ini` odvozen z `php.ini-development` (ne `php.ini-production`) — vhodnější pro lokální
  vývoj (např. zobrazuje chyby na obrazovce), což odpovídá tomu, že aplikace se podle zadání
  spouští jen lokálně, ne jako nasazená produkce.

**Stav repozitáře:** Stále jen plánovací soubory, žádný aplikační kód. Git repozitář zatím
nezaložen. PHP prostředí je teď funkční a připravené na start implementace.

---

## [2026-08-03 21:49] – Tomáš

**Co se dělo:** Tomáš založil GitHub repozitář na vlastní účet (podle Pravidla 5 v `CLAUDE.md`
Claude repozitáře nezakládá sám). Název `Jicin-parcel-map`, dřív navržený Claude jako alternativa
k původním nápadům ("Web-app-roadmap" / "RoadMap Jicin"), které by kolidovaly s významem
`roadmap.md` v tomto projektu. URL zapsáno do `plan.md`.

**Rozhodnutí a proč:**
- Repozitář: https://github.com/Tomas-Polanek/Jicin-parcel-map — jméno popisuje, co appka dělá
  (mapa parcel v Jičíně), ne proces práce na ní, aby název nepletl s `roadmap.md`.

**Stav repozitáře:** Na GitHubu založen prázdný repozitář. Lokálně v `C:\Users\PC\Desktop\Interview
Uloha` zatím není `git init`/napojení na remote — čeká se na rozhodnutí, kdo to udělá (viz níže).

---

## [2026-08-03 22:10] – Claude

**Co se dělo:** Tomáš odsouhlasil, ať Claude udělá lokální `git init` a napojení na remote (podle
Pravidla 5 se Claude nejdřív zeptal) — provedeno, žádný commit zatím neproběhl. Tomáš zároveň
potvrdil, že `CLAUDE.md`/`plan.md`/`roadmap.md` zůstávají v repozitáři i přesto, že bude veřejný —
uloženo i jako trvalá poznámka do paměti Claude (mimo tento projekt), protože jde o relevantní
kontext i pro budoucí konverzace. Následně vyřešeno poslední otevřené téma z `plan.md`: přepočet
souřadnicového systému.

**Rozhodnutí a proč:**
- `git init` + `git remote add origin` provedl Claude na základě výslovného svolení. Žádný soubor
  zatím není stagovaný ani commitnutý.
- Soubory `CLAUDE.md`/`plan.md`/`roadmap.md` zůstávají v repozitáři beze změny, i když bude
  veřejný — Tomáš je otevřeně sdělil, že použití Claude je v pořádku, a firma sama interně
  pracuje v Claude Max, takže transparentnost je tu výhoda, ne riziko. Před finálním odevzdáním
  vznikne navíc krátká, přehledná verze zápisníku přímo v README (viz předchozí diskuze).
- Souřadnicový systém: potvrzena **možnost A** — WFS dotaz na ČÚZK CPX bude rovnou žádat data v
  `srsName=EPSG:4326` (WGS84), aby se nemusel dělat ruční přepočet z S-JTSK na naší straně. Zapsáno
  do `plan.md`. Ověření, že to server podporuje, proběhne při reálném stažení dat.

**Stav repozitáře:** Lokální `.git` založen a napojen na GitHub remote, nic není commitnuté.
Aplikační kód stále nebyl psán.

---

## [2026-08-03 22:22] – Claude (na výslovné svolení Tomáše, viz Pravidlo 5)

**Co se dělo:** První commit repozitáře — `CLAUDE.md`, `plan.md`, `roadmap.md`,
`.claude/settings.json`. Commit message navržen Claude (nejdřív delší verze, na žádost Tomáše
zkrácen), odsouhlasen před spuštěním. `Interview_uloha_Programator.pdf` vědomě necommitnut.

**Rozhodnutí a proč:**
- `Interview_uloha_Programator.pdf` zůstává mimo git (necommitovaný, netracked) — je to interní
  zadávací materiál Viagem, veřejné repo by ho jinak přeposílalo dál bez svolení. Vše podstatné z
  něj je stejně přepsané do `plan.md`, takže se nic neztrácí.
- Commit message zkrácen na jeden řádek na žádost Tomáše — delší verze s odstavcem odůvodnění byla
  zbytečně těžkopádná pro první, čistě přípravný commit.

**Stav repozitáře:** `main` má první commit (`b8d8b37`), obsahuje plán, log a nastavení
spolupráce. Aplikační kód stále nebyl psán. `Interview_uloha_Programator.pdf` zůstává netracked.

---

## [2026-08-04 12:43] – Claude

**Co se dělo:** Ověřování WFS endpointu reálnými dotazy (žádný aplikační kód, viz Pravidlo 4).
Potvrzeno, opraveno nebo zjištěno šest věcí, které mění nebo upřesňují to, co `plan.md`
předpokládal:

1. **`srsName=EPSG:4326` funguje** — možnost A z `plan.md` je potvrzená, ruční přepočet z S-JTSK
   nebude potřeba. `GetCapabilities` uvádí podporu 5514, 4326, 4258, 3857 a dalších.
2. **Feature type musí být s prefixem `cp-ext:CadastralParcel`**, ne holé `CadastralParcel`.
   Bez prefixu server vrátí HTTP 200 a `numberMatched="0"` — tedy tiše nic, ne chybu.
3. **Pořadí os v `bbox` je lon,lat** (`minx,miny,maxx,maxy`), i když EPSG:4326 formálně definuje
   lat,lon. Se správným pořadím lat,lon vrátí server prázdný výsledek bez chyby.
4. **Výstupní geometrie je také v pořadí lon,lat** — pro GeoJSON (který má lon,lat) to znamená
   přímé přepsání bez prohazování, pro Leaflet (který má lat,lon) prohození.
5. **Limit počtu prvků není v praxi překážka** — dotaz na střed Jičína vrátil 9 028 parcel
   najednou bez oříznutí. Ale odpověď měla 36 MB XML, tj. ~4 KB na parcelu, což je nezávisle
   další argument pro rozhodnutí z `plan.md` data předem stáhnout a neposílat WFS naživo.
6. **Atributy reálně dostupné na parcele** (ověřeno na skutečných datech, ne ze schématu):
   `cp:label` (parcelní číslo, u stavebních s prefixem `st. `), `cp:areaValue` (výměra v m²),
   `cp:nationalCadastralReference` (kód k.ú. + parcelní číslo, např. `659541-1185/3`),
   `cp:zoning` (název k.ú. + jeho kód), `cp-ext:landType` a `cp-ext:landUse` (druh pozemku a
   způsob využití, ale jen jako anglické URL do číselníku, např. `.../LandTypeValue/BuiltUpArea`),
   `gml:id` (např. `CPX.2526318604`), `cp:beginLifespanVersion`, a u zastavěných parcel odkaz
   `cp-ext:building` na budovu.

**Rozhodnutí a proč:** Žádné implementační rozhodnutí zatím nepadlo — tato session byla čistě
ověřovací. Zjištění ale otevřela tři nové body k rozhodnutí (unikátní klíč parcely, způsob
stahování, převod číselníků do češtiny), které jsou sepsané níže v "Co dál".

**Stav repozitáře:** Beze změny — pouze plánovací soubory, žádný aplikační kód. Testovací XML
odpovědi zůstaly mimo repozitář (dočasná složka), do gitu se nepřidávají.

---

## [2026-08-04 13:05] – Tomáš (rozhodnutí) / Claude (ověření a zápis)

**Co se dělo:** Uzavřen otevřený bod z `plan.md` — unikátní klíč parcely v SQLite. Před
rozhodnutím ověřena jedinečnost obou kandidátů na reálném vzorku 9 028 parcel staženém z CPX,
ne odhadem. Aplikační kód stále nepsán.

**Rozhodnutí a proč:**
- **Primární klíč parcely = `cp:nationalCadastralReference`** (např. `659541-1185/3`,
  u stavebních `659541-st. 3047`). Tomáš vybral z nabídnutých tří možností tuto.
  Důvody: (a) řetězec už v sobě nese přesně tu trojici k.ú. + parcelní číslo + typ parcely,
  kterou `plan.md` označil za podmínku jedinečnosti — nemusíme si klíč skládat sami;
  (b) je to identifikátor domény (katastru), ne artefakt INSPIRE exportu, takže je odolnější
  vůči přegenerování dat na straně ČÚZK než `gml:id`; (c) je dohledatelný v
  `nahlizenidokn.cuzk.cz`, což zadání výslovně jmenuje — recenzent si obsah API může proti
  němu ověřit, u `CPX.2526318604` by neměl jak.
- **Zamítnuta varianta „gml:id jako PK + UNIQUE index na reference"** — technicky nejrobustnější,
  ale pro projekt se čtyřmi k.ú. a jednorázovým importem jde o předčasnou abstrakci; držet dvě
  identity téhož objektu by se hůř obhajovalo proti kritériu #20 (kvalita a čitelnost kódu).
- Ověřeno na datech: 9 028 parcel = 9 028 unikátních `nationalCadastralReference` i 9 028
  unikátních `gml:id`, ale jen 9 024 unikátních samotných parcelních čísel (4 kolize přes
  hranice k.ú.). Empirické potvrzení, že klíč bez kódu k.ú. by byl chybný.
- `ku_kod`, `ku_nazev` a parcelní číslo se do tabulky uloží i jako samostatné sloupce —
  nezávisle na volbě klíče, kvůli filtrování podle k.ú. a zobrazení v info panelu.
- Zaznamenaný důsledek do budoucna: `st. 2344` obsahuje mezeru a tečku, takže při návrhu detail
  endpointu bude nutné řešit enkódování v URL. Je to samostatné rozhodnutí, neřeší se teď.

**Vedlejší zjištění:** testovací bbox zasáhl i k.ú. Moravčice a Podhradí u Jičína, která nejsou
mezi zvolenými čtyřmi. Potvrzuje, že stahování podle bbox natáhne i sousední území a bude nutné
filtrování — vstup do rozhodnutí o způsobu stahování.

**Stav repozitáře:** Beze změny, žádný aplikační kód. Necommitnuté zůstávají poslední záznamy
v `roadmap.md`.

---

## [2026-08-04 13:20] – Tomáš (rozhodnutí) / Claude (ověření a zápis)

**Co se dělo:** Prozkoumány schopnosti CPX serveru přes `ListStoredQueries` a
`DescribeStoredQueries` — dřív jsme předpokládali, že jedinou cestou k datům je opakovaný dotaz
`GetFeature` s `bbox`. Ukázalo se, že server nabízí uložené dotazy, mimo jiné `GetSpatialDataSet`,
který vydává **předpřipravené GML soubory rozdělené po katastrálních územích**, tedy přesně po té
jednotce, se kterou pracuje náš plán. Změřeno reálným stažením všech čtyř území. Aplikační kód
stále nepsán.

**Rozhodnutí a proč:**
- **Data se budou stahovat přes `GetSpatialDataSet`, jeden dotaz na katastrální území**, ne
  dlaždicováním podle bbox, jak `plan.md` původně předpokládal. Tomáš vybral z nabídnutých tří
  variant tuto. Důvody:
  (a) Odpadá celá třída logiky, kterou by bbox varianta vyžadovala — dlaždicování, detekce
      oříznuté odpovědi, deduplikace parcel na hranách dlaždic a odfiltrování cizích k.ú.
      (že se cizí k.ú. do bbox výřezu opravdu připletou, bylo ověřeno dřív — Moravčice, Podhradí).
  (b) Hranice území určuje sám ČÚZK, ne náš obdélník — nemůže se stát, že by na okraji území
      nějaká parcela vypadla.
  (c) Naměřený objem: Jičín 7,5 MB, Popovice 0,7 MB, Robousy 1,3 MB, Valdice ~1 MB v zipu,
      dohromady ~10 MB a pod 2 sekundy. Soubory jsou na straně ČÚZK předgenerované a cachované
      (hlavička uvádí datum sestavení 30. 7. 2026), takže stahování je rychlé a nezatěžuje server.
- **Vědomě přijatá nevýhoda č. 1:** předpřipravený soubor obsahuje všechny typy prvků, ne jen
  parcely — u Valdic 8 511 prvků, z toho jen 1 053 parcel (zbytek jsou hranice, budovy, vnitřní
  kresba, geodetické body atd.). Zbytek se při importu přeskočí. Přijatelné, protože cena je
  jednorázová a nastává jen v importním skriptu, ne za běhu aplikace.
- **Vědomě přijatá nevýhoda č. 2:** předpřipravené sady jsou k dispozici jen v EPSG:5514 nebo
  **4258**, ne 4326. Zvoleno 4258 (ETRS89) a jeho souřadnice se použijí přímo jako WGS84.
  Rozdíl mezi ETRS89 a WGS84 ve střední Evropě je dnes zhruba půl metru až metr (obě soustavy
  byly totožné v roce 1989, evropská deska se od té doby posouvá ~2,5 cm/rok). To je pod
  přesností samotných katastrálních dat a na mapě neviditelné. Zamítnuta varianta stáhnout 5514
  a přepočítat Křovákovo zobrazení vlastními silami — desítky řádků trigonometrie nebo cizí
  závislost, s reálným rizikem tiché chyby, výměnou za submetrový zisk, který nikdo nepozná.
  **Toto patří do README jako vědomé rozhodnutí, ne jako opomenutí.**
- Zjištěny a zapsány identifikátory katastrálních území: **Jičín 659541, Popovice u Jičína
  725838, Robousy 740225, Valdice 776530** (přes uložený dotaz `GetZoningByName`).

**Zjištění po cestě:**
- **Třetí past na pořadí os:** předpřipravené soubory v EPSG:4258 mají souřadnice v pořadí
  **lat,lon**, zatímco `GetFeature` s `srsName=EPSG:4326` je vracel jako **lon,lat**. Stejný
  server, dvě cesty, opačná konvence. Po přechodu na `GetSpatialDataSet` platí pro nás lat,lon.
- **Diakritika v parametrech uložených dotazů:** `ZONING_NAME=Jičín` poslané neenkódovaně vrátí
  `numberMatched="0"` bez chyby, správně funguje až `Ji%C4%8D%C3%ADn`. Znovu stejný vzorec jako
  u prefixu a bbox — tento server chyby nehlásí, jen tiše vrací prázdno.
- **Sousednost zvolených 4 k.ú.:** obalové obdélníky Popovic (jih), Robous (východ) a Valdic
  (sever) se všechny překrývají s obdélníkem Jičína. Je to silná indicie, **ne důkaz** — dvě
  území mohou mít překryv obalových obdélníků a přesto se nedotýkat. Definitivní ověření až
  vizuálně nad mapou, bod zůstává otevřený.
- Server nabízí i uložené dotazy `GetParcel` (parcela podle k.ú. + parcelního čísla) a
  `GetNeighbourParcels` (sousední parcely). Teď je nepoužijeme, protože data máme lokálně, ale
  stojí za zmínku v README v sekci "co dál" — např. jako cesta k vyhledávání parcely podle čísla.

**Stav repozitáře:** Beze změny, žádný aplikační kód. Necommitnuté zůstávají poslední záznamy
v `roadmap.md` a úpravy `plan.md`.

---

## [2026-08-04 13:35] – Tomáš (rozhodnutí po diskuzi) / Claude (zápis)

**Co se dělo:** Rozhodnuto, v jakém jazyce se bude psát kód. Tomáš označil téma za "backbone
decision" a vyžádal si k němu argumentaci, ne jen doporučení — proběhla diskuze, ve které Tomáš
nejdřív navrhoval variantu "všechno česky" (konzistence: když jsou komentáře i UI česky, ať je
česky i zbytek) a Claude proti tomu argumentoval. Aplikační kód stále nepsán.

**Rozhodnutí a proč:**
- **Identifikátory (proměnné, funkce, tabulky, sloupce) anglicky. Komentáře česky. Texty v UI
  česky. Dokumentace (`plan.md`, `roadmap.md`, README) česky.**
- Není to náhodné míchání, ale hranice mezi vrstvami: *kód anglicky, všechno, co člověk čte jako
  souvislý text, česky.* Texty v UI patří do jazyka uživatele, komentáře do jazyka týmu,
  identifikátory do jazyka ekosystému daného programovacího jazyka.
- Argument, který rozhodl: **čeština je flektivní, identifikátory se neskloňují.** `$parcely` je
  zároveň nominativ plurálu ("parcels") i genitiv singuláru ("of the parcel") — čtenář to musí
  pokaždé odvozovat z kontextu. Anglické `$parcels` / `$parcel` tuto dvojznačnost nemá. U názvů
  funkcí (`najdiParceluPodleKlice()`) je nutné volit pád pro každé podstatné jméno ve jméně.
- Druhý argument: kód je strukturálně anglicky tak jako tak (`foreach`, `as`, `SELECT`, `PDO`),
  takže "všechno česky" nedá jeden jazyk, ale české podstatné jméno v anglické gramatice.
- Třetí argument: anglické názvy sloupců mapují 1:1 na zdrojová pole INSPIRE (`cp-ext:landType`
  → `land_type`, `cp:areaValue` → `area_m2`), takže nejde o náš překlad, ale o převzetí oficiálního
  anglického názvosloví ČÚZK. Česká varianta (`druh_pozemku`) by ten vztah ke zdroji zakryla.
- Čtvrtý argument: české identifikátory se v praxi píší bez diakritiky (`vymera`,
  `katastralni_uzemi`, `zpusob_vyuziti`), takže český čtenář čte horší češtinu, než jakou by
  anglický čtenář četl angličtinu.
- **Zamítnutá varianta "vše česky" nebyla označena za chybnou** — je obhajitelná (doména je český
  katastr, `věcné břemeno` nemá čistý anglický ekvivalent) a existují takové reálné codebase.
  Důvod zamítnutí je praktický: působí neobvykle a u pohovoru by se čas věnoval obhajobě této
  volby místo obhajoby kódu.

**Úkol vyplývající z rozhodnutí:** do README přidat krátký odstavec vysvětlující tuto jazykovou
konvenci a proč byla zvolena (vyžádal si Tomáš). Zatím nesplněno — README ještě neexistuje.

**Stav repozitáře:** Beze změny, žádný aplikační kód.

---

## [2026-08-04 13:50] – Tomáš (rozhodnutí) / Claude (měření a zápis)

**Co se dělo:** Změřen skutečný objem dat všech čtyř katastrálních území (rozbalením stažených
sad, ne odhadem) a ověřeno, která PHP rozšíření jsou v Tomášově instalaci reálně zapnutá.
Na základě naměřených čísel padla dvě rozhodnutí. Aplikační kód stále nepsán.

**Naměřená data (podklad pro obě rozhodnutí):**

| k.ú. | parcel | zip | rozbaleno |
|---|---|---|---|
| Jičín | 12 284 | 7,5 MB | 239 MB |
| Robousy | 2 444 | 1,3 MB | 39 MB |
| Popovice u Jičína | 1 475 | 0,7 MB | 23 MB |
| Valdice | 1 053 | ~1 MB | 17 MB |
| **celkem** | **17 256** | **~10,5 MB** | **~318 MB** |

**Rozhodnutí a proč:**
- **Stahovat zipovaně (`zipped=true`).** Kompresní poměr GML je zhruba 30:1 (318 MB → 10,5 MB),
  takže nejde o kosmetickou úsporu — nezipovaná varianta by znamenala stáhnout z ČÚZK 318 MB
  při každém běhu importu. Cena: nutné zapnout rozšíření `zip` (PHP `ZipArchive`), které v
  Tomášově `php.ini` **zatím zapnuté není** (ověřeno přes `php -m`; zapnuté jsou `pdo_sqlite`,
  `sqlite3`, `xmlreader`, `zlib`, `dom`, `SimpleXML`). Jde o stejný jednořádkový zásah, jaký už
  proběhl u SQLite. Musí být uvedeno v README mezi předpoklady.
- **Hotová SQLite databáze se commitne do repozitáře, spolu s importním skriptem.** Recenzent
  tak spustí aplikaci jen přes `php -S`, bez stahování dat a bez závislosti na dostupnosti ČÚZK
  v den pohovoru — to přímo naplňuje cíl #9 (musí jít spustit lokálně) a #24 (repozitář
  spustitelný po každém commitu). Importní skript v repozitáři zůstává a je nadále předmětem
  hodnocení kvality kódu (#20), jen není nutné ho spouštět.
  **Vědomě přijatá nevýhoda:** binární soubor odhadem 20–40 MB v git historii je jinak
  antipattern. Zde je to obhajitelný kompromis, protože data jsou statická (katastr se v
  horizontu úlohy nemění) a přínos pro spustitelnost je vyšší než cena za velikost repozitáře.
  **Patří do README jako vědomé rozhodnutí.**
- Vedlejší přínos naměřených čísel: **17 256 parcel celkem** je pro SQLite pohodlné množství —
  celý dataset se dá indexovat i dotazovat rychle. Je to dobrá zpráva pro cíl #19 (plynulost)
  a vstup do rozhodnutí o schématu tabulky.
- Zjištěno, že je k dispozici `xmlreader` — streamovací parser. Důležité proto, že rozbalený
  Jičín má 239 MB a `SimpleXML` by ho načítal celý do paměti. Volba parseru se ale rozhodne
  samostatně až u implementace importu.

**Stav repozitáře:** Beze změny, žádný aplikační kód. Necommitnuto zůstává několik záznamů
v `roadmap.md` a úpravy `plan.md`.

---

## [2026-08-04 14:10] – Tomáš (zadání pravidla) / Claude (zápis)

**Co se dělo:** Tomáš zadal nové pravidlo spolupráce — Claude se nemá ptát na svolení pokaždé,
když si potřebuje jen něco zjistit. Zapsáno jako **Pravidlo 6** v `CLAUDE.md` a doplněna
oprávnění v `.claude/settings.json`. Aplikační kód stále nepsán.

**Rozhodnutí a proč:**
- **Pravidlo 6:** zjišťování informací (čtení souborů, prohledávání kódu, web, čtecí dotazy na
  cizí API) bez ptaní. Strop na stahování **10 MB na jedno stažení**; nad limit se Claude ptá.
  Hodnotu určil Tomáš (Claude původně navrhoval 5 MB).
- **Mechanismus: pravidlo v `CLAUDE.md`, ne skill.** Tomáš původně navrhoval přidat skill.
  Claude to rozporoval s odůvodněním, že skill se aktivuje až vyvoláním (`/jméno` nebo když ho
  model vyhodnotí jako relevantní), zatímco požadované chování má platit trvale od začátku každé
  session — a to je přesně role `CLAUDE.md`, který je v kontextu vždy. Skill by tedy zadaný cíl
  nesplnil. Tomáš to akceptoval.
- **Doplněna oprávnění v `.claude/settings.json`:** `WebFetch`, `WebSearch`, `Bash(curl:*)` —
  aby stejné chování nevyvolávalo schvalovací dotazy harnessu. To je druhá, nezávislá vrstva:
  `CLAUDE.md` říká, co Claude *smí dělat*, `settings.json` říká, co *neprobublá jako dotaz*.
- **Vědomé omezení, zapsané i v `CLAUDE.md`:** limit 10 MB nejde vynutit technicky. Oprávnění
  v `settings.json` umí nástroj jen povolit nebo zakázat, nerozliší 2 MB od 300 MB. Limit tedy
  drží pravidlo, ne harness — je to dohoda, ne zámek.

**Stav repozitáře:** Beze změny co do aplikačního kódu. Změněny `CLAUDE.md` a
`.claude/settings.json`, oboje necommitnuto.

---

## [2026-08-04 14:20] – Tomáš (rozhodnutí) / Claude (ověření a zápis)

**Co se dělo:** Rozhodnut zdroj českých popisků pro `landType` (druh pozemku) a `landUse`
(využití území). WFS vrací tyto atributy jen jako anglická klíčová slova
(`BuiltUpArea`, `Road`), do českého UI je tedy nutné je převést. Aplikační kód stále nepsán.

**Rozhodnutí a proč:**
- **Popisky se stahují z registru číselníků ČÚZK při importu**, ne z ručně psané tabulky v kódu.
  Ověřeno, že registr vydává oba číselníky strojově čitelně:
  `https://services.cuzk.cz/registry/codelist/LandTypeValue?format=json` (10 hodnot) a
  `.../LandUseValue?format=json` (29 hodnot). Oba mají shodnou strukturu
  (`codelist.containeditems[].value` s poli `id`, `label.text`, `definition.text`), takže je
  zpracuje jedna funkce. Kromě českého názvu poskytují i definici a úřední číselný kód
  (např. `111` pro ornou půdu).
- Hlavní důvod: **žádný popisek není náš překlad.** "Zastavěná plocha a nádvoří" je oficiální
  znění ČÚZK převzaté ze zdroje, ne formulace, kterou bychom vymysleli — u pohovoru je to
  podstatně silnější odpověď a zároveň to vylučuje nepřesnou terminologii.
- Vedlejší přínosy: pokrývá i hodnoty, které se v našich 4 k.ú. nevyskytují (chmelnice, vinice),
  takže rozšíření rozsahu nevyžádá zásah do kódu; a při opakovaném importu se případná změna
  číselníku promítne sama.
- **Zamítnuto B (napevno zapsané pole v PHP):** jednodušší a bez requestů, ale znění by bylo naše,
  s rizikem nepřesného termínu, a tiše by zastaralo.
- **Zamítnuto C (anglické kódy v DB, překlad až v JS):** API by vracelo angličtinu do českého
  rozhraní, problém s mapováním by se jen přesunul do JavaScriptu a backend by nešlo smysluplně
  otestovat samostatně.
- Kde přesně budou popisky uložené (samostatná tabulka a JOIN vs. denormalizace do řádku parcely)
  **není součástí tohoto rozhodnutí** — patří do rozhodnutí o schématu databáze.

**Zjištění po cestě:** registr ignoruje příponu `.json` i hlavičku `Accept: application/json`
a řídí se výhradně parametrem `?format=json`. Kdo to netuší, dostane HTML a myslí si, že
strojové rozhraní neexistuje.

**Stav repozitáře:** Žádný aplikační kód.

---

## [2026-08-04 14:40] – Tomáš (rozhodnutí) / Claude (ověření a zápis)

**Co se dělo:** Rozhodnuto schéma databáze — poslední otevřený bod před psaním kódu. Před návrhem
ověřeno, co Tomášova SQLite v PHP skutečně umí. Aplikační kód stále nepsán.

**Ověřené vlastnosti SQLite (PHP 8.5.9, SQLite 3.53.2):**
- `json_*` funkce: k dispozici. FTS3/4/5: k dispozici.
- **`rtree` NENÍ k dispozici** (chybí v `PRAGMA compile_options`). R-Tree je prostorový index
  SQLite, tedy standardní způsob, jak odpovědět na dotaz „které polygony leží v tomto výřezu".
  Ve Windows buildu PHP zkompilovaný není.

**Rozhodnutí a proč:**
- **Chybějící R-Tree se nahradí čtyřmi sloupci obalového obdélníku** (`min_lon`, `min_lat`,
  `max_lon`, `max_lat`) a obyčejným B-tree indexem. Dotaz na výřez je pak běžný test překryvu
  obdélníků. Při 17 256 řádcích je to plně dostačující — R-Tree se vyplatí až u statisíců
  záznamů. **Patří do README:** je to vynucená volba daná prostředím, ne nedbalost.
- **Geometrie se ukládá jako hotový GeoJSON řetězec (TEXT).** Převod z GML proběhne jednou při
  importu; mapový endpoint pak jen skládá řetězce a neprovádí za běhu žádnou konverzi. Přímo
  slouží cíli #19 (plynulost) — princip „práci udělej jednou při importu, ne při každém requestu".
  **Vědomě přijatá nevýhoda:** v databázi je prezentační formát, ne neutrální data, a soubor
  `.sqlite` je větší (což není zadarmo, protože se commituje).
  **Zamítnuta varianta binárního BLOBu** (`pack('d*')`): dala by nejmenší databázi, ale vyžadovala
  by rozbalení a `json_encode()` při každém requestu — tedy přesně tu práci, které se má podle
  hodnoticího kritéria #19 předejít.
- **Popisky číselníků zůstávají v samostatných tabulkách a připojují se JOINem**, neduplikují se
  do řádků parcel. Tabulky mají 39 a 4 řádky, takže cena JOINu je zanedbatelná, zatímco
  denormalizace by uložila "Zastavěná plocha a nádvoří" 5 354krát a zrušila jedinou autoritativní
  kopii popisku. Složitější SQL v detail endpointu je přijatá cena.

**Rozhodnuté schéma:**

```sql
CREATE TABLE zonings (
    code  INTEGER PRIMARY KEY,          -- 659541
    name  TEXT NOT NULL                 -- 'Jičín'
);

CREATE TABLE codelist_values (
    codelist    TEXT NOT NULL,          -- 'LandType' | 'LandUse'
    code        TEXT NOT NULL,          -- 'BuiltUpArea'
    label       TEXT NOT NULL,          -- 'Zastavěná plocha a nádvoří'
    definition  TEXT,
    PRIMARY KEY (codelist, code)
);

CREATE TABLE parcels (
    ref            TEXT PRIMARY KEY,    -- '659541-1185/3'
    zoning_code    INTEGER NOT NULL REFERENCES zonings(code),
    parcel_number  TEXT NOT NULL,       -- '1185/3' | 'st. 2344'
    area_m2        INTEGER,
    land_type      TEXT,                -- 'BuiltUpArea'
    land_use       TEXT,                -- 'Road' | NULL
    geometry       TEXT NOT NULL,       -- hotový GeoJSON
    min_lon        REAL NOT NULL,
    min_lat        REAL NOT NULL,
    max_lon        REAL NOT NULL,
    max_lat        REAL NOT NULL
);

CREATE INDEX ix_parcels_bbox   ON parcels(min_lon, max_lon, min_lat, max_lat);
CREATE INDEX ix_parcels_zoning ON parcels(zoning_code);
```

**Poznámka k tomu, co ve schématu vědomě NENÍ:** `gml:id` (rozhodnuto dříve nedržet dvě identity
téhož objektu), `referencePoint`, `beginLifespanVersion` a odkaz na budovu. Nic z toho zatím
nemá využití — přidá se, až bude důvod, ne dopředu.

**Stav repozitáře:** Žádný aplikační kód. **Tímto jsou uzavřena všechna rozhodnutí, která si
`plan.md` vytkl před začátkem implementace.**

---

## [2026-08-04 14:55] – Tomáš (zadání) / Claude (úprava a ověření)

**Co se dělo:** Hook v `.claude/settings.json`, který vynucoval Pravidlo 5, změněn z **blokování**
na **upozornění**. Důvod podle Tomáše: hook commit zablokoval, ale Tomáš si toho nevšiml —
zmínka v dlouhé odpovědi Claude není dost viditelná. Varování viditelné přímo v UI je lepší než
tiché selhání. Aplikační kód stále nepsán.

**Rozhodnutí a proč:**
- **Hook nově vrací `exit 0` a vypisuje `{"systemMessage": ...}`** místo `exit 2` se zprávou na
  stderr. `systemMessage` je dokumentovaný způsob, jak z hooku zobrazit text **uživateli**;
  `exit 2` naopak příkaz zablokuje a zprávu pošle jen Claudovi. Přesně tenhle rozdíl způsobil,
  že si Tomáš zablokovaného commitu nevšiml.
- **Pravidlo 5 v `CLAUDE.md` zůstává beze změny.** Mění se jen vynucení: dřív technický zámek,
  teď upozornění. Povinnost zeptat se před commitem platí dál, jen ji drží pravidlo, ne harness —
  stejná konstrukce jako u limitu 10 MB v Pravidle 6.
- **Ponechána široká shoda na celém vstupu hooku**, ne filtr `if: Bash(git *)`. Důvod: složený
  příkaz typu `cd projekt && git commit` nezačíná na `git`, takže by ho úzký filtr minul.
  Falešně pozitivní upozornění je teď levné (jen se zobrazí navíc), zatímco **minuté** upozornění
  by bylo přesně to selhání, kvůli kterému se hook mění.

**Zjištění po cestě:** starý hook zablokoval i vlastní testovací příkaz, který řetězec
`git commit` jen obsahoval jako text uvnitř skriptu. Hook totiž porovnává celý JSON vstupu, ne
jen spouštěný příkaz. U blokující verze to byla reálná vada (nešlo o commitu ani napsat skript),
u varovné verze jde nanejvýš o zbytečné upozornění.

**Ověření:** příkaz hooku otestován mimo harness na třech vstupech — `git commit` a `git push`
vrací `exit 0` + `systemMessage`, `ls` nevrací nic. `settings.json` ověřen jako platný JSON
a existující blok `permissions` zůstal zachován.

**Stav repozitáře:** Žádný aplikační kód. Změněn `.claude/settings.json`.

---

## Co dál (další session)

- ~~Ověřit `srsName=EPSG:4326`~~ — hotovo 2026-08-04, funguje.
- Vizuálně/datově potvrdit, že zvolená 4 k.ú. na sebe skutečně navazují (zbývá — až nad mapou).
- ~~Rozhodnout schéma unikátního klíče parcely v SQLite~~ — hotovo 2026-08-04,
  `cp:nationalCadastralReference`.
- ~~Rozhodnout způsob stahování~~ — hotovo 2026-08-04, `GetSpatialDataSet` po katastrálních územích.
- ~~Rozhodnout jazykovou konvenci kódu~~ — hotovo 2026-08-04, identifikátory anglicky, zbytek česky.
- ~~Rozhodnout zipovaně vs. nezipovaně~~ — hotovo 2026-08-04, zipovaně (`zipped=true`).
- ~~Rozhodnout, jestli commitovat hotovou SQLite databázi~~ — hotovo 2026-08-04, ano, spolu se skriptem.
- Zapnout `extension=zip` v `php.ini` (zatím není zapnuté).
- ~~Rozhodnout zdroj českých popisků `landType`/`landUse`~~ — hotovo 2026-08-04, registr číselníků
  ČÚZK (`?format=json`), stahuje se při importu.
- ~~Rozhodnout schéma tabulky v SQLite~~ — hotovo 2026-08-04, viz záznam 14:40.
- **Všechna rozhodnutí před implementací jsou uzavřená. Další session = psaní kódu.**
  Pořadí prací: (1) zapnout `extension=zip`, (2) importní skript, (3) API endpointy,
  (4) frontend s Leafletem, (5) výkonové doladění, (6) README.
  Platí dál `CLAUDE.md` — každé implementační rozhodnutí, které se objeví až za běhu psaní
  (struktura funkcí, formát odpovědi, ošetření chyb), se nejdřív probere.

### Nesplněné úkoly nesené dál

- Do README doplnit odstavec o jazykové konvenci kódu (viz záznam 2026-08-04 13:35).
- Do README doplnit vědomé rozhodnutí o EPSG:4258 místo WGS84 (viz záznam 2026-08-04 13:20).
- Do README doplnit vědomé rozhodnutí o chybějících vlastnických datech (viz `plan.md`).
- Do README doplnit vědomé rozhodnutí o commitnuté SQLite databázi (viz záznam 2026-08-04 13:50).
- Do README doplnit `extension=zip` mezi předpoklady (viz záznam 2026-08-04 13:50).
- Vizuálně ověřit sousednost 4 k.ú. nad hotovou mapou.
