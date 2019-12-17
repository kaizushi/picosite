<?php

// picosite is almost a CMS, its a light way to have a site.

// DO NOT CHANGE THIS FILE OR YOU WILL BREAK UPDATES.

//default settings

$_config_softname = "picosite 1.2.0";
$_config_debugout = false;
$_config_traceoff = true;
$_config_sitename = "A New Picosite";
$_config_sitelogo = "sitelogo.png";
$_config_suteauth = "Anonymous";
$_config_currsymb = "$";

// You can change any of these settings by creating a settings.php
// and declaring the variables there.

if (file_exists("settings.php")) include_once("settings.php");
if (file_exists("parser.php")) include_once("parser.php");

define("SOFTNAME", $_config_softname);
define("DEBUGOUT", $_config_debugout);
define("TRACEOFF", $_config_traceoff);
define("SITENAME", $_config_sitename);
define("SITELOGO", $_config_sitelogo);
define("SITEAUTHOR", $_config_siteauth);
define("CURRENCY_SYM", $_config_currsymb);

if (isset($argv[1])) $_GET["q"] = $argv[1];

global $HIDEPAGE;
$HIDEPAGE = false;

function debugMethodName(){
	if (TRACEOFF) return;
	$e = new Exception();
	$trace = $e->getTrace();
	//position 0 would be the line that called this function so we ignore it
	$last_call = $trace[1];
	print_r($last_call);
}

function getBool($val) {
	if ($val) return "true";
	else return "false";
}

function printDebug($msg) {
	if (DEBUGOUT) {
		echo "picosite DEBUG: " . $msg . "\n";
		file_put_contents("debug.log", $msg . "\n", FILE_APPEND | LOCK_EX);
		echo "<br>";
	}
}

function printDebugArray($msg, $array) {
	printDebug($msg);
        foreach ($array as $key => $value) printDebug($key . " = " . $value);
}

function transSecureSysName($string) {
	//dumb method name
	$newstring = preg_replace("/[^A-Za-z0-9.-]/","",$string);
	return $newstring;
}


function transStart($instring, $substring) {
	$length = strlen($substring);
	return (substr($instring, 0, $length) === $substring);
}

function transEnd($instring, $substring) {
	$length = strlen($substring);
	return $length === 0 || (substr($instring, -$length) === $substring);
}

function transStripNode($nodes) {
	$stripped = [];
	foreach ($nodes as $key => $node) {
		$nodesplit = explode('/', $node);
		$snode = $nodesplit[1];
		$snodesplit = explode('.', $snode);
		$node = $snodesplit[0];
		$stripped[$key] = $node;
	}
	return $stripped;
}

function getLinkMain() {
	// this method makes a 'back to main page' link
	// and preserves the selected language
	$link = "/page.php";

	if (isset($_GET['l'])) $link = $link + "?l=" . $_GET['l'];

	return $link;
}

function getPageTitles($files, $subpage = "[NONE]") {
	$newFiles = [];


	foreach ($files as $file) {
		$paths = explode("/", $file);
			

		if (count($paths) == 2) {
			$file = $paths[1];
			$dir = $paths[0];
		}

		$lang = NULL;
		$inslang = "";
		$fn = NULL;

		$parts = explode('.', $file);
		$title = $parts[0];

		
		if ((transEnd($file, ".blog.page") && $dir === "blogs") ||
		    (transEnd($file, ".guide.page") && $dir === "guides") ||
		    (transEnd($file, ".sub.page") && $dir === $subpage)) {
		    	if (count($parts) == 3) {
				$type = $parts[1];
			} elseif ( count($parts) == 5) {
				$type = $parts[3];
			}
		} elseif (transEnd($file, ".page") && $dir === ".") {
			$type = "page";
	
		}

		if (($subpage === "[NONE]") && $type === "sub") {
			continue;
		}

		if (!is_null($_GET['l'])) {
			$lang = $_GET['l'];
			$inslang = "." . $lang . ".trans";
		}
			
		if ($type === "blog") $fn = "blogs/" . $title . $inslang . ".blog.page";
		if ($type === "guide") $fn = "guides/" . $title . $inslang . ".guide.page";
		if ($type === "sub") $fn = $subpage . "/" . $title . $inslang .  ".sub.page";
		
		if ((($type === "page") && transEnd($file, ".page")))
			$fn = $title . $inslang . ".page";
		
		if (!is_null($fn)) { 
			array_push($newFiles, $fn);
		}
	}

	$files = $newFiles;
	$titledRefs = [];

	foreach ($files as $file) {
		$hastitle = false;
		$ispub = true;

		$filesplit = explode(".", $file);
		$node = $filesplit[0];
		$nodesplit = explode("/", $node);

		if (count($nodesplit) == 2) {
			$node = $nodesplit[1];
		}

		if (!file_exists($file)) {
			$titledRefs[$node] = "Missing File";
			continue;
		}

		$page = file_get_contents($file);
		$lines = explode("\n", $page);
		#find if page requires publish picocode

		foreach ($lines as $line) {
			if (transStart($line, "%%##holdpub")) {
				$ispub = false;
			}
		}
	

		#find the page title
		foreach ($lines as $line) {
			if (transStart($line, "%%##title=")) {
				$sides = explode("=", $line);
				$title = $sides[1];
				$titledRefs[$node] = $title;
				$hastitle = true;
			}
		}

		printDebug("getPageTitles(): \$ispub = " . getBool($ispub));
		if (!$hastitle) $titledRefs[$node] = "Untitled Page";
		if (!$ispub) $titledRefs[$node] = "%%HIDE%%";
	}

	return $titledRefs;
}

function getPriceBitcoin() {
	return (float) file_get_contents("data/btc-price");
}

function getPriceMonero() {
	return (float) file_get_contents("data/xmr-price");
}

function getItemList() {
	$items = [];
	$data = file_get_contents("itemlist.txt");
	$lines = explode("\n", $data);
	foreach ($lines as $line) {
		$split = explode(',', $line);
		$name = $split[0];
		$price = $split[1];
		if ($name !== "") $items[$name] = $price;
	}

	return $items;
}

function getItemListOld() {
	$items = [];

	if (file_exists("itemlist-old.txt") === false) {
		$items['NOFILE'] = true;
		return $items;
	}

	$data = file_get_contents("itemlist-old.txt");
	$lines = explode("\n", $data);
	foreach ($lines as $line) {
		$split = explode(',', $line);
		$name = $split[0];

		$price = $split[1];
		if ($name !== "") $items[$name] = $price;
	}

	return $items;
}

function getPageSubpage($file) {
	$subname = "[NOFILE]";

	if (file_exists($file)) {
		$subname = "[NOSUB]";
		$content = file_get_contents($file);
		$lines = explode("\n", $content);

		foreach ($lines as $line) {
			if (transStart($line, "%%##subpg=")) {
				$exploded = explode("=", $line);
				$subname = $exploded[1];
				break;
			}
		}
	} 
	
	return $subname;
}

function getPageFile($subpagedir = "[NONE]") {
	$fn = "";
	$inslang = "";

	if (is_null($_GET['q']) || $_GET['q'] === "") $_GET['q'] = 'main';

	if (!is_null($_GET['l'])) $inslang = "." . $_GET['l'] . ".trans";

	if (is_null($_GET['g']) && is_null($_GET['b']) && is_null($_GET['sp'])) 
		$fn = $_GET["q"] . $inslang . ".page";

	if (!is_null($_GET['g']) && is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = "guides/" . $_GET['g'] . $inslang . ".guide.page";
	}

	if (is_null($_GET['g']) && !is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = "blogs/" . $_GET['b'] . $inslang . ".blog.page";
	}
	
	if (is_null($_GET['g']) && is_null($_GET['b']) && !is_null($_GET['sp'])) {
		if ($subpagedir === "[NONE]") {
			$subname = getPageSubpage($_GET['q'] . ".page");
			if ($subname === "[NOFILE]") {
				$fn = $_GET['q'] . ".page"; //if this looks odd it does a 404	
			} else if ($subname === "[NOSUB]") {
				return $subname;
			} else {
				$subpagedir = $subname;
			}
		}

		$fn = $subpagedir . "/" . $_GET['sp'] . $inslang . ".sub.page";
	}
	
	return $fn;
}

function printDomains($msg) {
	if (file_exists("domainlist.txt")) {
		printDebug("printDomians() domain list exists");
		$domains = file_get_contents("domainlist.txt");
		$domains = explode("\n", $domains);
	} else {
		return;
	}

	echo "$msg";
	foreach ($domains as $domain) {
		if ($_SERVER['HTTP_HOST'] === $domain) continue;
		echo '<a href="http://' . $domain . '/' . '">' . $domain . "</a> ";
	}
	echo "\n";
}

function printCoreOut($text) {
	debugMethodName();

	if (is_string($text)) {
		if (preg_match('/\n/', $text)) {
			$text = explode('\n', $text);
		} else {
			echo "$text";
		}
	}

	if (is_array($text)) {
		foreach ($text as $line) {
			echo "$line\n";
		}
	}
}

function printPrice($itemname, $usdprice, $oldprice) {
	$oldmode = true;

	if ($oldprice === "NOFILE") {
		$oldmode = false;
	}

	$btc = getPriceBitcoin();
	$xmr = getPriceMonero();

	$btcp = number_format($usdprice / $btc, 4);
	$xmrp = number_format($usdprice / $xmr, 3);

	$sym = CURRENCY_SYM;

	if ($oldmode) {
		echo "<tr><td width=\"50%\">$itemname</td><td>$sym<strike>$oldprice</strike></td><td>\$$usdprice</td><td>BTC: $btcp</td><td>XMR: $xmrp</td></tr>\n";
	} else { 
		echo "<tr><td width=\"50%\">$itemname</td><td>$sym$usdprice</td><td>BTC: $btcp</td><td>XMR: $xmrp</td></tr>\n";
	}
}

function printItemListing() {
	$oldmode = true;
	if (!file_exists("itemlist.txt")) {
		http_response_code(404);
		print "<h2>Price list does not exist.</h2>";
		return;
	}
	$items = getItemList();
	$olditems = getItemListOld();
	$timebtc = filemtime("data/btc-price");
	$timexmr = filectime("data/xmr-price");

	if ($olditems['NOFILE'] === true) {
		$oldmode = false;
		unset($olditems["NOFILE"]);
	}

	echo "<p><strong>BTC " . date("G:i:s d/m/Y", $timebtc) . ": $" . getPriceBitcoin() . "<br>\n";
	echo "XMR " . date("G:i:s d/m/Y", $timexmr) . ": $" . getPriceMonero() . "</strong>\n";
	echo "<table>\n";

	if ($oldmode) {
		echo "<tr><td width=\"50%\"><strong>Name of Item</strong></td><td><strong>Old price</strong></td><td>Price</td><td>Bitcoin cost</td><td>Monero cost</td></tr>\n";
	} else {
		echo "<tr><td width=\"50%\"><strong>Name of Item</strong></td><td>Price</td><td>Bitcoin cost</td><td>Monero cost</td></tr>\n";
	}

	$oldprice=0;
	foreach ($items as $name => $price) {
		$exists = False;
		foreach($olditems as $oname => $oprice) {
			if ($oname === $name) {
				$oldprice = $oprice;
				$exists = True;
			}
		}

		if (!$exists) $oldprice = "XX.XX";
		if (!$oldmode) $oldprice = "NOFILE";
		printPrice($name, $price, $oldprice);
	}
	echo "</table>\n";
}

function printBlog($justone = False, $amount = 0, $start = 0) {
	if ($_GET['b'] == null) {
		if (!file_exists("blogs/")) {
			return;
		}

		if (!is_null($_GET['l'])) $blogdir = glob("blogs/*.trans.blog.page");
		else $blogdir = glob("blogs/*.blog.page");

		$blogs = [];
		$x = 0;

		$inslang = "";
		$getlang = "";
		

		if (!is_null($_GET['l'])) {
			$inslang = "." . $_GET['l'] . ".trans";
			$getlang = "&l=" . $_GET['l'];
		}

		foreach ($blogdir as $blog) {
			if ($blog === ".." || $blog === ".") continue;
			if (is_null($_GET['l']) && transEnd($blog, ".trans.blog.page")) continue;

			if (transEnd($blog, ".blog.page")) {
				$fileparts = explode('.', $blog);
				$name = $fileparts[0];
				$fn = $name . $inslang . ".blog.page";
				$ctime = filemtime($fn);
				$blogs[$x + $ctime] = $fn;
				$x++;
			}
		}

		krsort($blogs);


		$titled = getPageTitles($blogs);
		$blogs = transStripNode($blogs);

		
		echo '<table>';
		$x = 0;
		foreach ($blogs as $time => $blog) {
			$time = $time - $x;
			$x++;
			$thetitle = "Untitled Blog";
			$thenode = "";
			foreach ($titled as $node => $title) {
				if ($blog === $node) {
					$thetitle = $title;
					$thenode = $node;
				}
			}

			if ($thetitle === "%%HIDE%%") {
				continue;
			}

			echo '<tr><td>' . date("G:i:s d/m/Y", $time) . '</td>';
			
			if ($justone) echo "<p>Latest blog post...</p>";

			if (($x - $start) >= 0) {
				echo '<td><a href="/page.php?q=' . $_GET['q'] . '&b='
					. $thenode . $getlang . '">' . $thetitle . "</a></td></tr>\n";
			}

			if (($x - $start) == $amount) break;
			if ($justone) break;
		}
		echo "</table>\n";
	} else {
		if (!file_exists("blogs/")) return;
		if ($justone) return;

		$file = getPageFile();

		if (!file_exists($file)) {
			http_response_code(404);
			echo "<h2>Blog not found in the blogs folder</h2>";
		}

		$lines = explode("\n", $file);

		printCoreOut($lines);
	}
}
 
function printGuide() {
	if ($_GET['g'] == null) {
		echo "<ul>\n";
		if (!file_exists("guides/")) {
			http_reponse_code(404);
			print "<h2>Guides folder does not exist.</h2>";
		}
		$guidesdir = scandir("guides/");
		$guides = [];
		foreach ($guidesdir as $guide) {
			if (transEnd($guide, ".trans.guide.page")) {
				if (!is_null($_GET['l'])) {
					$exploded = explode('.', $guide);
					$guidelang = $exploded[1];
					if ($guidelang === $_GET['l']) {
						array_push($guides, 'guides/' . $guide);
					}
				}
			} else if (transEnd($guide, ".guide.page")) {
				if (is_null($_GET['l'])) {
					array_push($guides, 'guides/' . $guide);
				}
			}
		}

		$titled = getPageTitles($guides);

		foreach ($titled as $node => $title) {
			if ($titled === "%%HIDE%%") continue;
			if (is_null($_GET['l'])) {
				echo '<li><a href="/page.php?q=' . $_GET['q'] . '&g=' . $node . '">' . $title . "</a></li>";
			} else {
				echo '<li><a href="/page.php?q=' . $_GET['q'] . '&g=' . $node . '&l=' . $_GET['l'] . '">' . $title . "</a></li>";
			}
			echo "\n";
		}
		echo "</ul>\n";
			
	} else {
		$file = getPageFile();
		$lines = explode("\n", $file);
		printCoreOut($lines);

		echo '<p><a href="/page.php?q=' . $_GET['q'] . '">Back to Guides</a>';
	}
}

function printSubpage($groupname) {
	$groupname = transSecureSysName($groupname); //this stops injection
	$lang = transSecureSysName($_GET['l']);

	$subpages = [];

	if (!file_exists($groupname . '/')) {
		http_response_code(404);
		print "<h2>Subpage called when directory does not exist</h2>";
		return;
	}

	if (is_null($_GET['sp'])) {
		$subpagesdir = scandir($groupname . '/');

		$inslang = "";
		$getlang = "";

		if (!is_null($_GET['l'])) {
			$inslang = "." . $_GET['l'] . ".trans";
			$getlang = "&l=" . $_GET['l'];
		}

		foreach($subpagesdir as $subpage) {
			if ($subpage === ".." || $subpage === ".") continue;

			$exploded = explode('.', $subpage);
			$thissub = $exploded[0];
			$fn = $groupname . '/' . $thissub . $inslang . ".sub.page";

			array_push($subpages, $groupname . '/' . $thissub . $inslang . ".sub.page");
		}

		$titled = getPageTitles($subpages, $groupname);
	
		echo "<ul>";
		foreach($titled as $node => $title) {
			if ($title === "%%HIDE%%") continue;
			if ($title === "Missing File") continue;
			echo '<li><a href="/page.php?q=' . $_GET['q'] . '&sp=' . $node . $getlang .  '">' . $title . "</a></li>\n";
		}
		echo "</ul>";
	} else {
		$file = getPageFile($groupname);

		if (!file_exists($file)) {
			http_response_code(404);
			print "<h2>Subpage called but file does not exist</h2>";
			return;
		}

		$grouptitle = getPageTitle($file);
		printFile($file);
		echo '<p><a href="/page.php?q=' . $_GET['q']  . '">Back to ' . $grouptitle . '</a>';
	}	
}

function printFile($file) {
	global $HIDEPAGE;
	
	if ($HIDEPAGE) {
		echo "The following page is currently hidden from view, and is probably
			a work in progress.";
		http_response_code(404);
		return;
	}

	if (file_exists($file)) {
		debugMethodName();
		$content = file_get_contents($file);
		$lines = explode("\n", $content);
		
		foreach ($lines as $line) {
			if (substr($line, 0, 4) !== "%%##") {
				printCoreOut($line);
				echo "\n";
			} else {
				// eventually this will be a function of
				// similar things but for now it just has
				// icnlude, which includes a file
				if (transStart($line, "%%##incld=")) {
					$exploded = explode("=", $line);
					$incl = $exploded[1];
					printFile($incl);
				}
				if (transStart($line, "%%##subpg=")) {
					$exploded = explode("=", $line);
					$subpage = $exploded[1];
					printSubpage($subpage);
				}
				if (transStart($line, "%%##guide")) {
					printGuide();
				}
				if (transStart($line, "%%##blogs")) {
					printBlog();
				}
				if (transStart($line, "%%##price")) {
					if (transStart($line, "%%##price=all")) {
						printItemListing();
					}
				}
			}
		}
	} else {
		http_response_code(404);
		print("<h2>File not found for this page</h2>");
	}
}

function printPageBody() {
	$pagefile = getPageFile();
		
	printFile($pagefile);
}

function printLinkTop() {
	if (!is_null($_GET['l'])) $files = glob("./*.trans.page");
	else $files = glob("./*.page");

	$links = array();

	$links = getPageTitles($files);

	$ordered = array();

	$file = file_get_contents("menulayout.txt");
	$layout = explode("\n", $file);

	$newlinks = [];

	foreach ($layout as $node) {
		if (($links[$node] === "") || ($links[$node] == null)) {
			continue;
		}
		$ordered[$node] = $links[$node];
		unset($links[$node]);
	}

	foreach ($ordered as $node => $title) {
		if ($title === "%%HIDE%%") continue;
		if (is_null($_GET['l'])) {
			echo "<a href=\"/page.php?q=$node\">$title</a> ";
		} else {
			$lang = $_GET['l'];
			echo "<a href=\"/page.php?q=$node&l=$lang\">$title</a> ";	
		}
	}

	echo "<br>";

	foreach ($links as $node => $title) {
		if ($title === "%%HIDE%%") continue;
		if (($node === "") || ($node == null)) {
			continue;
		}
		if (is_null($_GET['l'])) {
			echo "<a href=\"/page.php?q=$node\">$title</a> ";
		} else {
			$lang = $_GET['l'];
			echo "<a href=\"/page.php?q=$node&l=$lang\">$title</a> ";
		}
	}
}

function printLinksLangs() {
	if (!file_exists("languages.txt")) return;
	
	$config = file_get_contents("languages.txt");
	$relations = explode("\n", $config);
	$langs = [];
	foreach ($relations as $relation) {
		$splitted = explode(',', $relation);
		$code = $splitted[0];
		$name = $splitted[1];
		$langs[$code] = $name;
	}

	echo '<a href="/page.php?q=' . $_GET['q'] . '">English</a> ';

	$subpglink = "";

	if (!is_null($_GET['sp'])) $subpglink = "&sp=" . $_GET['sp'];

	foreach ($langs as $key => $value) {
		echo '<a href="/page.php?q=' . $_GET['q'] . '&l=' . $key . $subpglink . '">' . $value . '</a> ';
	}

	echo "<br>";
}

function getPageTitle($pagefile) {
	$title = "Unnamed Page";

	if (!file_exists($pagefile)) {
		$title = "Page not found";
	} else {
		$page = file_get_contents($pagefile);
		$lines = explode("\n", $page);

		foreach ($lines as $line) {
			if (transStart($line, "%%##title=")) {
				$sides = explode("=", $line);
				$title = $sides[1];
			}
		}

		$hide = false;

		foreach ($lines as $line) {
			if (transStart($line, "%%##holdpub")) $hide = true;
		}

		printDebug("getPageTitles(): \$hide = " . getBool($hide));
		if ($hide) $title = "%%HIDE%%";
	}

	return $title;
}

function printTitle() {
	global $HIDEPAGE;
	$pagefile = getPageFile();
	$pagetitle = getPageTitle($pagefile);
	if ($pagetitle === "%%HIDE%%") {
		$HIDEPAGE = true;
		$pagetitle = "404: Hidden Page";
	}
	echo $pagetitle;
}

if (file_exists("template.php")) {
	include("template.php");
	exit();
}

?>

<html><head><title><?php echo printTitle(); echo " :: "; echo SITENAME; ?></title></head>
<style>
body {
	background:url(rainbow_dash.png) fixed no-repeat bottom right;
	background-color: #222222;
	color: #00AA00;
} 
a {
	color: #8888DD;
}
table {
	color: #00DD00;
	background-color: #333333;
	border-radius: 15px;
	opacity: 0.5;
}
</style>
<body>
<center>
<?php printLinksLangs(); ?><br>
<?php printDomains("mirrors: ") ?><br>
<a href="<?php echo getLinkMain(); ?>"><img src="<?php echo SITELOGO; ?>" alt="Site logo image"></img>
<p><?php printLinkTop(); ?><br><br>
<?php printBlog(True); ?>
<br><br><br>
<table width=750 cellspacing=10 cellpadding=10><tr><td>
<h1><?php printTitle(); ?></h1>
<?php printPageBody(); ?>
</tr></td></table>
<br><br><br><br><br><br>
<p><?php echo date("Y") . " "; echo SITEAUTHOR; echo ", server time " . date("G:i:s d/m/y") . " UTC"; ?>
<p><i>Powered by Kaizu's <a href="https://github.com/kaizushi/picosite"><?php echo SOFTNAME; ?></a>!</i></p>
</body></html>
