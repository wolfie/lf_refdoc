# LightFrame Documentation Layout Example

Big words for pityful projects. This is just a reference implementation on how
to use the LightFrame documentation. The documentation is included as a [git
submodule](http://www.kernel.org/pub/software/scm/git/docs/git-submodule.html).
You may need to run `git-submodule update` to get the documentation too.

# Install

This repository doesn't include a `settings.php` for obvious computer-specific
reasons. Create your own. No database stuff needed.

There are three additional constants that need to be defined:
**`PAGE_CACHE`**, **`PAGE_SOURCE`** and **`MARKDOWN_FILE`**.

**`PAGE_CACHE**` is a directory where the web server can write its page cache.
In Linux, this means a full `0777`-access.

**`PAGE_SOURCE`** is where the manual pages are. In this case, it's
"`LF_PROJECT_PATH.'/lightframe-documentation'`".

**`MARKDOWN_FILE`** is the exact location of markdown.php from [my extra
special edition of PHP Markdown
Extra](http://github.com/wolfie/php-markdown/tree/extra). I guess I could
include it as a submodule, but I can't be bothered right now

# License

[Creative Commons by-nc-sa 3.0](http://creativecommons.org/licenses/by-nc-sa/3.0/)

