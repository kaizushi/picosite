<?php

define("DATADIR", "data");
define("BASECUR", "USD");
#define("BASECUR", "EUR");

function getpriceBTC() {
        $json = file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD");
        $data = json_decode($json, TRUE);
        $price = (float) $data['USD'];
        return round($price,3);
}

function getpriceXMR() {
        $json = file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=XMR&tsyms=USD");
        $data = json_decode($json, TRUE);
        $price = (float) $data['USD'];
        return round($price,3);
}

file_put_contents(DATADIR . "/btc-price", getpriceBTC());
file_put_contents(DATADIR . "/xmr-price", getpriceXMR());

?>
