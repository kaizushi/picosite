=== PICOSITE README ===

=== Introduction ===

This is picosite, it is a CMS that relies on one uploading files or using
and editor with SSH. It basically searches for files of specific names and
protects against injection by having these names hardcoded, and there is
a regexp which only allows files to have certain characters. 

I kept updating it without any version control, so the versions older than
what is here are not-existent.

=== Features ===

* Compatibility, this software is written in a way that the way you configure
	it one will always have full backward compatability. This is
	considered at every step of development. There are no depreciated
	features for this software. However some new features will make old
	ones redundant sometimes. However, the old features stay.
* Picocalls, there are only a half dozen of them so far but they put power
	in the pages you create. Features like Pages, Include, Blogs, and
	Subpages, Guides, and Price Lists can be called with a picocall and
	if you have created the files and folders they depend on, they will
	work and build your site. You can't really call it 'programming'
	and it is very easy.
* Pages, as such pages just sit in the same file as page.php which is the
	brains of picosite. 
* A static menu, a file called menulayout.txt can put Pages in a top menu
        where the order is preserved as what you put in menulist.txt
* A automatic menu, which is every page except those in the static menu.
        They are sorted alphabetically.
* Include, there is a picocall to include some other file in a page.
	I find this useful for doing a canary or providing GPG keys.
* Translations, these also sit in the directory with page.php and there is
	a text file for a list of languages.
* Blogs, a directory called 'blogs' can be created and files placed within
	for adding to the blog. It uses the modification time of the file
	to know the date of a blog. There is a picocall to put it in Pages
	which includes a list of blogs, and also it displays a blog when
	a blog that exists is specified by name in the GET value 'b'.
	Blogs now have experimental multi-language support that is not yet
	tested.
* Latest blog post, in the header of every page unless you are viewing
        a blog post or the listing of blogs - is the latest blog post.
* Subpages, these are replacing guides and are similar code. They allow one
	to make a picocall that on a page will show a list of other pages
	in a directory you create. The picocall names the directory and
	you can then put pages in it. They render like all other pages on
	the site.
* Guides, guides can exist in a folder called 'guides' and they support
	translations. Much like blogs there is a picocode for the Pages
	which shows a list of guides or if GET value 'g' is passed an
	actual guide if the file exists. [Now this is Subpages]
* Pricing system, if you write a cron job to get Bitcoin and Monero
	prices from somewhere and put them in files 'xmrprice' and
	'btcprice' you can display a list of prices for various
	products. There is a picocode for Pages which makes a list of
	prices to be displayed. Products are listed in the file
	itemlist.txt with prices in the currency that your cron
	job fetches. The price list is displayed in the same order
	as the item list.
* Security, having had trouble with vandals of PHP software and some
	malware, I wrote this so nothing could go wrong. As such the
	site has no editor, there is no code which can alter the site
	in picocms. One has to log in with SSH and create files.

=== Coming Soon ===

* BBCode, you will soon have an alternate parser however it will render
	HTML in pages unusable. It will be instantiated by a picocall
	that can be placed anywhere in a page. With this picosite will
	let you use BBCode to write pages. 
* Stability, this documentation may make promises that are not kept yet,
	but for every bug there is a workaround. The problems with this
	software however are simple. They also have no impact on security.
* Efficieny, this software has some redundant code and almost identical
	methods. These will be broken up into more methods.

=== Using picocms ===

= Install the script =

One starts by uploading page.php to a web directory. Then one has to
create some files for pages. As such the pages feature is the only
fundamental one. Blogs, guides, even the static menu are all optional
and one can ignore these features, just by not using their 'special'
syntax and not creating any pages for them.

= Set the Site Name and Logo =

There are some config settings at the top of page.php which control
the site. One is for the site name and you can also set others. You
might also want to change the logo graphic. 

There is also a image of my mascot Rainbow Dash in the corner, when you
get to the end it tells you how to change the CSS. It is very easy to
remove and you could just delete the file. However then your webserver
will get a 404 for every request.

= Change the Main Page =

To change your first page edit a file called main.page and wipe the
one which came with picosite. This page is the 'home' of your website.
This page is required and you will get a 404 if you delete it. Set the
title of the page wish the picocode below:

 %%##title=The Page Title

This is the first picocode you learn and The Page Title can be anything
you like. This is required on all Pages you create. In future the page
title will be able to handle HTML. For now the HTML works somewhere but
in the title bar it would display. The trick will be to filter it out
so one can put emphasis on headings and menu items.

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

The filename you give a page must end in .page like the main page and
I call it the nodename. This is a machine name used throughout the
picosite system. If the machine name was 'burgers' then the page
would be named as such:

burgers.page

Don't forget when you create a page to make a title for it as this is
very much required. This uses the title picocode, and once again there
is no space at the start like this document has.

Most the features of picosite are added to pages with a picocode. You
will be learning the rest as you read.

= Include a file into a page =

Then you might want to include something in a page, as such you can use
this picocode:

 %%##incld=filename.txt

You can include any page you want however I because of restrictions
of page filenames not allowing slashes (/) you can't include files
from subdirectories. Unlike machine names for Pages these includes can
go anywhere on the filesystem. This is not insecure as you have to
write the picocode into your files yourself.

= Configuring the site menus =

There is an automatic menu where all pages you have created are placed.
It displays a line of links on every page. These links are put in the
order of the alphabet. It was an issue the new links would move others
which is not intuitive.

Because of that issue of links moving, I made another links bar for the
site which only exists if the file 'menulayout.txt' exists. This links
bar displays above the automatic menu. It renders in the order of your
menulayout file, top to bottom goes left to right.

A menulayout.txt uses the machine names for pages. At this point they
are not able to render pages in subpage directories.

= Creating a Blog =

So next you might want a blog on the site, so to do that create a
page, I suggest you call it blog.page and it must have this picocode...

 %%##blogs

This will not work until you do two more things. You must also create
a folder called 'blogs', and create a blog inside it. I suggest you don't
enable this feature until you have something to blog about.

A blog has a nodename in its filename just like Pages. They must exist
in the blogs folder. With blogs you may wish to take advantage of the
picosite nodename capability for numbers. One may find after they have
made many blogs that they want to use the same nodename again. So from
the start one should perhaps put a number at the start or end of the
blog filename.

A blog with nodename 'secrets-01' must have a file with this kind of 
filename as an example

secrets-01.blog.page

Your blog posts will display when you are on the page you created for
blogs. The creation date lists them newest to oldest and also is on
the list for all to see. Blogs now support multiple languages.

= More Languages =

Now you might want to have some other languages supported by your site.
For that you have to create a file called 'languages.txt' and this file
is a list of lines with a comma in them. The comma seperates a machine
name for the language from a human readable one. The human names will
display at the very top of the page.

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

As for Blogs, Guides, and Subpages one can also put .trans. before
their node type name. A blog called '74-manifesto.blog.page' in the
blogs folder, for German one could make one with the name:
'74-manifesto.de.trans.blog' - it is important the machine name at
the beginning stays the same.

One can switch language when the translation exists easily by clicking
on the name of it (such as German) on the top bar. Although the machine
name must be the same - one can have different titles between languages.

= A list of products with prices =

After this one might want to list some products on their site, and this
system is currently hardcoded to display an estimate in Bitcoin and
Monero of what the products will cost. Firstly, there is a file called
getprices.php that came with picosite. This runs with PHP-CLI and of
course you need cron. It will put the prices in a folder called 'data'
and the files for prices will be called 'xmrprice' and 'btcprice'.

To create a cronjob from a Linux shell on most systems one just runs a
command called 'crontab -e' which opens cron in your system editor.
One can create a line as such in the file to check cron every five
minutes:

*/5 * * * * php getprices.php

At the top of 'page.php' there is a global variable where you can set
the currency symbol. It is called CURRENCY_SYM and you can replace the
dollar sign in it with your currency. You can edit the USD variable in
getprices.php called BASECUR. 

To have a price list one must create a page for it and use some syntax
to show the list. Like the blogs, you should put something around it
and there should be a title for the page. You should describe your
products and such. The picocode for showing the price list is:

 %%##price

This will list every price in a file you create called 'itemlist.txt'
which is comma seperated. It has a human readable name and then a price
after the comma. For example:

Car Wash,25
Detailing,70

This will create two products one Car Wash for 25 units of the base
currency your cronjob uses, and another for Detailing for 75. These
will display in a nice list. It is ordered the same as the order of
the files you created. 

One can also enable a feature which displays prices they have dropped
and older prices. One could do a sale and offer discounts with this
feature. The format is the same as the main list. One just has to crea-
te a new folder called 'itemlist-old.txt' and a new column will appear
on the pricelist table.

The older prices must match in human readable names in their respective
lists. If there is no older price it will read as having an old price of
$XX.XX (the dollar sign at the start will change if you configure your
currency symbol). 

A coming feature is being able to put an equals after the picocode for
doing a price. It will enable you to select products and display them
on any page of your site. 

= Guides =

You may wish to skip to subpages below.

(Guides are being depreciated with the more versatile subpages. However
backward comptability will remain. If you have a guides folder and
there are guides in it they will render with Subpages)

I started this site to run my dark web hosting. As such I have to have
helpful articles to give directions to my customers. So I made a pretty
static and ugly feature called 'guides' for picosite.

One can make a page for guides and put this picocode in it:

 %%##guide

This will trigger calling a picosite fuction that displays the guides
on a page. Once you have done this a folder called 'guides' is required
and it needs files with specific names. If you were to name on of your
guides 'install-gentoo' the filename should be as such:

install-gentoo.guide.page

This will create a guide that will appear on the page made for guides.

And a guide in German for examplel:

install-gentoo.de.trans.guide.page

= Subpages =

Something less static that guides is subpages where one can create any
amount of different categories. Much like guides they display in an
alphabetical order. For each category of subpages you make a Page in
the base directory of your site. Its machine name must match the name
of a folder you also create.

You could create a file called 'poems.page' in your main directory and
then a folder with it. If your folder was 'poetry' you could use this
picocode to create its root subpage.

%%##subpg=poetry

The Page you do this to is a root subpage and it will display a list of
files you create in our directory. The files in the directory like most
different types of files in picosite have a specific extension after a
machine name. The title as per usual will set the title on the page and
in the list of subpages.

To create a poem called 'i-love-her' as the machine name one makes a
file in their 'poetry' folder like this for example:

i-love-her.sub.page

This will create the poem for the Root Subpage and when you click on it
in the list, you will see the poem. You can also do these in languages
in a similar way to most other pages. For example:

i-love-her.de.trans.sub.page

A coming feature is being able to reference a subpage from any page,
other subpage, blog, and so on. Basically anywhere picocodes render a
result.

You can have as many root subpages as you want and you can have as many
pages in their directories as you want. As a root subpage is just a
normal page with a picocode they will show in the menu. One can put
these in their menulayout.txt

= Editing the theme =

Now for the last part, where you customise your page.php to look the
way you want it to look. If you go to the bottom of page.php you will
see the HTML code. It is very short, in a future release this may be
included instead from a template.php file much the same.

With the HTML code one may want to customise it. What comes with the
site is very simple. If you have a look you should find is straight-
forward enough. It renders things within the template with functions in
PHP which start with 'print.'

However if you call methods within my PHP and not limit yourself to the
ones I have used in the HTML after the <head>, you may create terrible
bugs.

Yet this picosite is secure, even from you, as such a malfunction of
the software should only cause bad rendering. It will not do anything
that changes your system. There is no such code, and as such one can
experiment a lot with picosite.

To make experimenting easier I will develop a 'reference.txt' and you
should go have a look because it might just be there.

Anyway, if you have followed all of this guide you have done everything
in picocms possible, except its infinite page capacity. I hope you enj-
oy it because it can't make changes to the system. That is a point for
its security bonus. 

More will come in terms of features, and some things will change. If
you see an error in the docmentation or a bug in the code email me at
kaizushi@infantile.us or kaizushi@cock.li
