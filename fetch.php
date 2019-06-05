<?php

function fetch_html($src) {
    $cachefile = './in/' . date('Ymd') . '_' . md5($src) . '.html';
    if(!file_exists($cachefile)) {
        echo "MUST LOAD: " . $src . " (" . $cachefile . ")<br />\n";
        $html = file_get_contents($src);
        $old_encoding = mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true);
        $html = mb_convert_encoding($html, 'UTF-8', $old_encoding);
        file_put_contents($cachefile, $html);
    }
    else {
        echo "FROM CACHE: " . $src . " (" . $cachefile . ")<br />\n";
    }

    $html = file_get_contents($cachefile);
    return $html;
}

?>
