<?php

// This is a service checker which will write to 'data' in the
// site directory about what services are online. This data
// is for picosite to then display this information.

define("SVCLIST", "/home/kaizushi/servicelist.txt");
define("SVCUP", "/var/www/user/kaizushi/kaizushigdv5mrnz.onion/data/svcup.txt");

function getServices() {
	if (!file_exists(SVCLIST)) return [];
	$services = file_get_contents(SVCLIST);
	return explode("\n", $services);
}

function checkServices($services) {
	$online = [];
	foreach ($services as $svc) {
		$curl = curl_init($svc);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_TIMEOUT,10);
		$output = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		echo "Debug: HTTP code: $httpcode type " . gettype($httpcode) . "\n";
		if ($httpcode === "200") {
			$online.array_push($svc);
		}
	}

	return $online;
}

$services = ["http://kaizushigdv5mrnz.onion", "http://dhfndjfoenclduie.onion", "http://kaizushih5iec2mxohpvbt5uaapqdnbluaasa2cmsrrjtwrbx46cnaid.onion/"];
checkServices($services);
