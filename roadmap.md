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

## Co dál (další session)

- Při reálném stažení CPX dat: ověřit `srsName=EPSG:4326` funguje, a vizuálně potvrdit, že zvolená
  4 k.ú. skutečně na sebe navazují.
- Rozhodnout schéma unikátního klíče parcely v SQLite (k.ú. + parcelní číslo + typ parcely).
- Až poté začít implementace — podle `CLAUDE.md` s odsouhlasením každého implementačního
  rozhodnutí předem.
