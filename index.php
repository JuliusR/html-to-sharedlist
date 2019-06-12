<?php

date_default_timezone_set('Europe/Berlin');
setlocale(LC_ALL, 'de');

require_once("env.php");
require_once("fetch.php");
require_once("parse.php");
require_once("format_bnn.php");
require_once("format_csv.php");

foreach($kind_sources as $kind => $sources) {
    $articles = array();
    foreach($sources as $src) {
        // fetch
        $html = fetch_html($src);

        // parse
        $articles = array_merge(
            $articles,
            parse_articles($html)
        );
    }

    // format
    $csv = format_csv($articles);
    file_put_contents('out/' . $kind . '.csv', $csv);

    // format
    $bnn = format_bnn($articles);
    $bnn = mb_convert_encoding($bnn, 'CP850', 'UTF-8');
    file_put_contents('out/PL' . $kind . '.BNN', $bnn);
}
