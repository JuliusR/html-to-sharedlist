<?php

function starts_with($haystack, $needle) {
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
}

function ends_with($haystack, $needle) {
    $length = strlen($needle);
    if($length == 0) return true;
    return substr($haystack, -$length) === $needle;
}

function mb_trim($str) {
    return preg_replace("/(^\s+)|(\s+$)/us", "", $str);
}

?>
