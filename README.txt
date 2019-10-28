=== PICOSITE README ===

=== Introduction ===

This is picosite, it is a CMS that relies on one uploading files or using
and editor with SSH. It basically searches for files of specific names and
protects against injection by having these names hardcoded, and there is
a regexp which only allows files to have certain characters. 

I kept updating it without any version control, so the versions older than
what is here are not-existent.

=== Features ===

* Pages, as such pages just sit in the same file as page.php which is the
	brains of picosite. 
* Include, there is a special syntax to include some other file in a page.
	I find this useful for doing a canary or providing GPG keys.
* Translations, these also sit in the directory with page.php and there is
	a text file for a list of languages.
* Blogs, a directory called 'blogs' can be created and files placed within
	for adding to the blog. It uses the modification time of the file
	to know the date of a blog. There is a special syntax for Pages
	which includes a list of blogs, and also it displays a blog when
	a blog that exists is specified by name in the GET value 'b'.
	Blogs do not support translations at this point but they might.
* Guides, guides can exist in a folder called 'guides' and they support
	translations. Much like blogs there is a special syntax for Pages
	which shows a list of guides or if GET value 'g' is passed an
	actual guide if the file exists.
* A static menu, a file called menulayout.txt can put Pages in a top menu
	where the order is preserved as what you put in menulist.txt
* A automatic menu, which is every page except those in the static menu.
	They are sorted alphabetically.
* Latest blog post, in the header of every page unless you are viewing
	a blog post or the listing of blogs - is the latest blog post.
* Pricing system, if you write a cron job to get Bitcoin and Monero
	prices from somewhere and put them in files 'xmrprice' and
	'btcprice' you can display a list of prices for various
	products. There is a special syntax which allows a list of
	prices to be displayed. Products are listed in the file
	itemlist.txt with prices in the currency that your cron
	job fetches. The price list is displayed in the same order
	as the item list.
* Security, having had trouble with vandals of PHP software and some
	malware, I wrote this so nothing could go wrong. As such the
	site has no editor, there is no code which can alter the site
	in picocms. One has to log in with SSH and create files.

=== Using picocms ===

One starts by uploading page.php to a web directory. Then one has to
create some files for pages. As such the pages feature is the only
fundamental one. Blogs, guides, even the static menu are all optional
and one can ignore these features, just by not using their 'special'
syntax and not creating any pages for them.

So first you must create a page, and it must end in the extension .page
and the filename is the reference used to find to the page in the 'q'
GET variable. In your page there is a special syntax to then set the
title of the page as it will display in a browser. It is this...

 %%##title=The Page Title

I used this weird syntax to avoid collisons with anything else, and 
all the 'special syntax' I mentioned in the feature list is much like
this and starts with those symbols.

Then you might want to include something in a page, as such you can use
this special syntax:

 %%##incld=filename.txt

Note: do not use the space in front of the syntax examples. Picosite
includes such as the README.txt one of the main page will run them and
then the default main.page will render things in a very bizarre way.

You can replace filename.txt with any page, and this does not use the
PHP include() and I did my own. Dumb pages are safe pages.

You should see the page you created in a link under the logo image.
Make more pages, and they will also display there. But there is an
option for a menu above these links, and you can put pages in this
top menu and it will take it out of the bottom one.

To do this create the file 'menulayout.txt' and on each line specify
the filename of the page you want in the menu. This top menu will
display in the order of the menu layout file. 

So next you might want a blog on the site, so to do that create a
page, I suggest you call it blog.page and it must have this special
syntax...

 %%##blogs

This page will now show a list of blogs, and at this point it should
be empty and display no blogs. To create a blog you must create a
folder called 'blogs' and in it you put blog entries that are much like
pages in that they have a title and can use special syntax. These 
pages must end in the extension blog.page and they will display in the
list with a date printed based on the file creation time. If copying
the blogs or backing up the site remember to preserve the creation
time.

The same page that lists the blogs displays the blogs when a blog is
selected. Selecting a blog passed the filename of the blog as the 'b'
GET variable to page.php.

You might also want to put the page you made for blogs in the menulayout.
The blog you have created should display under 'latest blog post' and
this is a nice feature so people can easily see if you've done anything
new.

Now you might want to have some other languages supported by your site.
For that you have to create a file called 'languages.txt' and this file
is a list of lines with a comma in them. Before the comma goes the code
for the language, this is used in the 'l' get variable, and after the
comma goes the human readable name of the langauge.

The file 'languages.txt' for example could appear as:

en,English
de,German

You can have as many lines and languages as you so please. Pages will
only be available in the other language if you create a page for them
containing the tranlated text. These pages use an extension including
the code for the languge specified in languages.txt. For a German
translation following the code in the above example, one should call
the file 'pagename.de.trans.page' and these files go in the directory
with page.php and your other pages.

After this one might want to list some products on their site, and this
system is currently hardcoded to display an estimate in Bitcoin and
Monero of what the products will cost. Firstly, there is a file called
getprices.php that came with picosite. This runs with PHP-CLI and of
course you need cron. It will put the prices in a folder called 'data'
and the files for prices will be called 'xmrprice' and 'btcprice'.

At the moment it is limited in displaying a dollar sign for the base
currency that your cron job fetches.

To have a price list one must create a page for it and use some syntax
to show the list. Like the blogs, you should put something around it
and there should be a title for the page. You should describe your
products and such. The syntax for showing the price list is:

 %%##price=all

One can also display a specific price, and it can get prices from two
files one for older prices you used to use, and one for the current
prices. These files are 'itemlist.txt' and 'itemlist-old.txt' and they
are both of the same format. As such each line contains a product and
price, the price after a comma. For example:

Car Wash,25
Detailing,70

This will create two products one Car Wash for 25 units of the base
currency your cronjob uses, and another for Detailing for 75. These
will display in a nice list. It is ordered the same as the order of
the files you created. If an old price does not exist a price will
render as $XX.XX - I will eventually implement a feature so that if
'itemlist-old.txt' does not exist the table will render without any
old prices.

You may now wish to use the guides feature, given time and updates this
feature will be made more generic. As such it will eventually allow one
to create any kind of folder and have it as a 'subpage.'

For now you just create a folder called guides and like the blog and the
price system, you use some special syntax on a page you create. As such
I recommend creating 'guides.page' and using this:

 %%##guide

This will create a nice list of guides, and to write a guide you should
create a folder called 'guides.' With guides translations are supported
and as such a guide has the extension guide.page and with translations,
just like regular pages it has a country code. If it is 'de' for
example:

guidename.de.trans.guide.page

As such guides can be used for other things. As I said, eventualy this
feature will be more generic. For now you can only have one page like
this with a listing of subpages. I was intended to write guides for my
customers on how to use my service. This is how it got its name.

Now for the last part, where you customise your page.php to look the
way you want it to look. If you go to the bottom of page.php you will
see the HTML code. It is very short, in a future release this may be
included instead from a template.php file much the same.

Anyway, if you have followed all of this guide you have done everything
in picocms possible, except its infinite page capacity. I hope you enj-
oy it because it can't make changes to the system. That is a point for
its security bonus. 

More will come in terms of features, and some things will change. If
you see an error in the docmentation or a bug in the code email me at
kaizushi@infantile.us or kaizushi@cock.li
