# HTML to sharedlist

See: [sharedlist](https://github.com/foodcoops/sharedlists)

## Environment settings

Datei `./env.php` mit folgendem Inhalt anlegen:

```
<?php

$kind_sources = array(
   'TAB1' => [ // maximal 6 Zeichen
       'https://url.de/abc1'
   ],
   'TAB2' => [
       'https://url.de/abc2',
       'https://url.de/abc3',
       'https://url.de/abc4'
   ]
);

$producers = [
    'APR' => 'Producer A',
    'BPR' => 'Producer B',
    'CPR' => 'Producer C'
];

```

## Todo

- [x] Warengruppe hinzufügen (done for CSV only)
- [x] Inverkehrbringer (Produzent) hinzufügen (done for CSV only)
- [ ] restrict to CSV output (drop support of BNN3 format)
