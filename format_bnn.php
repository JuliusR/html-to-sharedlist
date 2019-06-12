<?php

require_once("format_csv.php");

function get_bnn_tax_key($tax) {
    // Mehrwertsteuer 1=reduziert 2=voll 3=LandwirtsSatz
    if($tax == 7) return 1;
    if($tax == 19) return 2;
    if($tax == 10.7) return 3;

    echo "WARNING: unknown tax $tax\n";
    return 0;
}

function format_bnn_header($now = false) {
    if($now === false) $now = time();

    $cols = array();
    for($i=0; $i<12; ++$i) $cols[$i] = '';

    $cols[0] = 'BNN';// BNN als Kennung des Dateityps
    $cols[1] = '3';// 3 als Versionsnummer der Schnittstelle
    $cols[2] = '0';// 0=AscII 1=Ansi (IN FACT, WE USE CP437!!!)
    $cols[3] = '"' . sanitize_csv('Antakya (improvisierte Schnittstelle)') . '"';
    $cols[4] = 'T';// V=vollständige Preisliste, T=Teilliste, S=Sonderliste
    $cols[5] = '"' . sanitize_csv('Käse') . '"';
    $cols[6] = 'EUR';// Währung banküblich (DEM=DM, ATS=österr.Schilling, EUR=Euro)
    $cols[7] = '';// Preise gültig ab JJJJMMTT
    $cols[8] = '0';// gültig bis, 0=unbestimmt
    $cols[9] = date('Ymd', $now);// Datum der Datei-Erstellung JJJJMMTT
    $cols[10] = date('Gi', $now);// Uhrzeit der Datei-Erstellung SSMM
    $cols[11] = '1';// Angabe der Dateinummer, Datei/Diskette1 = 1, ...

    return join(";", $cols) . PHP_EOL;
}

function format_bnn_footer() {
    $cols = array();
    for($i=0; $i<3; ++$i) $cols[$i] = '';

    $cols[0] = '';// leer
    $cols[1] = '';// leer
    $cols[2] = '99';// Dateinummer der Folgedatei (bei Dateiende = 99)

    return join(";", $cols) . PHP_EOL;
}

function format_bnn_article($a) {
    $fmt = new NumberFormatter("de-DE", NumberFormatter::DECIMAL);
    $fmt->setAttribute(NumberFormatter::GROUPING_USED, false);
    $fmt->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);
    $fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 100);

    $unit_quantity = $fmt->format($a->unit_quantity);

    $cols = array();
    for($i=0; $i<69; ++$i) $cols[$i] = '';

    $cols[0] = '"' . sanitize_csv($a->id) . '"';
    $cols[1] = 'A';// Änderungskennung
    $cols[2] = '0';// ÄnderungsDatum
    $cols[3] = '0';// ÄnderungsZeit
    $cols[4] = '0';// EANladen
    $cols[5] = '0';// EANbestell
    $cols[6] = '"' . sanitize_csv(substr($a->name, 0, 50)) . '"';
    $cols[7] = '"' . sanitize_csv(substr($a->note, 0, 50)) . '"';

    // TODO: solve producer key
    // https://github.com/foodcoops/sharedlists/blob/81edeb4445bd80187ff12c4151dd900e1f43bf75/lib/article_import/bnn_codes.yml#L51
    // page 112 of http://bioantakya.de/Katalog.pdf

    //$cols[10] = '"' . get_bnn_producer_key($a->producer) . '"';
    $cols[10] = '';

    $cols[12] = '"' . sanitize_csv(substr($a->origin, 0, 3)) . '"';
    $cols[13] = '"' . sanitize_csv(substr($a->quality, 0, 4)) . '"';

    $cols[15] = '0';// MHD-Restlaufzeit

    // TODO: solve handling of article category
    // https://github.com/foodcoops/sharedlists/blob/81edeb4445bd80187ff12c4151dd900e1f43bf75/lib/article_import/bnn_codes.yml#L829

    $cols[16] = '0';// Warengruppe BNN
    $cols[17] = '0';// Warengruppe Institut für Handelsforschung
    $cols[18] = '0';// Warengruppe des jeweiligen Großhändlers

    $cols[20] = '1';// MinBestellMenge
    $cols[21] = '"' . sanitize_csv(substr($unit_quantity . ' x ' . $a->unit, 0, 15)) . '"';
    $cols[22] = $unit_quantity;
    $cols[23] = '"' . sanitize_csv(substr($a->unit, 0, 10)) . '"';
    $cols[24] = 1;// Faktor zur Menge-Preis-Relation Ladeneinheit (SIC!!!)

    $cols[28] = '0';// GewichtLadeneinheit
    $cols[29] = '0';// GewichtBestelleinheit
    $cols[30] = '0';// Breite
    $cols[31] = '0';// Höhe
    $cols[32] = '0';// Tiefe
    $cols[33] = get_bnn_tax_key($a->tax);
    $cols[34] = '0';// VkFestpreis
    $cols[35] = '0';// EmpfVk
    $cols[36] = '0';// EmpfVkGH
    $cols[37] = number_format($a->single_price, 2, ",", "");
    $cols[38] = 'N';// rabattfähig
    $cols[39] = 'N';// skontierfähig
    $cols[40] = '0';// StaffelMenge1
    $cols[41] = '0';// StaffelPreis1
    $cols[42] = 'N';// rabattfähig1
    $cols[43] = 'N';// skontierfähig1
    $cols[44] = '0';// StaffelMenge2
    $cols[45] = '0';// StaffelPreis2
    $cols[46] = 'N';// rabattfähig2
    $cols[47] = 'N';// skontierfähig2
    $cols[48] = '0';// StaffelMenge3
    $cols[49] = '0';// StaffelPreis3
    $cols[50] = 'N';// rabattfähig3
    $cols[51] = 'N';// skontierfähig3
    $cols[52] = '0';// StaffelMenge4
    $cols[53] = '0';// StaffelPreis4
    $cols[54] = 'N';// rabattfähig4
    $cols[55] = 'N';// skontierfähig4
    $cols[56] = '0';// StaffelMenge5
    $cols[57] = '0';// StaffelPreis5
    $cols[58] = 'N';// rabattfähig5
    $cols[59] = 'N';// skontierfähig5

    $cols[62] = '';// AktionspreisGültigAb (leer = ab sofort)
    $cols[63] = '';// AktionspreisGültigBis (leer = unbestimmt)
    $cols[64] = '';// Aktions-VK-Vorschlag incl. MWSt.
    $cols[65] = '"' . sanitize_csv(substr($a->unit, 0, 10)) . '"';
    $cols[66] = 1;// Mengen-Faktor der Grundpreiseinheit zur Ladeneinheit (SIC!!!)

    return join(";", $cols) . PHP_EOL;
}

function format_bnn($articles) {
    $bnn = '';

    $bnn .= format_bnn_header();
    foreach($articles as $a) $bnn .= format_bnn_article($a);
    $bnn .= format_bnn_footer();

    return $bnn;
}

?>
