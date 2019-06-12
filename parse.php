<?php

require_once("util.php");

function prepare_markup($html) {
    // replace `&` by `&amp;` if the ampersand is not part of a
    // [named|decimal|hex] character reference
    //
    // https://html.spec.whatwg.org/multipage/syntax.html#character-references
    $dec = '(?:#\d+)';
    $hex = '(?:#[xX][a-fA-F0-9]+)';
    $named = '(?:[A-Za-z]+)';// https://html.spec.whatwg.org/entities.json
    $charref = '(?:' . join('|', [$dec, $hex, $named]) . ')';

    $ampersand = '&(?!' . $charref . ';)';
    $html = preg_replace("@$ampersand@", '&amp;', $html);

    // replace `<` by `&lt;` if the less-than sign is neither part of
    // the doctype nor part of an HTML tag
    //
    // doctype starts with `!`; HTML tags start with a letter or `/`
    $lt = '<(?![a-zA-Z/!])';
    $lt_analyze = '.{0,20}' . $lt . '.{0,20}';// print some context in warning

    preg_match_all("@$lt_analyze@", $html, $ms);
    $expected_lt_count = count($ms[0]);
    if($expected_lt_count >= 1) {
        echo "WARNING: replacing some less-than symbols.<br />\n";
        var_dump($ms[0]);
    }
    $html = preg_replace("@$lt@", '&lt;', $html, -1, $real_lt_count);
    if($expected_lt_count !== $real_lt_count) {
        die('ERROR: I messed up with the less-than symbols.');
    }

    return $html;
}

function polish_article_with_kg_price($a) {
    if(!starts_with($a->single_price, '*')) die('PROBLEM');
    $a->single_price = mb_trim(substr($a->single_price, 1));

    $price = str_replace(',', '.', $a->single_price);
    $price = floatval($price);
    if($a->single_price != number_format($price, 2, ',', '')) die('PROBLEM');

    $a->unit = str_replace(',', '.', $a->unit);
    $unit_quantity = floatval(substr($a->unit, 0, -2));
    if($a->unit != $unit_quantity . 'kg') die('PROBLEM');

    $a->unit = '1kg';
    $a->unit_quantity = $unit_quantity;
    $a->single_price = $price;

    return $a;
}


function polish_article($a) {
    $a->category = str_replace(",", "", $a->category);

    $total_price = str_replace(",", ".", $a->total_price);
    $total_price = floatval($total_price);
    if($a->total_price != number_format($total_price, 2, ',', '')) die('PROBLEM');
    $a->total_price = $total_price;

    $tax = str_replace(",", ".", $a->tax);
    $tax = floatval($tax);
    if($a->tax != number_format($tax, 2, ',', '')) die('PROBLEM');
    $a->tax = $tax;

    if(starts_with($a->single_price, '*')) {
        return polish_article_with_kg_price($a);
    }

    $a->unit_quantity = 1;
    $a->single_price = $a->total_price / (1. + $a->tax/100.);

    $pattern = '/\A(\d+)x(\d.*)\z/';
    if(preg_match($pattern, $a->unit, $ms)) {// [unit_quantity]x[unit]
        $unit_quantity = intval($ms[1]);
        $unit = $ms[2];
        if($a->unit != $unit_quantity . 'x' . $unit) die('PROBLEM');

        $a->unit = $unit;
        $a->unit_quantity = $unit_quantity;
        $a->single_price = $a->single_price / $unit_quantity;
    }

    $a->unit = str_replace(',', '.', $a->unit);// yes, it is a mess... (currently)

    return $a;
}

function parse_single_article($dom, $xpath, $tr) {
    $article = (object)[];

    $article->status = '';
    $article->deposit = '';
    $article->unit_quantity = '';

    $selector = 'ancestor::div[1]/preceding-sibling::h3[1]';
    $h3 = $xpath->query($selector, $tr)->item(0);
    $article->category = $h3->textContent;

    $cells = $xpath->query('td', $tr);
    $article->id = $cells->item(1)->textContent;
    $article->name = $cells->item(3)->textContent;
    $article->unit = $cells->item(5)->textContent;
    $article->single_price = $cells->item(7)->textContent;
    $article->tax = $cells->item(9)->textContent;
    $article->total_price = $cells->item(11)->textContent;

    $selector = 'following-sibling::tr[@class="detailbox"][1]';
    $details = $xpath->query($selector, $tr)->item(0);
    $id_version = $details->getAttribute('id');

    if(strpos($id_version, $article->id) !== 0) die('id mismatch.');

    $article->version = substr($id_version, strlen($article->id));

    $cells = $xpath->query('.//tr//td', $details);
    $article->quality = $cells->item(3)->textContent;
    $article->producer = $cells->item(7)->textContent;
    $article->origin = $cells->item(12)->textContent;
    $article->additives = $cells->item(16)->textContent;
    $article->note = $cells->item(21)->textContent;
    $article->ingredients = $cells->item(25)->textContent;

    foreach($article as $k => $v) {
        $article->$k = mb_trim($v);
    }

    return polish_article($article);
}

function parse_articles($html) {
    $html = prepare_markup($html);

    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $article_rows = $xpath->query('//div[@id="accordion1"]//a/ancestor::tr');

    $articles = array();
    foreach($article_rows as $tr) {
        $articles[] = parse_single_article($dom, $xpath, $tr);
    }

    return $articles;
}

?>
