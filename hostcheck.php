<?php

// THIS IS NOT FINISHED BUT IS UNIT-TESTED TO BE A GOOD START

// This is a service checker which will write to 'data' in the
// site directory about what services are online. This data
// is for picosite to then display this information.

$_config_hc_srvclist = "servicelist.txt";
$_config_hc_srvcdata = "data/services/";
$_config_hc_redirlim = 5;

if (file_exists("settings.php")) include_once("settings.php");

define("SVCLIST", "$_config_hc_srvclist");
define("SVCDATA", "$_config_hc_srvcdata");

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
	foreach ($services as $svc) {
		echo "Checking service $svc\n";
		$svc = trim(str_replace("\n", "", $svc));
		$elsewhere = [];
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_TIMEOUT,10);
		curl_setopt($curl, CURLOPT_URL,$svc);
		$output = curl_exec($curl);
		echo "curl output: $output\n";
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		echo "HTTP code: $httpcode\n";
		$error = curl_error($curl);
		echo "Curl error: $error\n";
		if ($httpcode === 200) {
			echo "Response: 200 OK\n";
			if ($base === "") $base = $svc;
			array_push($online, "$svc|$base");
			curl_close($curl);
			continue;
		}
		if (($httpcode === 301) OR ($httpcode === 302)) {
			echo "Response: 301 or 302\n";
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$headers = substr($output, 0, $header_size);
			$headers = explode("\n", $headers);
			curl_close($curl);
			foreach ($headers as $header) {
				$headparts = explode(" ", $header);
				if ($headparts[0] === "Location:") {
					$url = $headparts[1];
					echo "Checking redirect $url\n";
					$arurl = []; array_push($arurl, $url);
					array_push($elsewhere, checkServices($arurl, $svc));
					array_merge($online, $elsewhere);
				}
			}
		}
	}
	
	return $online;
}

function getServiceList() {
	$svclist = file_get_contents(SVCLIST);
	$svclist = explode("\n", $svclist);
	return $svclist;
}

function getServDataLocation() {
	$files = scandir(SVCDATA);

	$fileold = 0;
	$filefin = 0;
	foreach ($files as $file) {
		if ($file === "." || $file === "..") continue;
		$fparts = explode(".", $file);

		$filenum = intval($fparts[0]);
		if ($filenum > $fileold) $filefin = $filenum;
		$fileold = $filenum;
	}

	$filefin++;

	return $filefin;
}

function writeGoodServices($services) {
	$file = strval(SVCDATA . getServDataLocation()) . ".dat";

	$f = fopen($file, 'a');
	foreach ($services as $svc) fwrite($f, "$svc\n");
	fwrite($f, "!!READY!!");
}

function hostcheck() {
	$services = getServiceList();
	$services = checkServices($services);
	writeGoodServices($services);
}

hostcheck();

?>
