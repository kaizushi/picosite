<?php

// picosite is almost a CMS, its a light way to have a site.

define("SITENAME", "A Fresh new picosite");
define("SOFTNAME", "picosite 1.0.0");
define("SITELOGO", "sitelogo.png");
define("DEBUGOUT", true);
define("CURRENCY_SYM", "$");

include_once("parser.php");

if (isset($argv[1])) $_GET["q"] = $argv[1];

function debugout($msg) {
	if (DEBUGOUT) {
		echo "picosite DEBUG: " . $msg . "\n";
		file_put_contents("debug.log", $msg . "\n", FILE_APPEND | LOCK_EX);
	}
}

function limitSysName($string) {
	//dumb method name
	$newstring = preg_replace("/[^A-Za-z0-9.-]/","",$string);
	return $newstring;
}


function startsWith($instring, $substring) {
	$length = strlen($substring);
	return (substr($instring, 0, $length) === $substring);
}

function endsWith($instring, $substring) {
	$length = strlen($substring);
	return $length === 0 || (substr($instring, -$length) === $substring);
}

function secureGetFile($filename) {
	$fileparts = explode("/", $filename);
	foreach ($fileparts as $filepart) {
		$filename = $filepart;
	}

	if ($fileparts[0] === "guides") {
		$file = "guides/" . limitSysName($filename);
	} else if ($fileparts[0] === "blogs") {
		$file = "blogs/" . limitSysName($filename);
	} else {
		$file = limitSysName($filename);
	}

	return $file;
}

function nodestrip($nodes) {
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

function pageTitles($files) {
	$titledRefs = [];

	$hastitle=false;
	
	if (!is_null($_GET['l'])) {
		$newFiles = [];
		foreach ($files as $file) {
			if (endsWith($file, ".trans.guide.page")) {
				array_push($newFiles, $file);
			} else {
				if (endsWith($file, ".blog.page") || endsWith($file,".guide.page")) {
					$parts = explode('.', $file);
					$title = $parts[0];
					if (endsWith($file,".guide.php")) $fn = "guides/" . $title . "." . $_GET['l'] . '.guide.php';
					array_push($newFiles, $file);
				} else {
					$parts = explode('.', $file);
					$title = $parts[0];
					$fn = $title . "." . $_GET['l'] . '.trans.page';
					array_push($newFiles, $fn);
				}
			}
		}
		$files = $newFiles;
	}

	foreach ($files as $file) {
		$filesplit = explode(".", $file);
		$node = $filesplit[0];
		$nodesplit = explode("/", $node);
		if (count($nodesplit) == 2) {
			$node = $nodesplit[1];
		}
		$page = file_get_contents(secureGetFile($file));
		$lines = explode("\n", $page);

		$hastitle = false;

		foreach ($lines as $line) {
			if (startsWith($line, "%%##title=")) {
				$sides = explode("=", $line);
				$title = $sides[1];
				$titledRefs[$node] = $title;
				$hastitle = true;
			}
		}

		if (!$hastitle) $titledRefs[$node] = "Untitled Page";
	}

	return $titledRefs;
}

function getpriceBTC() {
	return (float) file_get_contents("data/btc-price");
}

function getpriceXMR() {
	return (float) file_get_contents("data/xmr-price");
}

function getItems() {
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

function getOldItems() {
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

function getPageFile($subpagedir = "none") {
	//This method is a mess, but is another thing for security. This makes
	//sure that files opened are somewhat hardcoded. It can be a tricky thing
	//to change.
	$fn = "";

	if (is_null($_GET['q']) || $_GET['q'] === "") $_GET['q'] = 'main';

	if (is_null($_GET['l']) && is_null($_GET['g']) && is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = $_GET["q"] . ".page";
	}

	if (!is_null($_GET['l']) && is_null($_GET['g']) && is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = $_GET["q"] . "." . $_GET["l"] . ".trans.page";
	}

	if (is_null($_GET['l']) && !is_null($_GET['g']) && is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = "guides/" . $_GET['g'] . ".guide.page";
	}

	if (!is_null($_GET['l']) && !is_null($_GET['g']) && is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = "guides/" . $_GET['g'] . "." . $_GET['l'] . ".trans.guide.page";
	}

	if (is_null($_GET['l']) && is_null($_GET['g']) && !is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = "blogs/" . $_GET['b'] . ".blog.page";
	}

	if (!is_null($_GET['l']) && is_null($_GET['g']) && !is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = "blogs/" . $_GET['b'] . "." . $_GET['l'] . ".trans.blog.page";
	}

	if (!is_null($_GET['l']) && !is_null($_GET['g']) && !is_null($_GET['b']) && is_null($_GET['sp'])) {
		$fn = $subpagedir . "/" . $_GET['sp'] . ".sub.page";
	}
	
	if (!is_null($_GET['l']) && is_null($_GET['g']) && is_null($_GET['b']) && !is_null($_GET['sp'])) {
		$fn = $subpagedir . "/" . $_GET['sp'] . "." . $_GET['l'] . ".trans.sub.page";
	}

	return $fn;
}

function parsePrint($text) {
	if (is_string($text)) {
		if (preg_match('/\n/', $text)) {
			$text = explode('\n', $text);
		} else {
			echo "$text";
		}
	}

	if (is_array($text)) {
		foreach ($text as $parse) {
			
		}
		foreach ($text as $line) {
			echo "$line";
		}
	}
}

function printPrice($itemname, $usdprice, $oldprice) {
	$oldmode = true;

	if ($oldprice === "NOFILE") {
		$oldmode = false;
	}

	$btc = getpriceBTC();
	$xmr = getpriceXMR();

	$btcp = number_format($usdprice / $btc, 4);
	$xmrp = number_format($usdprice / $xmr, 3);

	$sym = CURRENCY_SYM;

	if ($oldmode) {
		echo "<tr><td width=\"50%\">$itemname</td><td>$sym<strike>$oldprice</strike></td><td>\$$usdprice</td><td>BTC: $btcp</td><td>XMR: $xmrp</td></tr>\n";
	} else { 
		echo "<tr><td width=\"50%\">$itemname</td><td>$sym$usdprice</td><td>BTC: $btcp</td><td>XMR: $xmrp</td></tr>\n";
	}
}

function printallPrices() {
	$oldmode = true;
	if (!file_exists("itemlist.txt")) {
		http_response_code(404);
		print "<h2>Price list does not exist.</h2>";
		break;
	}
	$items = getItems();
	$olditems = getOldItems();
	$timebtc = filemtime("data/btc-price");
	$timexmr = filectime("data/xmr-price");

	if ($olditems['NOFILE'] === true) {
		$oldmode = false;
		unset($olditems["NOFILE"]);
	}

	echo "<p><strong>BTC " . date("G:i:s d/m/Y", $timebtc) . ": $" . getpriceBTC() . "<br>\n";
	echo "XMR " . date("G:i:s d/m/Y", $timexmr) . ": $" . getpriceXMR() . "</strong>\n";
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
			http_response_code(404);
			print "<h2>Blog directory does not exist.</h2>";
		}
		$blogdir = scandir("blogs/");
		$blogs = [];
		$x = 0;
		foreach ($blogdir as $blog) {
			if (endsWith($blog, ".blog.page")) {
				$fileparts = explode('.', $blog);
				$name = $fileparts[0];
				$ctime = filemtime('blogs/' . $name . ".blog.page");
				$blogs[$x + $ctime] = "blogs/" . $name . ".blog.page";
				$x++;
			}
		}

		krsort($blogs);
		$titled = pageTitles($blogs);
		$blogs = nodestrip($blogs);

		echo '<table>';
		$x = 0;
		foreach ($blogs as $time => $blog) {
			$time = $time - $x;
			$x++;
			echo '<tr><td>' . date("G:i:s d/m/Y", $time) . '</td>';
			$thetitle = "Untitled Blog";
			$thenode = "";
			foreach ($titled as $node => $title) {
				if ($blog === $node) {
					$thetitle = $title;
					$thenode = $node;
				}
			}
			
			if ($justone) echo "<p>Latest blog post...</p>";

			if (($x - $start) >= 0) {
				echo '<td><a href="/page.php?q=' . $_GET['q'] . '&b='
					. $thenode . '">' . $thetitle . "</a></td>\n";
			}

			if (($x - $start) == $amount) break;
			if ($justone) break;
		}
		echo "</table>\n";
	} else {
		if ($justone) return;
		$file = getPageFile();
		if (!file_exists($file)) {
			http_response_code(404);
			echo "<h2>Blog not found in the blogs folder</h2>";
		}
		$lines = explode("\n", $file);
		parsePrint($lines);
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
			if (endsWith($guide, ".trans.guide.page")) {
				if (!is_null($_GET['l'])) {
					$exploded = explode('.', $guide);
					$guidelang = $exploded[1];
					if ($guidelang === $_GET['l']) {
						array_push($guides, 'guides/' . $guide);
					}
				}
			} else if (endsWith($guide, ".guide.page")) {
				if (is_null($_GET['l'])) {
					array_push($guides, 'guides/' . $guide);
				}
			}
		}

		$titled = pageTitles($guides);

		foreach ($titled as $node => $title) {
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
		parsePrint($lines);

		echo '<p><a href="/page.php?q=' . $_GET['q'] . '">Back to Guides</a>';
	}
}

function printSubpage($groupname) {
	$foreign = false;

	$groupname = limitSysName($groupname); //this stops injection
	$lang = limitSysName($_GET['l']);

	$subpages = [];

	if (!file_exists($groupname . '/')) {
		http_response_code(404);
		print "<h2>Subpage called when directory does not exist</h2>";
		return;
	}

	if (is_null($_GET['sp'])) {
		$subpagesdir = scandir($groupname . '/');
		foreach($subpagesdir as $subpage) {
			if (endsWith($subpage, ".trans.sub.page")) {
				if (!is_null($_GET['l'])) {
					$exploded = explode('.', $subpage);
					$thissub = $exploded[1];
					$thislang = $exploded[2];
					if ($thislang === $_GET['l']) {
						array_push($subpages, $groupname . $thislang . '/' . $thissub);
					}
				}
			} else if (endsWith($guide, ".sub.page")) {
				if (is_null($_GET['l'])) {
					$exploded = explode('.', $subpage);
					$thissub = $exploded[1];
					array_push($subpages, $groupname . '/' . $thissub);
				}
			}
		}
	 

		$titled = pageTitles($subpages);
	
		foreach($titled as $node => $title) {
 			if (is_null($_GET['l'])) {
		 			echo '<li><a href="/page.php?q=' . $_GET['q'] . '&sp=' . $node .  '">' . $title . "</a></li>";
			} else {
				echo '<li><a href="/page.php?q=' . $_GET['q'] . '&sp=' . $node . '&l=' .   '">' . $title . "</a></li>";
			}
			echo "\n";
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
		$lines = explode("\n", $file);
		parsePrint($lines);
		echo '<p><a href="/page.php?q=' . $_GET['q']  . '">Back to ' . $grouptitle . '</a>';
	}

function printFile($file) {

	if (file_exists($file)) {
		$content = file_get_contents(secureGetFile($file));
		$lines = explode("\n", $content);
		
		foreach ($lines as $line) {
			if (substr($line, 0, 4) !== "%%##") {
				parsePrint($line);
				echo "\n";
			} else {
				// eventually this will be a function of
				// similar things but for now it just has
				// icnlude, which includes a file
				if (startsWith($line, "%%##incld=")) {
					$exploded = explode("=", $line);
					$incl = $exploded[1];
					printFile($incl);
				}
				if (startsWith($line, "%%##subpg=")) {
					$expoded = $explode("=", $line);
					$subpage = $exploded[1];
					printSubpage($subpage);
				}
				if (startsWith($line, "%%##guide")) {
					printGuide();
				}
				if (startsWith($line, "%%##blogs")) {
					printBlog();
				}
				if (startsWith($line, "%%##price")) {
					if (startsWith($line, "%%##price=all")) {
						printallPrices();
					}
				}
						
			}
		}
	} else {
		http_response_code(404);
		print("<h2>File not found for this page</h2>");
	}
}

function pageBody() {
	$pagefile = getPageFile();
	printFile($pagefile);
}

function pageLinks() {
	$files = scandir(".");
	$relevant = array();
	$links = array();
	foreach ($files as $file) {
		if (endsWith($file, ".page")) {
			array_push($relevant, $file);
		}
	}

	$links = pageTitles($relevant);

	$ordered = array();

	$file = file_get_contents("menulayout.txt");
	$layout = explode("\n", $file);

	foreach ($layout as $node) {
		if (($links[$node] === "") || ($links[$node] == null)) {
			continue;
		}
		$ordered[$node] = $links[$node];
		unset($links[$node]);
	}

	foreach ($ordered as $node => $title) {
		if (is_null($_GET['l'])) {
			echo "<a href=\"/page.php?q=$node\">$title</a> ";
		} else {
			$lang = $_GET['l'];
			echo "<a href=\"/page.php?q=$node&l=$lang\">$title</a> ";	
		}
	}

	echo "<br>";

	foreach ($links as $node => $title) {
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

function langLinks() {
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
	foreach ($langs as $key => $value) {
		echo '<a href="/page.php?q=' . $_GET['q'] . '&l=' . $key . '">' . $value . '</a> ';
	}

	echo "<br>";
}

function getPageTitle($pagefile) {
	$title = "Unnamed Page";

	if (!file_exists($pagefile)) {
		$title = "Page not found";
	} else {
		$page = file_get_contents(secureGetFile($pagefile));
		$lines = explode("\n", $page);

		foreach ($lines as $line) {
			if (substr($line, 0, 10) === "%%##title=") {
				$sides = explode("=", $line);
				$title = $sides[1];
			}
		}
	}

	return $title;
}

function pageTitle() {
	$pagefile = getPageFile();
	$pagetitle = getPageTitle($pagefile);
	return $pagetitle;
}
?>

<html><head><title><?php echo pageTitle(); echo " :: "; echo SITENAME; ?></title></head>
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
<?php langLinks(); ?>
<img src="<?php echo SITELOGO; ?>" alt="Site logo image"></img>
<p><?php pageLinks(); ?><br><br>
<?php printBlog(True); ?>
<br><br><br>
<table width=750 cellspacing=10 cellpadding=10><tr><td>
<h1><?php print pageTitle(); ?></h1>
<?php pageBody(); ?>
</tr></td></table>
<br><br><br><br><br><br>
<p><?php echo date("Y") . " Mr Website Creator, server time " . date("G:i:s d/m/y") . " UTC"; ?>
<p><i>Powered by Kaizu's <a href="https://github.com/kaizushi/picosite"><?php echo SOFTNAME; ?></a>, <a href="https://www.nginx.com/">nginx</a>, PHP, and hopefully Linux!</i></p>
</body></html>
