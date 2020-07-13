# PHP Localbox
Your inbox for local development.  View emails without actually sending any email!

PHP Localbox is a simple two-file PHP/MySQL application.  You can easily incorporate it into your existing workflow to route all of your email messages into your Localbox when developing locally.  Messages are stored in a database table instead of actually being sent.  

You can then view them in your Localbox that is designed to look and feel like an actual inbox.  Localbox supports both plain text and HTML email messages, and has a responsive design for testing on mobile devices.

## Screenshots

Here's what a Localbox "inbox" looks like:

![Localbox Inbox](/screenshots/inbox.png?raw=true "Localbox Inbox")

And here's what an HTML message looks like:

![Localbox Message](/screenshots/message.png?raw=true "Localbox Message")

## Requirements

PHP Localbox was developed for PHP 7+ and MySQL 5.7+ (or equivalent MariaDB database), however it should work on earlier versions.

## Installation

To get started you'll need a database user with SELECT, INSERT, UPDATE, and CREATE privileges.  

In the ```Localbox.php``` file, fill out the database information. At a minimum you'll need to enter the user, password, and name.

If your application autoloads classes using something like [spl_autoload_register]( https://www.php.net/manual/en/function.spl-autoload-register.php), you can move the ```Localbox.php``` class file over to that directory.  Then in the ```inbox.php``` file change the ```include``` path to the path to your configuration file that includes the autoloader. 

Alternatively, you can ```include``` the ```Localbox.php``` class each time that you use it.  In this case you do not need to adjust the ```index.php``` file.

Open the *index.php* file in your browser and click the *Install* button.

## Demo Messages

Run the ```demo.php``` script to insert three test emails into your Localbox: a plain-text message, an HTML message, and a basic HTML message.

Open the ```index.php``` file to see those three messages in your inbox.  You can click to view them or archive them to remove them from the inbox.

## Basic Usage

In your codebase




## FAQ

## About

## License
