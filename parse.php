<?php

require_once("util.php");

function fix_markup_errors($html) {
    $fix = array(
        '& ' => '&amp; ',
        '&Walnuss' => '&amp;Walnuss',
        '&gesalzen' => '&amp;gesalzen',
        ' &Co<' => ' &amp;Co<',
        '&Sirup<' => '&amp;Sirup<',
        '&Snacks<' => '&amp;Snacks<',
        '&Papayast' => '&amp;Papayast',
        'frisch&Fruchtig' => 'frisch&amp;Fruchtig',
        '&PannaCotta' => '&amp;PannaCotta',
        'W&S ' => 'W&amp;S ',
        '<5%Xantan' => '&lt;5%Xantan',
        '<5% nichtionische' => '&lt;5% nichtionische',
        '&Zubehör' => '&amp;Zubehör',
        '&hg=' => '&amp;hg=',
        '&p=' => '&amp;p=',
        '&partner=' => '&amp;partner=',
        '&ean=' => '&amp;ean=',
        'Limone &Verveine' => 'Limone &amp;Verveine'
    );

    return str_replace(array_keys($fix), array_values($fix), $html);
}

function polish_article($a) {
    $a->category = str_replace(",", "", $a->category);

    $a->unit = str_replace(",", ".", $a->unit);
    $a->single_price = str_replace(",", ".", $a->single_price);
    $a->tax = str_replace(",", ".", $a->tax);

    $tax = floatval($a->tax);
    if($a->tax != number_format($tax, 2)) die('PROBLEM');
    $a->tax = $tax;

    $price = $a->single_price;
    if(starts_with($price, '*')) {// price per kg
        $price = mb_trim(substr($price, 1));

        $price_per_1kg = floatval($price);
        if($price != number_format($price_per_1kg, 2)) die('PROBLEM');

        $unit_quantity = floatval(substr($a->unit, 0, -2));
        if($a->unit != $unit_quantity . 'kg') die('PROBLEM');

        $a->unit = '1kg';
        $a->unit_quantity = $unit_quantity;
        $a->single_price = $price_per_1kg;

        return $a;
    }

    $pattern = '/\A(\d+)x(\d.*)\z/';
    if(preg_match($pattern, $a->unit, $ms)) {
        $unit_quantity = intval($ms[1]);
        $unit = $ms[2];
        if($a->unit != $unit_quantity . 'x' . $unit) die('PROBLEM');

        $price_per_unit = floatval($price);
        if($price != number_format($price_per_unit, 2)) die('PROBLEM');

        $a->unit = $unit;
        $a->unit_quantity = $unit_quantity;
        $a->single_price = $price_per_unit;

        return $a;
    }

    $price_per_unit = floatval($price);
    if($price != number_format($price_per_unit, 2)) die('PROBLEM');

    $a->unit_quantity = 1;
    $a->single_price = $price_per_unit;

    return $a;
}

function polish_article_producer($producer_or_key) {
    $producer_or_key = mb_trim($producer_or_key);

    if(empty($producer_or_key) || !is_uppercase($producer_or_key))  {
        return $producer_or_key;
    }

    $key = $producer_or_key;
    $producers = $GLOBALS['producers'];

    if(isset($producers[$key])) {
        return $producers[$key];
    }

    $GLOBALS['missing_producer_keys'][] = $key;

    return $key;
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
    $article->producer = polish_article_producer($cells->item(7)->textContent);
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
    $html = fix_markup_errors($html);

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
