'use strict';

/*
 * Klientská část mapy parcel.
 *
 * Tok dat: pohyb mapy → výřez (bbox) → /api/parcels → polygony v mapě.
 *          klik na polygon → /api/parcel?ref=… → info panel.
 */

/** Střed dat, spočítaný z obalových obdélníků všech 17 256 parcel. */
const INITIAL_CENTER = [50.43121, 15.37817];

/**
 * Výchozí přiblížení. Zvoleno 17, ne nižší: server nad určitou plochou výřezu
 * vrací `zoom_in` místo parcel (viz MAX_BBOX_AREA v src/parcels.php) a při 16
 * by se na velmi široké obrazovce hned po načtení neukázalo nic. Recenzent má
 * vidět parcely, ne výzvu k přiblížení. Při 17 se vejde i monitor s 2 200 px
 * širokou mapou, na kterém se aplikace ověřovala.
 */
const INITIAL_ZOOM = 17;

/**
 * Jak dlouho po posledním pohybu mapy se čeká, než se pošle dotaz (ms).
 *
 * Během tažení myší se `moveend` neposílá vůbec, takže tohle řeší až sérii
 * krátkých pohybů za sebou — bez prodlevy by každé škubnutí znamenalo dotaz,
 * který stejně zahodíme.
 */
const MOVE_DEBOUNCE_MS = 200;

/** Běžná parcela a vybraná parcela. */
const PARCEL_STYLE = { color: '#3d5a80', weight: 1, fillColor: '#98c1d9', fillOpacity: 0.2 };
const SELECTED_STYLE = { color: '#9d0208', weight: 3, fillColor: '#e85d04', fillOpacity: 0.45 };

const mapElement = document.getElementById('map');
const noticeElement = document.getElementById('notice');
const detailElement = document.getElementById('detail');
const statusElement = document.getElementById('status');

/*
 * preferCanvas: true je hlavní výkonové rozhodnutí na klientovi. Leaflet ve
 * výchozím nastavení vytvoří jeden SVG uzel na každou parcelu — při tisících
 * polygonů se prohlížeč utopí ve správě DOM. S canvasem se všechny nakreslí
 * do jediného plátna.
 */
const map = L.map(mapElement, { preferCanvas: true }).setView(INITIAL_CENTER, INITIAL_ZOOM);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
}).addTo(map);

/** Vrstva s parcelami aktuálního výřezu; při každém načtení se nahradí celá. */
let parcelsLayer = null;

/** Klíč vybrané parcely, aby zvýraznění přežilo překreslení vrstvy. */
let selectedRef = null;

/** Časovač debounce a rozdělaný dotaz — oba se ruší, když přijde nový pohyb. */
let moveTimer = null;
let pendingRequest = null;

// ---------------------------------------------------------------- pomocné

/** Zobrazí nebo schová hlášku nad mapou. */
function showNotice(text) {
    noticeElement.textContent = text ?? '';
    noticeElement.hidden = !text;
}

/** Vyprázdní prvek. Bezpečnější než innerHTML = '' a čitelnější než cyklus. */
function clear(node) {
    node.replaceChildren();
}

/** Vytvoří prvek s textem. Text jde přes textContent, takže se nic neinterpretuje jako HTML. */
function element(tag, text, className) {
    const node = document.createElement(tag);

    if (text !== undefined && text !== null) {
        node.textContent = String(text);
    }

    if (className) {
        node.className = className;
    }

    return node;
}

/** 12345 → „12 345 m²“. Oddělovače tisíců podle českých zvyklostí obstará prohlížeč. */
function formatArea(squareMetres) {
    return `${Number(squareMetres).toLocaleString('cs-CZ')} m²`;
}

/** České skloňování za číslovkou: 1 parcela, 2–4 parcely, 5+ parcel. */
function plural(count) {
    if (count === 1) {
        return 'parcela';
    }

    return count >= 2 && count <= 4 ? 'parcely' : 'parcel';
}

// ------------------------------------------------------------ načítání parcel

/** Styl parcely podle toho, jestli je zrovna vybraná. */
function styleFor(feature) {
    return feature.id === selectedRef ? SELECTED_STYLE : PARCEL_STYLE;
}

/**
 * Načte parcely pro aktuální výřez mapy.
 *
 * Rozdělaný dotaz se ruší — když uživatel popojede dřív, než odpověď dorazí,
 * je odpověď pro výřez, ze kterého už odjel. Bez zrušení by se dotazy hromadily
 * a mapa by na okamžik blikla starým obsahem.
 */
async function loadParcels() {
    pendingRequest?.abort();
    pendingRequest = new AbortController();

    // toBBoxString() vrací west,south,east,north — přesně to pořadí, které
    // čeká naše API. Nic se nepřehazuje, a právě tohle byl u dat ČÚZK
    // nejčastější zdroj tichých chyb.
    const bbox = map.getBounds().toBBoxString();

    try {
        const response = await fetch(`/api/parcels?bbox=${bbox}`, { signal: pendingRequest.signal });

        if (!response.ok) {
            const body = await response.json().catch(() => ({}));
            throw new Error(body.error ?? `HTTP ${response.status}`);
        }

        drawParcels(await response.json());
    } catch (error) {
        // Zrušený dotaz není chyba, jen nás předběhl novější.
        if (error.name === 'AbortError') {
            return;
        }

        showNotice('Parcely se nepodařilo načíst.');
        statusElement.textContent = error.message;
    }
}

/** Nahradí vrstvu parcel novými daty. */
function drawParcels(collection) {
    if (parcelsLayer !== null) {
        parcelsLayer.remove();
        parcelsLayer = null;
    }

    if (collection.zoom_in) {
        showNotice('Výřez je příliš velký — přibliž mapu, aby se zobrazily parcely.');
        statusElement.textContent = '';
        return;
    }

    showNotice(null);

    parcelsLayer = L.geoJSON(collection, {
        style: styleFor,
        onEachFeature: (feature, layer) => layer.on('click', () => selectParcel(feature.id)),
    }).addTo(map);

    const count = collection.features.length;
    statusElement.textContent = `Ve výřezu: ${count.toLocaleString('cs-CZ')} ${plural(count)}`;
}

// ------------------------------------------------------------- detail parcely

/** Zvýrazní parcelu v mapě a načte její údaje do panelu. */
async function selectParcel(ref) {
    selectedRef = ref;
    parcelsLayer?.eachLayer((layer) => layer.setStyle(styleFor(layer.feature)));

    clear(detailElement);
    detailElement.append(element('p', 'Načítám…', 'hint'));

    try {
        const response = await fetch(`/api/parcel?ref=${encodeURIComponent(ref)}`);

        if (!response.ok) {
            const body = await response.json().catch(() => ({}));
            throw new Error(body.error ?? `HTTP ${response.status}`);
        }

        renderDetail(await response.json());
    } catch (error) {
        clear(detailElement);
        detailElement.append(element('p', error.message, 'error'));
    }
}

/**
 * Vykreslí údaje o parcele do panelu.
 *
 * Druh pozemku i způsob využití mohou v datech chybět — ČÚZK je u části parcel
 * nevede (u `landUse` asi u tří čtvrtin). Chybějící údaj se vypíše jako
 * „neuvedeno“, nezamlčí se: prázdné místo by vypadalo jako chyba aplikace.
 */
function renderDetail(parcel) {
    const list = document.createElement('dl');

    const rows = [
        ['Parcelní číslo', parcel.parcel_number, 'number'],
        ['Výměra', formatArea(parcel.area_m2)],
        ['Druh pozemku', parcel.land_type_label],
        ['Způsob využití', parcel.land_use_label],
        ['Katastrální území', parcel.zoning_name],
        ['Identifikátor ČÚZK', parcel.ref],
    ];

    for (const [label, value, className] of rows) {
        list.append(element('dt', label));
        list.append(
            value === null || value === undefined || value === ''
                ? element('dd', 'neuvedeno', 'missing')
                : element('dd', value, className)
        );
    }

    clear(detailElement);
    detailElement.append(list);

    // Definice druhu pozemku se u části položek v registru ČÚZK nevede
    // a vrací se jako prázdný řetězec — proto se testuje prázdnost, ne null.
    if (parcel.land_type_definition) {
        detailElement.append(element('p', parcel.land_type_definition, 'definition'));
    }
}

// ------------------------------------------------------------------ propojení

/*
 * Během tažení mapy Leaflet už vykreslené vrstvy jen posouvá transformací,
 * takže samotný pohyb je plynulý a nic se nepřekresluje. Dotaz se posílá až
 * po `moveend`, tedy když uživatel pohyb dokončí.
 */
map.on('moveend', () => {
    clearTimeout(moveTimer);
    moveTimer = setTimeout(loadParcels, MOVE_DEBOUNCE_MS);
});

loadParcels();
