# Roadmap / průběžný log

> **Tenhle soubor číst nemusíte.** Je to syrový chronologický podklad — jeden záznam za každou
> pracovní session, zapisovaný průběžně během práce, ne sepsaný zpětně na konci. Leží tu jako
> doklad postupu, ne jako text ke čtení.
>
> **Shrnutí rozhodnutí tematicky, a mnohem kratší, je v [`rozhodnuti.md`](rozhodnuti.md).**
> Tam začněte. Sem se dívejte, jen když u konkrétního rozhodnutí chcete vidět, kdy padlo,
> co mu předcházelo a co se přitom naměřilo.

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

## [2026-08-04 16:55] – Tomáš (provedl) / Claude (navedl a zapsal)

**Co se dělo:** Zapnuto rozšíření `zip` v `C:\php\php.ini` (řádek 949, odkomentováno
`;extension=zip`). Ověřeno přes `php -m`, `zip` se načítá. Tím je splněn poslední přípravný bod
ze seznamu "Co dál" a prostředí je kompletní. Aplikační kód zatím nepsán.

**Rozhodnutí a proč:**
- **Způsob práce v implementační fázi: kód píše Tomáš, Claude vysvětluje a reviduje**, místo
  dřívější představy, že Claude píše a Tomáš čte. Rozhodl Tomáš po diskuzi. Důvod: plánovací fáze
  (~4 h) vybudovala znalost *proč*, ale ne *jak* — čtení odsouhlaseného kódu a jeho samostatné
  napsání jsou různé dovednosti a u pohovoru se počítá ta druhá (možnost, že bude vyzván kód na
  místě upravit). Zásada, kterou Tomáš formuloval sám: standard je **vlastnictví, ne autorství** —
  stejný princip, na kterém podle zadavatele stojí i práce týmu Viagem s Claude Max (nástroj píše,
  odpovědnost nese člověk, který to spustil).
- Praktické dělení: mechanické jednorázové části (parsování GML) může sepsat Claude a projít je
  s Tomášem řádek po řádku; hodnocené a demonstrované části (PHP endpointy, frontend) píše Tomáš.
  Pravidlo: co Tomáš neumí vysvětlit vlastními slovy, to se neodevzdává.
- Zapsáno i do trvalé paměti Claude (mimo repozitář), aby se dohoda neuvolnila v průběhu delší
  session — stejné riziko, na které upozorňuje i Pravidlo 1 v `CLAUDE.md`.

**Stav repozitáře:** Beze změny co do aplikačního kódu. PHP prostředí je nyní kompletní
(`pdo_sqlite`, `sqlite3`, `xmlreader`, `zlib`, `dom`, `SimpleXML`, `zip`).

---

## [2026-08-04 17:45] – Tomáš (rozhodnutí a kód) / Claude (ověření a zápis)

**Co se dělo:** Založena struktura projektu, `.gitignore` a kostra `import.php` (první aplikační
kód — píše ho Tomáš). Před psaním stahovací části ověřen reálnými dotazy přesný tvar URL pro
`GetSpatialDataSet`, protože `plan.md` ani dřívější záznamy si nepoznamenaly konkrétní podobu
parametrů.

**Rozhodnutí a proč:**
- **Rozvržení projektu s odděleným web rootem** — `public/` (jediná složka vystavená serverem),
  `src/`, `db/`, `data/`, `docs/`. Spouští se `php -S localhost:8000 -t public`. Důvod: bez
  odděleného rootu je `db/parcels.sqlite` volně stažitelná přes HTTP a `import.php` spustitelný
  z prohlížeče. Cena: reviewer musí použít přepínač `-t public`, patří proto na první řádek README.
- **Plánovací soubory přesunuty do `docs/`**, `CLAUDE.md` a `README.md` zůstávají v rootu.
  `CLAUDE.md` se z jiného umístění vůbec nenačte (Claude Code ho hledá v kořeni projektu), README
  vykresluje GitHub jen z kořene — u obou jde o technické omezení, ne o preferenci.
- **Seznam katastrálních území jako konstanta `ZONINGS` přímo v `import.php`**, ne konfigurační
  soubor ani dotaz na ČÚZK za běhu. Důvod: žádný jiný konzument seznam nepotřebuje (API čte území
  z tabulky `zonings`), takže konfigurační soubor by byl abstrakce pro jediného volajícího.
  Runtime dotaz by navíc přidal síťovou chybovou cestu k datům, která se prakticky nemění.
- **`declare(strict_types=1)`** — import převádí text na čísla (`areaValue`, souřadnice) a tiché
  typové konverze jsou přesně to místo, kde by chyba prošla neviditelně.
- **`.gitignore` chrání i zadávací PDF** (bez lomítka, tj. na jakékoliv úrovni) — dřív bylo jen
  „nezacommitnuté", což je stav, ne ochrana; jeden `git add .` by ho poslal do veřejného repozitáře.

**Ověřeno reálnými dotazy (opravuje dřívější záznamy):**
- **Správný tvar identifikátoru je `DataSetIdCode=CPX.SD.<kód k.ú.>`**, např. `CPX.SD.659541`.
  Dřívější záznam z 13:20 uváděl jen kódy území — s holým `659541` server odpoví HTTP 400.
- **`DataSetIdNamespace` je nepovinný** — s `CZ-00025712-CUZK_CPX` i bez něj vrací server totéž.
- **Dokumentace stored query je zavádějící:** popisuje tvar `AD.SD.[kód]` a tvrdí, že sady jsou
  děleny „po obcích". Obojí je text převzatý ze služby pro adresní místa (AD); pro CPX platí
  prefix `CPX.` a dělení po katastrálních územích. Ověřeno testem obou variant.
- **Chybová hláška serveru lže:** na neplatný `DataSetIdCode` vrací
  `Unsupported CRS for specified DataSetIdCode`, přestože CRS je platné. Kdo hlášce uvěří, řeší
  souřadnicové systémy místo identifikátoru. Už počtvrté tento server hlásí něco jiného, než je
  skutečná příčina (dřív: chybějící prefix, pořadí os, neenkódovaná diakritika).
- **EPSG:4258 pro předpřipravené sady funguje** — rozhodnutí z 13:20 potvrzeno, ne vyvráceno.
- **Soubor uvnitř zipu se jmenuje `<kód>.xml`, ne `.gml`** (např. `776530.xml`, 17 MB rozbaleno).
  Obsah je GML, ale kód hledající uvnitř archivu `*.gml` by nenašel nic.
- Naměřené velikosti zipů: Jičín 7,53 MB, Robousy 1,29 MB, Popovice 0,73 MB, Valdice 0,53 MB.

**Zjištění k prostředí:** rozšíření **`curl` v PHP zapnuté není** (na rozdíl od `zip`, `sqlite3`,
`xmlreader`), `allow_url_fopen` je `On`. Volba způsobu stahování se tomu musí přizpůsobit —
je to vstup do dalšího rozhodnutí, ne hotová věc.

**Stav repozitáře:** `import.php` obsahuje hlavičku, `declare(strict_types=1)` a konstantu
`ZONINGS`. Spustitelný (`php import.php` proběhne bez výstupu). Nic zatím necommitnuto.

---

## [2026-08-04 18:50] – Tomáš (psal kód) / Claude (vysvětloval, ověřoval, zapsal)

**Co se dělo:** Napsána a odladěna celá stahovací část `import.php`. Všechna čtyři katastrální
území se stáhnou (~9,7 MB), velikosti souhlasí s referenčním stažením přes curl. Kód psal Tomáš,
Claude vysvětloval syntaxi po malých krocích — Tomáš PHP dosud neuměl, takže tempo bylo
„jedna funkce = několik kroků, každý ověřený spuštěním".

**Rozhodnutí a proč:**
- **Stahování přes `file_get_contents()` + kontrola obsahu, ne `copy()` ani cURL.** Rozhodující
  argument: server na chybný vstup vrací HTTP 400 s XML dokumentem. `copy()` by ho uložil jako
  `.zip` a chyba by se projevila až o krok dál jako „poškozený archiv". Kontrola prvních dvou
  bajtů (`PK`, podpis ZIP formátu) chytí problém v okamžiku vzniku a do výjimky vloží
  prvních 200 znaků skutečné odpovědi ČÚZK. cURL zamítnut: rozšíření `curl` není zapnuté a čtyři
  statické soubory jeho robustnost nevyužijí — méně předpokladů pro reviewera je samo o sobě
  argument kvality.
- **Chyby se hlásí výjimkami (`RuntimeException`), ne návratovou hodnotou `false` ani `exit()`
  uvnitř funkce.** Jeden `try/catch` na konci skriptu obsluhuje všechny čtyři budoucí kroky
  (stažení, rozbalení, parsování, zápis) místo čtyř samostatných větví. Funkce tak neví, jak
  program končí, a zůstává použitelná i jinde. Návratová hodnota `false` by navíc rozbila
  návratový typ `: string` kvůli `declare(strict_types=1)`.
- **Sestavení URL vyčleněno do vlastní funkce `buildDatasetUrl()`.** Umožňuje URL vytisknout
  a zkontrolovat bez síťového dotazu — u serveru, který čtyřikrát ohlásil jinou příčinu, než
  byla skutečná, to není luxus. `downloadDataset()` tím zůstává krátká.
- **Dotaz se skládá přes `http_build_query()`, ne spojováním řetězců.** Parametry obsahují `:`
  a `/` (např. `storedquery_id`), které je nutné enkódovat. Ověřeno, že server enkódovanou
  podobu (`%3A%2F%2F`) přijímá stejně jako neenkódovanou — vrací identických 529 974 bajtů.
- **Skript vypisuje průběh** (`Jičín: data/659541.zip`) — u importu, který běží desítky sekund,
  je tiché čekání horší než tři řádky výstupu. Chyby jdou na `STDERR` a skript končí `exit(1)`,
  aby selhání bylo rozpoznatelné i strojově, ne jen okem.
- **Stažené soubory se kešují** — funkce se nejdřív podívá, jestli zip už v `data/` je.
  Opakovaný běh je okamžitý. Bez toho by každá iterace ladění parseru znamenala 9,7 MB navíc
  z cizího serveru.

**Zjištění k prostředí (patří do README mezi předpoklady):**
- **Rozšíření `openssl` nebylo zapnuté** a bez něj PHP vůbec nezná wrapper `https` —
  `stream_get_wrappers()` vypisoval `http`, ale ne `https`. Projeví se to hláškou
  *„Unable to find the wrapper https"*, která nezní jako chybějící rozšíření. Zapnuto na
  řádku 930 v `php.ini`. **Volba cURL by nepomohla** — `php_curl` potřebuje openssl pro TLS
  úplně stejně, takže tenhle krok byl nutný tak jako tak.
- Předpoklady pro spuštění jsou tedy: `pdo_sqlite`, `sqlite3`, `xmlreader`, `zip`, `openssl`.

**Ověřeno reálným během:** čtyři zipy v `data/` — Jičín 7 531 561 B, Robousy 1 292 202 B,
Popovice 730 826 B, Valdice 529 974 B, celkem 9,7 MB. Velikosti se shodují s referenčním
stažením přes curl. Druhý běh skriptu je okamžitý (keš funguje).

**Stav repozitáře:** `import.php` je spustitelný a dělá kompletní první krok importu.
`data/` je ignorovaná gitem, takže stažené zipy do repozitáře nejdou. Necommitnuto.

---

## [2026-08-05 14:26] – Tomáš (rozhodnutí a provedení) / Claude (ověření a zápis)

**Co se dělo:** Tomáš rozdělil repozitář na dvě větve, aby odevzdávaný `main` neobsahoval
soubory řídící spolupráci s Claude. Podnětem byl dojem, že Claude je u commitů veden jako
spoluautor. Před zásahem to Claude ověřil v gitu — **nebyla to pravda**: všechny tři původní
commity mají v poli author i committer výhradně `Tomas-Polanek`, a `%(trailers)` je u všech
tří prázdný, tedy žádný `Co-Authored-By`. Claude se v repozitáři objevoval jen **obsahem
souborů** — `CLAUDE.md` v kořeni, `.claude/settings.json`, a hlavně `docs/roadmap.md`, jehož
záznamy jsou hlavičkovány `– Claude` a který se mění v každém commitu, takže "Claude" byl
vidět v diffu každého commitu. Rozdělení větví tedy neřeší autorství (to bylo od začátku
Tomášovo), ale **vzhled odevzdávaného repozitáře**. Aplikační kód se v této session nepsal.

**Rozhodnutí a proč:**
- **Dvě větve místo smazání repozitáře.** Původní úvaha byla smazat GitHub repozitář celý a
  založit znovu. Zvoleno rozdělení: `main` = čistý deliverable (`.gitignore`, `README.md`,
  `import.php`), `claude` = pracovní větev se vším včetně `CLAUDE.md` a `docs/`. Důvod: cíl #16
  ze zadání (krátký zápisník rozhodnutí) a hodnoticí kritérium #17 (postup a zdůvodnění) stojí
  přímo na obsahu `roadmap.md` — smazáním by se ztratil podklad k tomu, co zadavatel hodnotí
  jako první. Rozdělení splní obojí: `main` nevypadá jako přepis session, materiál pro zápisník
  zůstává dohledatelný.
- **Commity do `main` dělá výhradně Tomáš.** Claude commituje jen do větve `claude`. Je to
  zpřísnění Pravidla 5 (`CLAUDE.md`) — dosud Claude směl commitovat po dotazu, nově do `main`
  nesmí vůbec.
- **Historie větví je rozpojená** — `main` byl založen jako nový commit (`4e025c9`), ne jako
  potomek původní historie. Praktický důsledek: `git merge claude` do `main` by přitáhl i
  `docs/` a `CLAUDE.md`, tedy přesně to, co tam nemá být. Obsah se do `main` přenáší kopií
  souboru a vlastním commitem, ne merge.
- **Původní historie zachována ve větvi `backup-2026-08-05`** — nic se nemazalo nenávratně.
- **Transparentnost se nemění.** Tomáš uvede použití Claude na pohovoru ústně; cílem není
  použití skrýt, ale aby repozitář četl jako pracovní výsledek, ne jako záznam konverzace.

**Zjištěná otevřená vada (neopraveno):** `git config user.email` je nastaven na
`tomaspolane1@gmail.com`, chybí `k` — správně `tomaspolanek1@gmail.com`. GitHub páruje commity
k účtu podle e-mailu, takže commity se k Tomášovu profilu nepřipojují (šedý avatar, nepočítají
se do příspěvků). Týká se i nového `main` commitu. Oprava je rozhodnutí Tomáše, ne mechanická
oprava překlepu — starší commity by bylo nutné přepsat, nebo nechat být.

**Otevřené téma z této session:** Tomáš uvedl, že ho PHP zdržuje. Změna backendu ale není
možná — zadání ho určuje výslovně (cíl #4, "Backend musí být v PHP"). Řešení se hledá v dělbě
práce uvnitř PHP, ne mimo něj; rozhodnutí zatím nepadlo.

**Stav repozitáře:** `main` (= `origin/main`) obsahuje `.gitignore`, `README.md` (prázdný) a
`import.php`. Větev `claude` navíc `CLAUDE.md`, `.claude/settings.json`, `docs/plan.md`,
`docs/roadmap.md`. Aplikační kód beze změny — `import.php` dělá kompletní stahovací krok.

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
- ~~Zapnout `extension=zip` v `php.ini`~~ — hotovo 2026-08-04, viz záznam 16:55.
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

---

## [2026-08-05 14:10] – Claude (asistováno, příkazy spouštěl Tomáš)

**Co se dělo:** Reorganizace gitových větví na žádost Tomáše. Cíl: čistý `main` jen s funkčním
kódem (bez Claude spolupráce) a oddělená pracovní větev `claude` se vším (funkční kód + `plan.md`,
`roadmap.md`, `CLAUDE.md`, `.claude/`). Aplikační kód se neměnil, šlo čistě o strukturu
repozitáře. Příkazy spouštěl Tomáš ve svém terminálu, Claude radil krok po kroku a po každé dávce
ověřoval stav.

**Rozhodnutí a proč:**
- `claude` = původní `main` jen přejmenovaný (`git branch -m`), aby si zachoval plnou historii
  a zůstal pracovní větví, kde Claude soubory fyzicky existují na disku (potřebné pro spolupráci).
- `main` postaven jako nová orphan větev s jediným commitem (`4e025c9`) místo přepisu historie
  (filter-branch/filter-repo) — jednodušší, bezpečnější a lépe obhajitelné: „main = odevzdávka,
  spolupráce žije na claude". `git filter-repo` navíc nebyl k dispozici.
- Před zásahem vytvořena pojistná větev `backup-2026-08-05`, aby šlo cokoliv vrátit.
- Vše provedeno jen lokálně, bez pushe — push na GitHub (oddělit `claude` od `origin/main`
  a `main` poslat force, protože historie se rozešla) se nechává na Tomáše po odsouhlasení.
- Prázdné složky (`db/`, `src/`, `public/api/`) se do `main` nepřenesly — git neumí sledovat
  prázdné adresáře; `.gitkeep` zatím nepřidán (odloženo, do rozhodnutí).

**Poznámka:** Zdánlivé „rozpracované změny" v `import.php`/`.gitignore`, které hlásil
Linux-sandbox, byly jen rozdíl konců řádků (CRLF vs LF), ne obsahové úpravy — Windows git
(`autocrlf`) hlásí `working tree clean`. Žádný kód se neztratil.

**Stav repozitáře:** `main` = 1 commit (`4e025c9`), jen `.gitignore`, `README.md`, `import.php`.
`claude` = plná historie (`69748c5`), všechny soubory. `backup-2026-08-05` drží původní stav.
Spustitelnost beze změny (šlo jen o git strukturu, ne o kód).

---

## [2026-08-05 14:44] – Tomáš (zadání pravidla) / Claude (zápis)

**Co se dělo:** Tomáš zadal dohodu o dělbě práce v gitu a Claude ji zapsal jako **Pravidlo 7**
do `CLAUDE.md`. Aplikační kód se neměnil.

**Rozhodnutí a proč:**
- **Pravidlo 7:** Claude pracuje výhradně na větvi `claude`; `main` patří Tomášovi (Claude ji
  nepřepíná, needituje, nemerguje do ní, nepushuje). Všechny commity a pushe dělá Tomáš sám,
  Claude může nanejvýš upozornit, že je vhodná chvíle na commit.
- **Zapsáno do `CLAUDE.md`, ne jen do `roadmap.md`.** `CLAUDE.md` je v kontextu každé session
  automaticky, `roadmap.md` ne — dohoda, která se má držet i za měsíc, patří tam, kde ji Claude
  uvidí bez hledání. Stejná úvaha jako u Pravidla 6 (skill vs. `CLAUDE.md`).
- **Formulováno jako doplněk Pravidla 5, ne jeho náhrada.** Pravidlo 5 říká „neptej se — nedělej
  commit"; Pravidlo 7 přidává „a do `main` ani po zeptání". Dosavadní zpřísnění zapsané zatím
  jen v záznamu z 14:26 tím dostává trvalé místo.
- **Rozšíření oproti záznamu z 14:26:** tam bylo omezení jen na `main`, nově Tomáš dělá **všechny**
  commity, i na větvi `claude`. Důvod je stejný jako u způsobu práce v implementační fázi
  (záznam 2026-08-04 16:55): standard je vlastnictví, ne autorství.

**Zjištění po cestě:** větev `claude` je vůči `origin/main` **ahead 4, behind 1** — historie
větví se rozešla (viz záznam 14:26, `main` je orphan commit). Sjednocení je operace Tomáše,
zatím se neřeší, jen je zaznamenáno, aby to nepřekvapilo později.

**Stav repozitáře:** Beze změny co do aplikačního kódu (`import.php` dělá kompletní stahovací
krok). Změněny `CLAUDE.md` a `docs/roadmap.md`, obojí necommitnuto.

---

## [2026-08-05 14:53] – Tomáš (rozhodnutí) / Claude (zápis)

**Co se dělo:** Uzavřeno otevřené téma ze záznamu 14:26 („Tomáš uvedl, že ho PHP zdržuje").
Změněna dohoda o tom, kdo píše kód. Zapsáno jako **Pravidlo 8** v `CLAUDE.md`. Aplikační kód
se v této session zatím neměnil.

**Rozhodnutí a proč:**
- **Backend (PHP) píše Claude, klientskou část (JS/Leaflet) píše Tomáš.** Rozhodl Tomáš
  s odůvodněním, že PHP neumí a nechce se ho učit uprostřed časově omezené úlohy, zatímco
  frontend si chce napsat sám.
- **Ne „napíšu všechno a vysvětlím na konci", ale „napíšu funkci a hned ji vysvětlím".**
  Tomáš vybral z nabídnutých tří variant tuto. Důvod, který Claude uvedl proti odložení výkladu
  na konec: první setkání s kódem po 500 napsaných řádcích je nejhorší možný okamžik na
  pochopení — po funkcích je to stejně rychlé (Tomáš stejně netypuje), ale zvládnutelné.
  Součástí dohody je, že „přepiš to jednodušeji, nerozumím tomu" je platný důvod k přepsání.
- **Nahrazuje dohodu ze záznamu 2026-08-04 16:55** („kód píše Tomáš, Claude vysvětluje").
  Podle pravidel tohoto souboru se starý záznam needituje — platí novější.
- **Co se nemění:** zásada *vlastnictví, ne autorství* — co Tomáš neumí vysvětlit vlastními
  slovy, to se neodevzdává. A Pravidlo 1: rozhodnutí (struktura funkcí, formát odpovědi,
  ošetření chyb) se dál probírají předem, i když kód píše Claude.
- **Vědomě přijaté riziko:** zadavatel může u pohovoru vyzvat k úpravě kódu na místě. Tuto
  schopnost trénuje psaní, ne čtení. Riziko se zmenšuje výkladem po částech a tím, že frontend
  — tedy část, na kterou se recenzent dívá nejdřív — píše Tomáš. Neodstraňuje se úplně.
  Claude na riziko upozornil před rozhodnutím, Tomáš ho po vysvětlení potvrdil.

**Stav repozitáře:** `import.php` beze změny (kompletní stahovací krok). Změněny `CLAUDE.md`
a `docs/roadmap.md`, necommitnuto.

---

## [2026-08-05 15:14] – Claude (psal kód, viz Pravidlo 8) / Tomáš (rozhodoval)

**Co se dělo:** Dokončen celý importní skript — rozbalení, parsování GML a zápis do SQLite.
`php import.php` proběhne za 5,3 s a vytvoří `db/parcels.sqlite` (9,1 MB) se 17 256 parcelami.
Přidán `src/Parcel.php`. První session podle Pravidla 8 (kód píše Claude a vysvětluje po
funkcích). Před psaním předloženo 7 rozhodnutí, Tomáš vybral A/A/A, pak A/B/A/A, pak A.

**Rozhodnutí a proč:**
- **Čtení přímo z archivu přes wrapper `zip://`**, ne `ZipArchive::extractTo()`. Změřeno:
  celý import má vrchol paměti **4 MB**, přestože rozbalený Jičín má 239 MB. Odpadá ~318 MB
  dočasných souborů i kód na jejich úklid.
- **Parser hybridně: `XMLReader` najde parcelu, `readOuterXml()` + `SimpleXML` ji rozeberou.**
  Streamování drží paměť konstantní, ale samotné vytahování polí se píše proti objektu do 14 kB
  čitelnou syntaxí, ne stavovým automatem. Zamítnut čistý `XMLReader` (dlouhý `switch`, hůř se
  čte — kritérium #20) i `SimpleXML` nad celým souborem (239 MB do paměti).
- **Díry v polygonech se zachovávají.** GeoJSON je vyjadřuje jako další prstence za vnějším
  obrysem. Týká se **647 parcel v Jičíně, celkem ~10 %** — bez nich by se dvůr uvnitř budovy
  vykreslil jako plocha.
- **Parcela je třída `Parcel` s `readonly` vlastnostmi**, ne asociativní pole. **Tomáš přehlasoval
  doporučení Claude** (pole = kratší cesta do SQL). Jeho varianta má reálnou výhodu: překlep
  v názvu vlastnosti je tvrdá chyba, u pole by `$p['are_m2']` tiše propadlo jako `null` až do
  databáze. Konstruktor se volá pojmenovanými argumenty — u 11 parametrů by prohozené
  `minLat`/`minLon` jinak prošlo bez chyby.
- **Opakovaný běh maže a staví databázi znovu** (ne `INSERT OR REPLACE`) — stejný příkaz musí dát
  stejný soubor, bez řádků přeživších z dřívější vadné verze parseru.
- **Jedna transakce na katastrální území** (4 celkem). Bez explicitní transakce potvrzuje SQLite
  každý řádek zvlášť — u 17 256 řádků rozdíl mezi sekundami a minutami. Dělení po územích, aby
  chyba na posledním území nezahodila práci na předchozích.
- **Nedostupný registr číselníků shodí celý import** (varianta A). Databáze, která by se
  „úspěšně" naimportovala a v info panelu ukazovala `BuiltUpArea` místo „Zastavěná plocha a
  nádvoří", je horší výsledek než hlasité selhání, které jde zopakovat.

**Ověřeno reálným během (ne odhadem):**
- 17 256 parcel = přesně součet zaznamenaný 2026-08-04, tedy nic se cestou neztratilo.
- **0 osiřelých kódů** — každý `land_type` i `land_use` má popisek v `codelist_values`.
- **0 vadných obalových obdélníků** (převrácených nebo mimo ČR) — kontrola, že prohození
  lat/lon proběhlo správně. Souřadnice v GeoJSON vycházejí lon-first (`15.38…, 50.45…`).
- Dotaz na mapový výřez: **5 632 parcel za 0,5 ms** — náhrada R-Tree čtyřmi sloupci a B-tree
  indexem je pro tento objem dostatečná (potvrzení rozhodnutí z 2026-08-04 14:40).
- Výsledná databáze má **9,1 MB**, ne 20–40 MB odhadovaných 2026-08-04 — argument pro její
  commitnutí je tedy silnější, než se čekalo.

**Zjištění po cestě:**
- **Element parcely je `cp-ext:CadastralParcel`**, ne `cp:CadastralParcel`. Špatný prefix nevrátí
  chybu, jen 0 nalezených prvků — popáté stejný vzorec tichého selhání u těchto dat.
- **`landUse` chybí u ~74 % parcel, a je to v pořádku.** Ověřeno na Valdicích: 241 hodnot
  s `xlink:href` + 812 s `xsi:nil="true"` = 1 053. Parser vrátil přesně 812 `null` — nejde tedy
  o chybu parsování, ale o skutečný stav dat (ČÚZK u těchto parcel způsob využití nevede).
- **Registr číselníků vydává `id` na hostiteli `services.cuzk.gov.cz`, zatímco GML odkazuje na
  `services.cuzk.cz`.** Kód spojující obě strany podle celé URL by nenašel ani jednu shodu.
  Proto se na obou stranách bere `basename()`, tedy jen koncová část za lomítkem.
- Číselník nese i úřední číselný kód v poli `additionalInformation` (např. `111` pro ornou půdu).
  Zatím se neukládá — není pro co, přidá se, až bude důvod.

**Stav repozitáře:** `import.php` je kompletní a spustitelný (`php import.php`, 5,3 s).
Nový `src/Parcel.php`. `db/parcels.sqlite` vytvořena, zatím **není** v gitu — commit databáze
je samostatné rozhodnutí (padlo 2026-08-04, ale zatím neprovedeno). Necommitnuto: `CLAUDE.md`,
`docs/roadmap.md`, `import.php`, `src/`, `db/`.

---

## [2026-08-05 15:38] – Claude (psal kód, viz Pravidlo 8) / Tomáš (rozhodoval)

**Co se dělo:** Napsána celá serverová část API — `public/router.php`, `public/api/index.php`,
`src/Database.php`, `src/parcels.php`. Tři endpointy běží a jsou otestované reálnými dotazy přes
`php -S`. Před psaním předložena 4 rozhodnutí, Tomáš vybral A/A/A/A. Frontend zatím nevznikl —
podle Pravidla 8 ho píše Tomáš.

**Rozhodnutí a proč:**
- **Jeden vstupní bod `public/api/index.php` s výslovnou tabulkou rout** (`switch` nad cestou),
  ne soubor na endpoint. Odpovídá tomu, co si `plan.md` vytkl v cíli #4 („malý vlastní router,
  několik explicitních route"). Cena: vestavěný server neumí sám poslat požadavek na neexistující
  cestu, takže se spouští s `public/router.php` — **patří do README na první řádek**:
  `php -S localhost:8000 -t public public/router.php`.
- **Mapový endpoint vrací jen geometrii a `ref`, `properties` zůstávají prázdné.** Popisky se
  dotahují až kliknutím z `/api/parcel`. Drží nejčastější dotaz mimo JOIN na číselníky a nenafukuje
  odpověď textem, který uživatel čte vždy jen pro jednu parcelu.
- **Nad limitem výřezu se nevrací nic, jen příznak `zoom_in: true`.** Zamítnuto posílání všeho
  (6,75 MB a 17 256 polygonů na jeden posun mapy = přesně sekání z kritéria #19) i zjednodušování
  geometrie při malém zoomu (Douglas–Peucker je vlastní geometrický kód, který by se musel psát
  a obhajovat, a hranice parcel jsou přitom smysl celé aplikace — patří do README jako „co dál").
- **Klíč parcely jde v detail endpointu jako query parametr** (`/api/parcel?ref=...`), ne jako část
  cesty. Řeší to otevřený problém zapsaný 2026-08-04: `659541-st. 2344` obsahuje mezeru a tečku.
  **Ověřeno reálným dotazem** — `776530-st. 96/7` projde celou cestou od URL po JSON odpověď.
- **Chybný vstup vrací HTTP 400 s JSON popisem, ne prázdný výsledek.** Vědomá reakce na to, čím
  nás celou dobu mátl server ČÚZK — tichá prázdná odpověď na chybný dotaz. Naše API to dělat nemá.
- **Odpověď na výjimku je „Chyba serveru." a nic víc**, skutečná výjimka jde do `error_log`.
  Do prohlížeče nepatří SQL ani cesty na disku.
- **Skládání GeoJSON spojováním řetězců**, ne `json_encode()` nad velkým polem — geometrie je
  v databázi už jako hotový GeoJSON, takže by ji `json_encode()` musel rozebrat a znovu složit.
  Potvrzení rozhodnutí ze schématu (2026-08-04 14:40).

**Naměřeno (localhost, po zahřátí):**

| výřez | parcel | velikost | odpověď |
|---|---|---|---|
| 0,4 × 0,4 km | 473 | 0,24 MB | 4 ms |
| 0,7 × 0,7 km | 1 136 | 0,57 MB | 6 ms |
| 1,0 × 1,0 km | 2 408 | 1,12 MB | 10 ms |
| nad limitem | — | `zoom_in` | 1 ms |

Sestavení celé sady 17 256 prvků trvá 8 ms, samotný SQL dotaz 31 ms — server tedy ani zdaleka
není úzké hrdlo.

**Otevřený bod, vědomě nedořešený:** konstanta `MAX_BBOX_AREA = 0.0002` (výřez do ~1,2 km) je
**provizorní**. Skutečné omezení není na serveru, ale v tom, kolik polygonů zvládne vykreslit
Leaflet — a to nejde změřit dřív, než frontend existuje. Hodnota se doladí až podle naměřeného
vykreslování a **výsledné číslo i jeho zdůvodnění patří do README** jako výkonové rozhodnutí.

**Zjištění po cestě:** `land_type_definition` se u některých hodnot vrací jako prázdný řetězec,
ne `null` — registr ČÚZK definici u části položek nevede. Pro info panel to znamená, že se musí
testovat prázdnost, ne jen `null`.

**Stav repozitáře:** API běží a je otestované (`/api/parcels`, `/api/parcel`, `/api/zonings`,
včetně chybových stavů 400/404/500). Frontend neexistuje, `public/index.php` zatím není.
Necommitnuto: `public/`, `src/Database.php`, `src/parcels.php`, `docs/roadmap.md`.

---

## [2026-08-05 17:10] – Tomáš (psal README) / Claude (zápis)

> **Doplněno zpětně 2026-08-06 13:55.** Tato session proběhla bez záznamu — Pravidlo 2 se
> tehdy neuplatnilo. Zápis vzniká z commitu `55cf17f` a z výsledného souboru, ne z poznámek
> pořízených během práce, takže je stručnější než ostatní záznamy a nezachycuje varianty,
> které se cestou zvažovaly a zahodily. Doplněno se souhlasem Tomáše; mezera se nezakrývá.

**Co se dělo:** Napsán celý obsah `README.md` (232 řádků, commit `55cf17f`) — spuštění,
předpoklady, struktura, popis API, zdroj dat a zápisník. Aplikační kód se neměnil.

**Rozhodnutí a proč (rekonstruováno z výsledku):**
- **Zápisník je součástí README, ne samostatný soubor.** Zadání žádá „krátký zápisník"
  (cíl #16) a README je první a často jediný soubor, který recenzent otevře. `docs/roadmap.md`
  zůstává provozním logem pro nás, README nese destilovanou verzi pro čtenáře zvenčí.
- **Zápisník rozdělený na tři části** — „Rozhodnutí a proč", „Co překvapilo", „Co bych udělal
  s víc časem". Kopíruje to trojici, kterou jmenuje samo zadání, takže recenzent najde
  odpověď na svou otázku tam, kde ji čeká.
- **U rozhodnutí se uvádí i zamítnutá varianta a její cena**, ne jen zvolená cesta (např.
  Křovákovo zobrazení počítané vlastními silami, Douglas–Peucker, R-Tree). Hodnotí se
  „postup a rozhodování" (kritérium #17), a rozhodnutí bez alternativy nevypadá jako
  rozhodnutí.
- **Čísla v README jsou naměřená, ne odhadnutá** — velikosti odpovědí, doby dotazů, počty
  parcel, podíl parcel s dírou. Přebrána z měření zapsaných v tomto logu.

**Stav repozitáře:** spustitelný, beze změny funkčnosti — měnil se jen README.

---

## [2026-08-06 13:48] – Tomáš (rozhodnutí) / Claude (vysvětlení a zápis)

**Co se dělo:** Session bez psaní kódu — probrána volba technologie frontendu a čtyři rozhodnutí,
která musela padnout před první řádkou klientského kódu. Kód píše podle Pravidla 8 Tomáš, zatím
nezačal.

**Rozhodnutí a proč:**
- **Frontend jako vanilla JS + Leaflet, ne Next.js.** Tomáš se ptal na Next.js s tím, že ho bral
  jako způsob, jak spojit HTML, CSS a JS do jednoho souboru — to ale dělá single-file komponenta
  (Vue/Svelte), ne Next.js; ten je frameworkem nad Reactem běžícím na Node.js. Zadání frontend
  nijak neomezuje, takže Next.js by byl přípustný, zamítnut ale byl kvůli důsledkům: druhý server
  vedle PHP (a tím CORS/proxy navíc), nutnost Node + `npm install` v README — což je přesně ta
  závislost, kvůli které jsme commitovali databázi (cíl #9) — tření Leafletu s Reactem o vlastnictví
  DOM, a učení tří nových věcí najednou v už rozpracovaném časovém rozpočtu (cíl #22). Vanilla
  varianta je ~100–150 řádků, tedy rozsah, kde framework nemá co spravovat.
- **Tři soubory `public/index.html` + `app.js` + `style.css`**, ne všechno v jednom HTML.
  Prohlížeč pak hlásí chyby s čísly řádků, která odpovídají skutečnému JS souboru, a v repozitáři
  je na první pohled vidět, kde je logika mapy. Cena: tři soubory místo jednoho.
- **Leaflet stažený do `public/vendor/`, ne z CDN.** Stejný argument jako u commitnuté databáze —
  aplikace se má spustit jedním příkazem a nezávisle na tom, jestli cizí server zrovna běží.
  Cena: ~150 kB cizího kódu v gitu. (Dlaždice OSM internet potřebují tak jako tak, ale bez CDN
  aspoň naběhne stránka a UI, ne bílá obrazovka.)
- **Načítání parcel na `moveend` s debounce, s pokaždé novým dotazem**, ne cache už načtených
  parcel. Zamítnutí cache je vědomé: pomohla by jen u malého posunu do už viděné oblasti, kdežto
  drahý případ (oddálení nebo skok jinam) je celý z nových dat, kde cache nemá co nabídnout —
  zaplatila by se složitost a stejně by se stálo. Tomáš vznesl námitku, že zadání dvakrát zmiňuje
  plynulost; rozhodnuto ji řešit až měřením nad hotovou mapou, ne preventivně.
- **Info panel jako pevný panel vedle mapy**, ne Leaflet popup. Bublina by překryla právě tu
  parcelu, na kterou uživatel klikl, u delších popisků („Zastavěná plocha a nádvoří") by se
  roztáhla přes kus mapy a zavírala by se při pohybu mapy. Cena: rozvržení flexboxem a ošetření
  stavu „nic není vybráno".

**Odloženo k rozhodnutí až podle měření (ne opomenuto):** tři páky na plynulost, které se mají
probrat, teprve až půjde měřit skutečné vykreslování — canvas renderer místo výchozího SVG
(`preferCanvas`, jedna volba místo jednoho DOM uzlu na parcelu), doladění `MAX_BBOX_AREA`
(stále provizorních `0.0002`, otevřený bod z 2026-08-05 15:38) a `AbortController` na zrušení
dotazu pro výřez, ze kterého už uživatel odjel.

**Provedeno:** stažen **Leaflet 1.9.4** do `public/vendor/` — jen `leaflet.js` (144 kB)
a `leaflet.css` (14 kB), bez PNG ikon markerů: kreslíme polygony, ne značky, takže by v repozitáři
ležely nepoužité. Verze 1.9.4 je poslední stabilní 1.x; verze 2.0 je zatím v beta a distribuuje se
jen jako ES moduly — zbytečné riziko pro tenhle rozsah.

**Stav repozitáře:** spustitelný, backend i README beze změny, frontend zatím neexistuje.
Necommitnuto: `docs/roadmap.md`, `public/vendor/`.

---

## [2026-08-06 14:36] – Tomáš (rozhodnutí) / Claude (psal kód)

**Co se dělo:** Tomáš začal psát `index.html` a `style.css` sám. Po třech kolech revize (chybné
vnoření obalu, neuzavřená značka, značka `<sidebar>`, která v HTML neexistuje, a cesty
s nadbytečným `public/`) se ukázalo, že se čas tratí na mechanické části, ne na té, která se
hodnotí. Rozhodnuto posunout hranici z Pravidla 8. Kostru napsal Claude, `app.js` zůstává Tomášovi.

**Rozhodnutí a proč:**
- **Posunuta dělba z Pravidla 8: `index.html` a `style.css` píše Claude, `app.js` píše Tomáš.**
  Zadání hodnotí kvalitu PHP kódu, plynulost a způsob rozhodování — statická kostra stránky mezi
  hodnocenými věcmi není, kdežto klientská logika (načítání podle výřezu, debounce, klik na
  parcelu) přímo souvisí s kritériem plynulosti a Tomáš ji bude u pohovoru obhajovat. Rozhodnutí
  je vědomé a zapsané, ne tiché obejití pravidla; Tomášova zásada **vlastnictví, ne autorství**
  platí dál a je zajištěná tím, že se kostra prochází řádek po řádku, dokud ji Tomáš neumí
  převyprávět vlastními slovy. Zamítnuta varianta pokračovat po řádcích s Tomášem u klávesnice —
  stálo by to zbytek časového rozpočtu (cíl #22) na části, kterou nikdo hodnotit nebude.
- **Obalový `<div id="layout">` kolem mapy a panelu.** Flexbox rovná přímé potomky, takže oba
  prvky musí mít společného rodiče. Zamítnuto dát `display: flex` rovnou na `<body>` — fungovalo
  by to, ale zabralo by to jediný prvek, do kterého by šla později přidat hlavička nad mapu.
- **Panel je `<aside>`, ne `<div>`.** Významově jde o doplňkový obsah vedle hlavního — odečítače
  obrazovky s tím umí pracovat, `<div>` nenese žádný význam.
- **Pevná šířka panelu 320 px**, ne šířka podle obsahu: panel měnící šířku by při každém kliknutí
  poskočil a české popisky („Zastavěná plocha a nádvoří“) potřebují místo.
- **Šedé pozadí mapy `#e8e8e8`** není jen dočasná kontrolní barva — zůstává, protože je vidět
  během načítání dlaždic místo bílé díry.
- **Vnitřek panelu zatím jen odstavec s výzvou „Klikni na parcelu."** Jeho skutečná struktura je
  samostatné rozhodnutí, které padne až u kroku s info panelem — teď by se rozhodovalo naslepo.

**Stav repozitáře:** spustitelný, `localhost:8000` poprvé vrací stránku místo 404. Mapa se ještě
nevykresluje — `app.js` je prázdný a čeká na Tomáše. Necommitnuto: `docs/roadmap.md`,
`public/index.html`, `public/style.css`, `public/app.js`, `public/vendor/`.

---

## [2026-08-06 15:00] – Claude (psal kód a měřil) / Tomáš (zadal rozsah)

**Co se dělo:** Tomáš zadal „udělej to celé, rozhoduj se sám, maximálně se ptej na architekturu“
a jako cíl session „funkční kód, vše podle zadání a plánu splněno a zdokumentováno“. Dopsán
`public/app.js` (celá klientská logika), doladěn `MAX_BBOX_AREA` podle měření v prohlížeči,
ověřen celý tok v Chrome, aktualizováno README a přidán `docs/pruvodce-kodem.md`.

**Vědomé uvolnění Pravidla 1:** Tomáš výslovně zrušil povinnost odsouhlasit každé implementační
rozhodnutí předem a ponechal ji jen pro architekturu. Rozhodnutí učiněná v této session jsou
proto zapsaná **zpětně zde a v README**, ne odsouhlasená dopředu. Pravidlo 1 tím není zrušené
do budoucna, jen se na tuhle session neuplatnilo. Zapsáno na rovinu, protože „postup a
rozhodování“ je hodnocené kritérium a tahle změna je jeho součástí.

**Rozhodnutí a proč:**
- **`preferCanvas: true`.** Leaflet by jinak vytvořil jeden SVG uzel na parcelu. Změřeno:
  s canvasem se všech 17 256 parcel vykreslí za **402 ms**. Cena: polygony nejdou stylovat
  přes CSS — pro dva styly (běžná/vybraná parcela) nevadí.
- **Načítání na `moveend` s debounce 200 ms, bez cache, s `AbortController`.** Potvrzení
  rozhodnutí z 13:48 po měření. Cache by pomohla jen u malého posunu do už viděné oblasti;
  drahý případ (oddálení, skok jinam) je celý z nových dat.
- **Zvýraznění vybrané parcely drženo v `selectedRef` mimo vrstvu**, protože vrstva se při
  každém pohybu zahazuje a staví znovu. Styl se odvozuje funkcí `styleFor()` při každém
  vykreslení. Ověřeno: po posunu mapy zůstane vybraná parcela zvýrazněná.
- **Panel se staví přes `document.createElement` a `textContent`, ne skládáním `innerHTML`.**
  Data jdou z databáze do HTML; skládaný řetězec by byl prostor pro vložení cizího kódu.
- **Chybějící údaj se vypisuje jako „neuvedeno“.** Prázdné místo by vypadalo jako chyba
  aplikace. Testuje se i prázdný řetězec, ne jen `null` — `land_type_definition` se u části
  položek registru ČÚZK vrací prázdný (zjištění z 2026-08-05 15:38).
- **Výchozí zoom 17.** Při 16 by se na velmi široké obrazovce (ověřovací monitor má mapu
  2 199 px) hned po načtení ukázala jen výzva k přiblížení. Recenzent má vidět parcely.
- **`MAX_BBOX_AREA` zvýšeno z provizorních `0,0002` na `0,001`** — uzavření otevřeného bodu
  z 2026-08-05 15:38. Původní hodnota byla nastavená dřív, než existoval klient, a na velkém
  monitoru blokovala i zoom 17. Nová hodnota je doladěná měřením celého řetězce v prohlížeči
  (fetch + `JSON.parse` + vykreslení), ne odhadem.

**Naměřeno v prohlížeči (Chrome, localhost, mapa 2 199 × 1 308 px):**

| plocha | šířka | parcel | odpověď | fetch | parse | render | celkem |
|---|---|---|---|---|---|---|---|
| 2,0e-4 | 1,30 km | 1 331 | 0,55 MB | 12 | 2 | 32 | **46 ms** |
| 4,0e-4 | 1,84 km | 2 523 | 1,05 MB | 16 | 7 | 62 | **85 ms** |
| 1,0e-3 | 2,91 km | 5 669 | 2,36 MB | 26 | 21 | 162 | **209 ms** ← limit |
| 2,0e-3 | 4,11 km | 11 121 | 4,58 MB | 48 | 42 | 261 | **350 ms** |
| 1,35e-2 | 10,68 km | 17 256 | 6,75 MB | 65 | 91 | 402 | **559 ms** |

**Ověřeno v Chrome:** vykreslení parcel, klik → detail v panelu (`659541-st. 1584`,
`659541-985/47`), zachování zvýraznění po překreslení vrstvy, hláška `zoom_in` při velkém
výřezu a její zmizení po přiblížení, prázdná konzole po načtení. Chybové cesty API: `400` na
nesmyslný i převrácený `bbox`, `404` na neexistující parcelu i neznámý endpoint, `200` na `ref`
s mezerou a tečkou (`776530-st. 96/7`).

**Zjištění po cestě:**
- **První měření podle zoomu bylo neplatné** — `map.setView()` animuje a `getBounds()` vracel
  ještě starý výřez, takže všech šest úrovní zoomu vyšlo na stejnou plochu. Opraveno přes
  `{ animate: false }` a čekání. Připomínka, že měření se má nejdřív ověřit na tom, jestli
  vůbec měří to, co si myslíme.
- **Limit se neměří zoomem, ale plochou.** Stejný zoom znamená na jiném monitoru jiný výřez —
  ověřovací stroj má okno 2 552 × 1 308 CSS px, kde zoom 17 vydá `2,109e-4`, zatímco na
  notebooku 1366 px jen `5,3e-5`. Proto je limit na ploše, ne na zoomu.

**Stav repozitáře:** aplikace je kompletní a funkční od mapy po info panel. Necommitnuto:
`docs/roadmap.md`, `docs/pruvodce-kodem.md`, `README.md`, `src/parcels.php`, `public/index.html`,
`public/style.css`, `public/app.js`, `public/vendor/`.

**Zbývá:** ruční test ve **Firefoxu** (cíl #8 v `plan.md` vyžaduje Chrome i Firefox; ověřen zatím
jen Chrome) a commit — ten podle Pravidel 5 a 7 dělá Tomáš.

---

## [2026-08-06 16:41] – Tomáš (rozhodnutí) / Claude (zápis)

**Co se dělo:** Přerovnání dokumentace. Aplikační kód se neměnil. Podnět dal Tomáš: dokumentace
se špatně čte a roadmapa je na čtení moc dlouhá.

**Zjištění, které rozhodnutí spustilo:** dokumentace měla 1 873 řádků proti zhruba 1 000 řádkům
kódu — dvakrát delší než to, co dokumentuje. Přitom zadání žádá „**krátký** zápisník“. Samotná
`roadmap.md` má 1 125 řádků ve 28 záznamech; recenzent takový soubor nepřečte, přeskočí ho, a tím
se ztratí to nejcennější, co v něm je.

**Rozhodnutí a proč:**
- **Dokumentace rozvržena do tří vrstev podle čtenáře**, ne do jednoho velkého textu: README
  (musí stačit samo), `rozhodnuti.md` (proč), `roadmap.md` (doklad). Zamítnuto slučování do
  README — nafouklo by ho a zadání chce zápisník krátký.
- **Nový `docs/rozhodnuti.md` je tematický, ne chronologický.** Kapitoly Data, Úložiště, Import,
  API, Klient, Výkon, Hranice, Konvence; u každého rozhodnutí čtveřice *co / alternativy / proč /
  cena*. Důvod: chronologie odpovídá na otázku „kdy“, ale recenzent se ptá „proč“ — a odpověď na
  „proč“ je v logu rozsypaná přes 28 záznamů a tři dny.
- **`roadmap.md` zůstává beze změny obsahu, dostala jen hlavičku**, že je to podklad a číst se
  nemusí. Pravidlo 2 se tím neporušuje: žádný záznam se neupravil ani nesmazal, změnil se jen
  úvodní odstavec souboru. Zamítnuto roadmapu z repozitáře odebrat — je to jediný doklad, že
  rozhodnutí vznikala průběžně, ne že byla sepsaná zpětně na konci.
- **`docs/pruvodce-kodem.md` jde mimo repozitář** (přidán do `.gitignore`). Je to příprava Tomáše
  na pohovor — otázky a odpovědi, slabá místa, jak odpovědět na dotaz, kdo psal kód. To je
  koučovací materiál, ne dokumentace produktu; v odevzdaném repozitáři působí divně. Na disku
  zůstává.
- **Do README přidána tabulka „Kudy číst“** s jednou větou ke každému dokumentu. Bez ní by byla
  nová vrstva neviditelná — recenzent netuší, že `rozhodnuti.md` existuje.

**Stav repozitáře:** spustitelný, funkčnost beze změny. Necommitnuto: `.gitignore`, `README.md`,
`docs/roadmap.md`, nový `docs/rozhodnuti.md`. Odebrání průvodce ze sledování gitem musí ještě
provést Tomáš jedním příkazem (viz konverzace) — git operace podle Pravidel 5 a 7 dělá on.

**Otevřené, vědomě nedořešené:** sekce „Zápisník“ v README (173 řádků) se teď z velké části
překrývá s `rozhodnuti.md`. Zkrácení README na skutečně krátký zápisník s odkazem na tematický
dokument je samostatné rozhodnutí — probrat, ne udělat potichu.

---

## [2026-08-06 17:05] – Tomáš (rozhodnutí) / Claude (provedl)

**Co se dělo:** Dokončení přerovnání dokumentace z předchozího záznamu — uzavření bodu, který tam
byl zapsaný jako „otevřené, vědomě nedořešené“. Aplikační kód se neměnil.

**Rozhodnutí a proč:**
- **Sekce „Rozhodnutí a proč“ v README zkrácena ze 119 řádků na 45** — zůstalo šest rozhodnutí,
  která nejvíc určila výslednou podobu (předem stažená data, SQLite v repozitáři, limit výřezu,
  canvas renderer, čisté PHP + vanilla klient, chybějící vlastník), plus odkaz do
  `docs/rozhodnuti.md` na zbytek. Důvod: po vzniku tematického dokumentu byla ta samá rozhodnutí
  v repozitáři dvakrát, a zadání přitom žádá **krátký** zápisník. Celé README kleslo z 293 na
  219 řádků.
- **Tabulka měření limitu zkrácena na tři řádky** místo pěti — krajní hodnoty a zvolený limit
  stačí k pochopení, plná tabulka zůstává v `rozhodnuti.md` a v komentáři u konstanty.
- **Sekce „Co překvapilo“ ponechána celá.** Zadání ji jmenuje výslovně a její obsah se nikde
  jinde neopakuje — je to jediné místo, kde je zapsané, čím konkrétně matoucí byla data ČÚZK.
- **Sekce „Co bych udělal s víc časem“ ponechána** beze změny, má 11 řádků.

**Zjištění po cestě:** tohle byla **třetí** ztráta rozepsané práce tím, že měl Tomáš soubor
otevřený ve VS Code — editor si držel starší verzi a při uložení přepsal zápis na disku.
Ztraceny byly tabulka „Kudy číst“ v README, hlavička roadmapy a záznam z 16:41; musely se psát
znovu. Zavedené opatření: soubory, na kterých se právě pracuje, zůstávají v editoru zavřené.

**Stav repozitáře:** spustitelný, funkčnost beze změny. Necommitnuto: `.gitignore`, `README.md`,
`docs/roadmap.md`.
