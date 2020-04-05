<?php

// THIS IS NOT FINISHED BUT IS UNIT-TESTED TO BE A GOOD START

// This is a service checker which will write to 'data' in the
// site directory about what services are online. This data
// is for picosite to then display this information.

$_config_hc_srvclist = "servicelist.txt";
$_config_hc_srvcdata = "data/";
$_config_hc_redirlim = 5;

if (file_exists("settings.php")) include_once("settings.php");

define("SVCLIST", "$_config_srvclist");
define("SVCDATA", "$_config_srvcdata");

global $redir_count;
$redir_count = 0;

function transEnd($instring, $substring) {
        $length = strlen($substring);
        return $length === 0 || (substr($instring, -$length) === $substring);
}

function checkURL($url) {
	return filter_var($url, FILTER_VALIDATE_URL);
}

function checkServices($services, $base = "") {
	$online = [];
	$elsewhere = [];
	foreach ($services as $svc) {
		$curl = curl_init($svc);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_TIMEOUT,10);
		$output = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($httpcode === 200 || $httpcode === 302) {
			array_push($online, "$svc|$base");
		if ($httpcode === 301) {
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$headers = substr($output, 0, $header_size);
			$headers = explode("\n", $headers);
			foreach ($headers as $header) {
				$headparts = explode(":", $header);
				if ($headparts[0] === "Location" && count($headparts) === 3) {
					$url = trim($headparts[1] . ":" . $headparts[2]);
					if (!checkURL($url)) continue;
					array_push($elsewhere, $url);
					array_merge($online, checkServices($elsewhere, $url));
				}
			}
		}
	}
	
	return array_unique($online);
}

function getServiceList() {
	$svclist = file_get_contents(SVCLIST);
	$svclist = explode("\n", $svclist);
	return $svclist;
}

function getServDataLocation() {
	$files = scandir(SVCDATA);
	$files = sort($files);

	// bootstrapping shit...
	if (count($files)) === 0) return 1;
	if (count($files)) === 1) {
		$filenameparts = explode(".", $files[0]);
		return intval($filenameparts[0]);
	}

	$fileold = 0;
	$filefin = 0;
	foreach ($files as $file) {
		$filenameparts = explode(".", $file);

		$filenum = intval($filenameparts[0]);
		if ($filenum > $fileold) $filefin = $filenum
		$fileold = $filenum;
	}

	$filefin++;

	return $filefin;
}

function writeGoodServices($services) {
	$services = checkServices($services);
	$file = strval(getServDataLocation()) . ".dat";

	$f = fopen($file, 'a');
	foreach ($services as $svc) fwrite($f, "$svc\n");
	fwrite($f, "!!READY!!");
}

function cleanServiceData() {
