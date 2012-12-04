# Venne:CMS based on Venne:FRAMEWORK

[![Build Status](https://secure.travis-ci.org/Venne/cms-module.png)](http://travis-ci.org/Venne/cms-module)

![Venne:CMS](http://sphotos-e.ak.fbcdn.net/hphotos-ak-ash4/383535_488937831131218_1478109251_n.jpg)


## Installation

### By Composer

The best way to install Venne:CMS is create new project using
[Composer](http://doc.nette.org/composer):

	composer create-project venne/sandbox:2.0.x-dev myApp -s dev && cd myApp
	composer require venne/cms-module:2.0.x [--prefer-dist]
	php www/index.php venne:module:update
	php www/index.php venne:module:install cms [--noconfirm]

### Manual

Next way to install Venne:CMS is download and unpack [archive](https://github.com/Venne/cms-module/downloads).

## Modules

### Installation by composer

	composer require [name:version]

### Manual installation

Module can be installed manualy. Download archive from GitHub and unpack it into `/vendor/venne`.

### Commands

	php www/index.php venne:module:list                   # List modules
	php www/index.php venne:module:update                 # Update local database of modules
	php www/index.php venne:module:install <name>         # Install module
	php www/index.php venne:module:uninstall <name>       # Uninstall module
	php www/index.php venne:module:upgrade <name>         # Upgrade module

### Example of module installation

	composer require venne/sample-module:2.0.x [--prefer-dist]      # Download module
	php www/index.php venne:module:update                           # Update local database of modules
	php www/index.php venne:module:install sample                   # Install module

### Next official useful modules

- [**Ckeditor**](https://github.com/Venne/ckeditor-module) - Integration of Ckeditor.
- [**Blog**](https://github.com/Venne/blog-module) - Basic module for blog system.
- [**Mailform**](https://github.com/Venne/mailform-module) - Module with dynamic mailform page.
- [**Social**](https://github.com/Venne/social-module) - Module with support for social sites.
