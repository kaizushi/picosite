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

= Install the script =

One starts by uploading page.php to a web directory. Then one has to
create some files for pages. As such the pages feature is the only
fundamental one. Blogs, guides, even the static menu are all optional
and one can ignore these features, just by not using their 'special'
syntax and not creating any pages for them.

= Set the Site Name =

There are some config settings at the top of page.php which control
the site. One is for the site name and you can also set others. You
might also want to change the logo graphic. 

= Change the Main Page =

To change your first page edit a file called main.page and wipe the
one which came with picosite. This page is the 'home' of your website.
This page is required and you will get a 404 by default if you delete
it. Set the title of the page with the method below:

 %%##title=The Page Title

(The space at the start is there because this readme is called by the
default picosite, and the title setting line above rendered so one
could not see it, and was changing the page title of the main.page
which includes it)

After this step that is required, the parts that come are optional and
you don't use them. The main page uses the same syntax as any other
page.

Pages on picosite can contain HTML but cannot use PHP. A coming feature
is being able to use BBCode to make it user friendly. 

= Create more pages =

You can optionally create more than one page. To do this you simply
create a file ending in .page and its name only allows certin
characters. These are lower case and upper case letters, numbers,
and a dash (-). This is to prevent exploits and exfiltration of files
using injection exploits.

Your page must have a title like every other page you create on your
site, and this is just like the 'change the last page' step that you
started with. 

Most the features of picosite are added to pages with
syntax like the title one. Later steps need pages but multiple
features can actually go in one page.

= Include a file into a page =

Then you might want to include something in a page, as such you can use
this special syntax:

 %%##incld=filename.txt

(Don't forget to not put a space at the start of the include syntax
line)

You can include any page you want however I because of restrictions
of page filenames not allowing slashes (/) you can't include files
from subdirectories. Files it includes have to be in the same directory
as main.page but I may change this.

= Configuring the site menus =

There is an automatic menu where all pages you have created are placed.
It displays a line of links on every page. These links are put in the
order of the alphabet. These links are on all pages, and display as a
line. The automatic links are in alphabetical order, this means as more
pages are created existing links may be displaced. This is
counter-intuitive for end users using the site.

You can create another links bar which takes links out of the automatic
links lists. They are placed in a list which is above the automatic
list of links. This is the main links bar, and they display in the
order of your choice.

To do this create the file 'menulayout.txt' and on each line specify
the filename of the page you want in the menu. This top menu will
display in the order of the menu layout file. 

= Creating a Blog =

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
GET variable to page.php. Blogs appear in the order of newest to oldest
with the date displayed. This information comes from the file creation
date on the filesystem.

You might also want to put the page you made for blogs in the menulayout.
The blog you have created should display under 'latest blog post' and
this is a nice feature so people can easily see if you've done anything
new.

= More Languages =

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

= A list of products with prices =

After this one might want to list some products on their site, and this
system is currently hardcoded to display an estimate in Bitcoin and
Monero of what the products will cost. Firstly, there is a file called
getprices.php that came with picosite. This runs with PHP-CLI and of
course you need cron. It will put the prices in a folder called 'data'
and the files for prices will be called 'xmrprice' and 'btcprice'.

At the top of 'page.php' there is a global variable where you can set
the currency symbol. It is called CURRENCY_SYM and you can replace the
dollar sign in it with your currency.

To have a price list one must create a page for it and use some syntax
to show the list. Like the blogs, you should put something around it
and there should be a title for the page. You should describe your
products and such. The syntax for showing the price list is:

 %%##price=all

This has the 'all' part which for now is all picosite can do with its
pricing system. Eventually one will be able to get an individual price
out the of the pricelist. With this I thought ahead, and when the new
feature comes it won't break people's sites.

One can not yet use a a specific price, and it can get prices from two
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

= Guides or a list of subpages =

Starting out I made this to display an alphabetized list of guides to
tell people how to use my products. The code however is capable of
being made far more extensible. For now it is called guides as a relic
of where this code began. As such it only supports one page being like
it. It will be made in a future release into 'subpages' and allow one
to have pages with lists of other pages in respective directories.

For now you just create a folder called 'guides' and like the blog and the
price system, you use some special syntax on a page you create. As such
I recommend creating 'guides.page' and using this:

 %%##guide

This will trigger calling a picosite fuction that displays the guides
on a page. Once you have done this a folder called 'guides' is required
and must have files in it. These pages support all the same syntax as
other pages and can use HTML, and eventually BBCode.

In the guides folder which you have placed in your home directory, you
must create files with a special extension just for guides. One can
also make guides in other languages support in the language list. And
these are found with a special extension with a machine name for the
language in it. This machine name is usually the country code.

A guide in the main language:

guidename.guide.page

And a guide in German:

guidename.de.trans.guide.page

Replace 'guidename' with one of your own and remember that you can only
use lowercase and uppercase letters with numbers, and the only permit-
ted symbol is the dash (-) which is generally for spacing.

= Editing the theme =

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
