<?php

date_default_timezone_set('Europe/Berlin');
setlocale(LC_ALL, 'de');

require_once("env.php");
require_once("fetch.php");
require_once("parse.php");
require_once("format.php");

$missing_producer_keys = [];

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
    $bnn = format_bnn($articles);

    $bnn = mb_convert_encoding($bnn, 'CP850', 'UTF-8');
    file_put_contents('out/PL' . $kind . '.BNN', $bnn);

    if(count($missing_producer_keys) > 0) {
        echo "WARNING: possibly missing producer keys.<br />\n";
        var_dump($missing_producer_keys);
        echo "<br />\n";
        $missing_producer_keys = [];
    }
}

?>
