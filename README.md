rebus-widget
============

Widget generator for Rebus:List from PTFS Europe

You will need the following settings and dependencies to get things up and running:

Edit SU/config.ini to include the uri for your Rebus api
The "SU" code will need to be on the PHP include path
The "public" folder of "widgets" will need to be available via a http server

Zend (We use the http client but you could quite easily strip this out and do it all in standard php)
Mustache (A template engine - Again, you could replace this with any template system or simply output html via php
in the controller) - This is stored in widgets/vendor and we use php composer to maintain it
