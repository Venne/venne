Venne:CMS based on Venne:FRAMEWORK
==================================

Installing
----------

The best way to install Venne:CMS is create new project using
[Composer](http://doc.nette.org/composer):

	curl -s http://getcomposer.org/installer | php
	php composer.phar create-project venne/sandbox myApp
	cd myApp
	php prepare
	php composer.phar require venne/cms-module:2.0.x-dev
