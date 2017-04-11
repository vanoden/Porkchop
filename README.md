Porkchop CMS
===========

The Porkchop CMS is a content management system I've built over many years and used on many different projects.  It can act as a set of web services or as a complete web site.

The CMS is divided into 'modules' and 'views'.  Each url contains the name of a module with a leading underscore followed by the name of a view.  The engine parses the URL on forward slashes and loads the appropriate view for the module.  It parses the remainder of the url as parameters.

For example:

 /_register/account/bobdole

would load the 'account' view of the 'register' module populated with account information for the user 'bobdole'.

*Installation

Clone the repository into a folder of your choice.  Configure your web server to use the 'html' subfolder as it's document root.

You will need to configure the server to rewrite urls beginning with '/_' to load '/core/index.php'.  Look in /misc for a sample Apache configuration.
