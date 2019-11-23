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
<?php printLinksLangs(); ?>
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
