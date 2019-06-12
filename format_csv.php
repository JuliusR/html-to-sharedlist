<?php

function sanitize_csv($str, $maxlen=255) {
    $str = mb_substr($str, 0, $maxlen);

    $fix = array(
        '"' => '""'
    );

    return str_replace(array_keys($fix), array_values($fix), $str);
}

function format_csv_header() {
    $cols = array();
    for($i=0; $i<13; ++$i) $cols[$i] = '';

    $cols[0] = '"Status"';
    $cols[1] = '"Bestellnummer"';
    $cols[2] = '"Name"';
    $cols[3] = '"Notiz"';
    $cols[4] = '"Produzent"';
    $cols[5] = '"Herkunft"';
    $cols[6] = '"Einheit"';
    $cols[7] = '"Nettopreis"';
    $cols[8] = '"MwSt"';
    $cols[9] = '"Pfand"';
    $cols[10] = '"Gebindegröße"';
    $cols[11] = '"(geschützt)"';
    $cols[12] = '"(geschützt)"';
    $cols[13] = '"Kategorie"';

    return join(";", $cols) . PHP_EOL;
}

function format_csv_article($a) {
    $cols = array();
    for($i=0; $i<13; ++$i) $cols[$i] = '';

    $cols[0] = '"' . sanitize_csv($a->status) . '"';
    $cols[1] = '"' . sanitize_csv($a->id) . '"';
    $cols[2] = '"' . sanitize_csv($a->name) . '"';
    $cols[3] = '"' . sanitize_csv($a->note) . '"';
    $cols[4] = '"' . sanitize_csv($a->producer) . '"';
    $cols[5] = '"' . sanitize_csv($a->origin) . '"';
    $cols[6] = '"' . sanitize_csv($a->unit) . '"';
    $cols[7] = number_format($a->single_price, 2, ",", "");
    $cols[8] = number_format($a->tax, 2, ",", "");
    $cols[9] = number_format($a->deposit, 2, ",", "");
    $cols[10] = $a->unit_quantity;
    $cols[11] = '';
    $cols[12] = '';
    $cols[13] = '"' . sanitize_csv($a->category) . '"';

    return join(";", $cols) . PHP_EOL;
}

function format_csv($articles) {
    $csv = format_csv_header();
    foreach($articles as $a) {
        $csv .= format_csv_article($a);
    }
    return $csv;
}

?>
