REElocate
=========

ExpressionEngine stores your website URL and Server Path in a ridiculous
number of places. REElocate helps you update these in one simple step.

Often when you are developing an EE site, you will have it running on a
test URL or test server, while your client edits content, tests features etc.

When you move to the production location, you find out that ExpressionEngine
stores your sites URL in hundreds of places, as well as the server path (document root).
OK, maybe that is a slight exaggeration, but on average REElocate finds over
30 settings which need updating. Worse, these are spread between many different
configuration pages all over the place - from regular settings, channel settings,
upload settings etc… the list goes on.

Installation
------------

To install REElocate, simply upload the entire 'reelocate' module directory to
`./system/expressionengine/third_party/reelocate` on your web server. You should then be able
to find REElocate in the modules list of your control panel (under Add-ons > Modules).

Changelog
---------

**1.2** *(2011-03-08)*

* Updated directory structure, and updated code to PHP5 syntax

**1.1** *(2010-10-04)*

* Added detection for many more URLs and paths stored in channel preferences

**1.0** *(2010-09-21)*

* Initial release