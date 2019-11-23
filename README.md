# picosite
## Current Version
The current version is 1.1.3 and it was released on the 23rd of November 2019, twice, because of bugs pushed to master. All is well now!

## Introduction
A secure CMS / site generator, which only knows how to read files you create. It has no code that can write to the system. It just checks for files.

If you see this line you are in the 'demosite' branch. The only pull request I should get for this is for a blog post about your shiny new [feature](https://picosite.kloshost.online/page.php?q=contribute) and do your picocode for the title right.

In only roughly 750 lines of code!

There are two branches 'master' is stable and so you can clone right away. There is another branch where I iteratively change stuff and see what happens called 'fiddling' because I just fiddle around with stuff.

There are sometimes brances for big feature introductions.

## Installation

### Mininum

Copy page.php to the remote server and create `main.page` and other page files. You can use picocodes to create your site. Use `REFERENCE.txt` (incomplete) to find a reference of picocodes.

### Standard

Copy everything to your remote server for hosting the website. Then move your getprices.php and secure the site, where you will want to make it so the webserver can't serve up `.page` files on the site. You might also want to delete README.txt and REFERENCE.txt to stop versioning. Read README.txt on your computer or preserve it somehow to learn how to configure picosite.

## Author Bio

Who is Kaizushi?

I a woman who has escaped from the hell on Earth that is Arabia. I live in a nice secular society now, and I live for Agorism. I run Kaizushi's Little Onion Server which I consider to be free of any jurisdiction. It is hosting, shell accounts, a communication service, and more.

You can find me through the Tor network with this address, and see picosite in action...
http://kaizushigdv5mrnz.onion/ - picosite

