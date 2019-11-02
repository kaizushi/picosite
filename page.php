<?php

// picosite is almost a CMS, its a light way to have a site.

define("SITENAME", "A Fresh new picosite");
define("SOFTNAME", "picosite 1.0.0");
define("SITELOGO", "sitelogo.png");
define("DEBUGOUT", false);
define("TRACEOFF", true); 
define("CURRENCY_SYM", "$");

include_once("parser.php");

if (isset($argv[1])) $_GET["q"] = $argv[1];

function GetCallingMethodName(){
	if (TRACEOFF) return;
	$e = new Exception();
	$trace = $e->getTrace();
	//position 0 would be the line that called this function so we ignore it
	$last_call = $trace[1];
	print_r($last_call);
}

function debugout($msg) {
	if (DEBUGOUT) {
		echo "picosite DEBUG: " . $msg . "\n";
		file_put_contents("debug.log", $msg . "\n", FILE_APPEND | LOCK_EX);
		echo "<br>";
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

function pageTitles($files, $subpage = "[NONE]") {
	debugout("pageTitles: method called with subpage $subpage");
	
	$newFiles = [];
	foreach ($files as $file) {
		$paths = explode("/", $file);
		debugout("pageTitles: file $file has " . count($paths) . " paths");
		if (count($paths) == 2) {
			$file = $paths[1];
		};

		debugout("pageTitles: doing $file in list");
		$lang = NULL;
		$inslang = "";
		$fn = NULL;

		$parts = explode('.', $file);
		$title = $parts[0];
		debugout("pageTitles: file has title: $title");
		debugout("pageTitles: file $file has " . count($parts) . " parts");
		if (((endsWith($file, ".blog.page") || endsWith($file,".guide.page") || endsWith($file,".sub.page"))
		      && count($parts)) == 3) {
			$type = $parts[1];
			debugout("pageTitles: special type (title: $title) (type: $type)");
		} elseif (endsWith($file, ".page") && count($parts) == 2) {
			$type = "page";
		}
		debugout("pageTitles: file has type: $type");		

		if (($subpage === "[NONE]") && $type === "sub") {
			debugout("pageTitles: no subpage but sub page type detected");
			continue;
		}

		if (!is_null($_GET['l'])) { 
			$lang = $parts[2];
			$inslang = "." . $lang . ".trans";
		}
			
		if ($type === "blog") $fn = "blogs/" . $title . $inslang . ".blog.page";
		if ($type === "guide") $fn = "guides/" . $title . $inslang . ".guide.page";
		if ($type === "sub") $fn = $subpage . "/" . $title . $inslang .  ".sub.page";
		
		if ((($type === "page") && endsWith($file, ".page")))
			$fn = $title . $inslang . ".page";
		
		if (!is_null($fn)) { 
			debugout("pageTitles: NOT NULL for $fn");
			array_push($newFiles, $fn);
		}
	}

	$files = $newFiles;
	$titledRefs = [];

	debugout("pageTitles: searching for titles through " . count($files) . " files");
	foreach ($files as $file) {
		debugout("pageTitles: iterating files value: $file");
		
		$hastitle = false;

		$filesplit = explode(".", $file);
		$node = $filesplit[0];
		$nodesplit = explode("/", $node);

		if (count($nodesplit) == 2) {
			$node = $nodesplit[1];
		}
		debugout("pageTitles: iteration broken down into $node");

		if (!file_exists($file)) {
			$titledRefs[$node] = "Missing File";
			continue;
		}

		$page = file_get_contents($file);
		$lines = explode("\n", $page);

		debugout("pageTitles: page has been exploded into " . count($lines) . " array slots");

		foreach ($lines as $line) {
			debugout("pageTitles: iterating file lines: $line");

			if (startsWith($line, "%%##title=")) {
				$sides = explode("=", $line);
				$title = $sides[1];
				debugout("pageTitles: detected title $title");
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

function getSubpageName($file) {
	$subname = "[NOFILE]";

	if (file_exists($file)) {
		$subname = "[NOSUB]";
		$content = file_get_contents($file);
		$lines = explode("\n", $content);

		foreach ($lines as $line) {
			if (startsWith($line, "%%##subpg=")) {
				$exploded = explode("=", $line);
				$subname = $exploded[1];
				debugout("getSubpageName: we have found subpage $subname picocall in $file");
				break;
			}
		}
	} 
	
	return $subname;
}

function getPageFile($subpagedir = "[NONE]") {
	//This method is a mess, but is another thing for security. This makes
	//sure that files opened are somewhat hardcoded. It can be a tricky thing
	//to change.
	debugout("getPageFile: called from " . GetCallingMethodName());
	debugout("getPageFile: starting with $subpagedir");
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
	
	if (is_null($_GET['l']) && is_null($_GET['g']) && is_null($_GET['b']) && !is_null($_GET['sp'])) {
		if ($subpagedir === "[NONE]") {
			$subname = getSubpageName($_GET['q'] . ".page");
			if ($subname === "[NOFILE]") {
				$fn = $_GET['q'] . ".page"; //if this looks odd it does a 404	
			} else if ($subname === "[NOSUB]") {
				return $subname;
			} else {
				$subpagedir = $subname;
			}
		}
		
		$fn = $subpagedir . "/" . $_GET['sp'] . ".sub.page";
	}
	
	debugOut("getPageFile: exiting to return $fn");
	return $fn;
}

function printParse($text) {
	GetCallingMethodName();

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
		return;
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
			return;
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
		if (!file_exists("blogs/")) return;
		if ($justone) return;

		$file = getPageFile();

		if (!file_exists($file)) {
			http_response_code(404);
			echo "<h2>Blog not found in the blogs folder</h2>";
		}

		$lines = explode("\n", $file);
		printParse($lines);
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
		printParse($lines);

		echo '<p><a href="/page.php?q=' . $_GET['q'] . '">Back to Guides</a>';
	}
}

function printSubpage($groupname) {
	debugout("printSubpage: starting with subname \"$groupname\" called from " . GetCallingMethodName());
	$groupname = limitSysName($groupname); //this stops injection
	$lang = limitSysName($_GET['l']);

	$subpages = [];

	if (!file_exists($groupname . '/')) {
		http_response_code(404);
		print "<h2>Subpage called when directory does not exist</h2>";
		return;
	}

	if (is_null($_GET['sp'])) {
		debugout("printSubpage: doing a subpage listing");
		$subpagesdir = scandir($groupname . '/');
		debugout("printSubpage: we scanned the directory and found " . count($subpagesdir) . " items.");

		$inslang = "";
		$getlang = "";

		if (!is_null($_GET['l'])) {
			$inslang = $_GET['l'] . ".trans";
			$getlang = "&l=" . $_GET['l'];
		}

		foreach($subpagesdir as $subpage) {
			if ($subpage === ".." || $subpage == ".") continue;
			debugout("printSubpage: processing $subpage");

			$exploded = explode('.', $subpage);
			$thissub = $exploded[0];
			array_push($subpages, $groupname . '/' . $thissub . $inslang . ".sub.page");
		}

		debugout("printSubpage: there are " . count($subpages) . " valid subpages.");

		$titled = pageTitles($subpages, $groupname);
		debugout("printSubpage: we have titled " . count($subpages) . " valid titled pages.");
	
		echo "<ul>";
		foreach($titled as $node => $title) {
			echo '<li><a href="/page.php?q=' . $_GET['q'] . '&sp=' . $node . $getlang .  '">' . $title . "</a></li>\n";
		}
		echo "</ul>";
	} else {
		$file = getPageFile($groupname);
		debugout("printSubpages: we tried getPagefile($groupname) and got this: $file");

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
	debugout("printFile called, printing: $file");
	if (file_exists($file)) {
		GetCallingMethodName();
		$content = file_get_contents($file);
		$lines = explode("\n", $content);
		
		debugout("printFile: we have " . count($lines) . " lines to print.");
		foreach ($lines as $line) {
			debugout("printFile: processing line: $line");
			if (substr($line, 0, 4) !== "%%##") {
				debugout("printFile: calling printParse(): $line");
				printParse($line);
				echo "\n";
			} else {
				debugout("printFile: processing syntax $line");
				// eventually this will be a function of
				// similar things but for now it just has
				// icnlude, which includes a file
				if (startsWith($line, "%%##incld=")) {
					$exploded = explode("=", $line);
					$incl = $exploded[1];
					debugout("printFile: we are including file: $incl");
					printFile($incl);
				}
				if (startsWith($line, "%%##subpg=")) {
					$exploded = explode("=", $line);
					$subpage = $exploded[1];
					debugout("printFile: we are calling subpage: $subpage");
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
		$page = file_get_contents($pagefile);
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
