<?php

date_default_timezone_set('Europe/Berlin');
setlocale(LC_ALL, 'de');

require_once("env.php");
require_once("fetch.php");
require_once("parse.php");
require_once("format_bnn.php");
require_once("format_csv.php");

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>HTML2Sharedlists</title>
    <style type="text/css">input { font-size: 500%; }</style>
  </head>
  <body>
    <form method="post">
      <fieldset>
	<input type="hidden" name="refresh" value="refresh" />
	<input type="submit" value="Refresh" onclick="this.disabled = true; this.value='Refreshing...';" />
      </fieldset>
    </form>
<?php

if(isset($_POST['refresh']) || isset($_GET['always_refresh'])) {
    echo '<pre>';

    $missing_producer_keys = [];
    $article_write_counts = [];

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

        $article_write_counts[$kind] = count($articles);

        if(count($missing_producer_keys) > 0) {
            echo "WARNING: possibly missing producer keys.<br />\n";
            var_dump($missing_producer_keys);
            echo "<br />\n";
            $missing_producer_keys = [];
        }
    }
    echo '</pre>';

    echo '<h1>Refresh succeeded for:</h1>';
    echo '<dl>';
    foreach($article_write_counts as $kind => $count) {
        echo "<dt>$kind</dt><dd>$count articles</dd>";
    }
    echo '</dl>';
}
?>
  </body>
</html>
