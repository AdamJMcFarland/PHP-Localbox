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

Open the ```index.php``` file in your browser and click the ```Install``` button. A table named ```localbox``` will be created in your database.


## Demo Messages

It's helpful to start with a few demo messages.

Run the ```demo.php``` script to insert three test emails into your Localbox: a plain-text message, an HTML message, and a basic HTML message.

Open the ```index.php``` file to see those three messages in your inbox.  You can click to view them or archive them to remove them from the inbox.

## Basic Usage

In your codebase, initiate the ```Localbox``` class and send a message.

```php
$localbox = new Localbox;
$localbox->Mail($sender, $recipient, $subject, $message, $sender_name, $reply_to, $headers);
```

The ```Mail()``` function has four required parameters and three optional parameters:

Parameter | Required | Description
--- | --- | ---
```$sender``` | Yes | The email address of the sender
```$recipient``` | Yes | The email address of the recipient
```$subject``` | Yes | The subject line
```$message``` | Yes | The message content. Plain text and HTML are acceptable (edit ```$message_allowable_tags``` in ```Localbox.php``` to add/remove allowable HTML)
```$sender_name``` | No | The name of the sender
```$reply_to``` | No | The reply-to email address.  If left blank, this will be the ```$sender```
```$headers``` | No | A string of additional headers, separated by a CRLF (Carriage Return Line Feed) ```"\r\n"```

Localbox was designed to integrate into your existing workflow.  Instead of attempting to send an email when developing locally, you can instead save that message to your Localbox.  For example, you might have defined a variable ```LOCAL_ENVIRONMENT``` that is set to ```TRUE``` when developing locally and false otherwise.

In the past you might have:

```php
if (!LOCAL_ENVIRONMENT) {
  //send email using PHP's mail() function, PHPMailer, or any other method
}
```

Whereas with Localbox you can do:

```php
if (!LOCAL_ENVIRONMENT) {
  //send email using PHP's mail() function, PHPMailer, or any other method
} else {
  $localbox = new Localbox;
  $localbox->Mail($sender, $recipient, $subject, $message, $sender_name, $reply_to, $headers);
}
```

## FAQ

**Does Localbox actually send any email?**
Nope, it's just taking the parameters of a message, storing it in a database table, and then presenting them in an inbox-like format to simulate email.

**How is the email rendered?**
The email is included in Localbox using an ```iframe```.  It is displayed as it would be if you outputted the contents into the web browser that you're using to access your Localbox.

**Can this help with testing design in different email clients?**
Nope.  You'll probably need to test each email client with real emails.  Once you've got your design down though, Localbox is great for testing whether you're sending the correct content.  For instance, you might send customers different order confirmation emails based upon the products that they ordered.

**How do you differentiate between plain text and HTML messages?**
We check the message content to see if any of the allowable HTML tags defined in  ```$message_allowable_tags``` are included.  

**Do the headers actually do anything?**
Nope.  They're just displayed for your reference.

**Why doesn't my HTML email look right?**
Try double-checking the ```$message_allowable_tags``` variable at the top of ```Localbox.php```.  The default tags cover most commonly used tags, however you may need to add some more to get your email displaying properly.

**Is it responsive?**
Yup.  Try viewing it on your phone to see how messages would look when viewed in a mobile client.

**Can I delete messages?**
Not currently.  The archive feature is meant to accomplish this.  Once you archive a message it goes to the ```Archived``` tab, which displays the most recent 100 archived messages.  You can always open up your database and delete an invidual message from the ```localbox``` table.

## About
PHP Localbox was created by [Adam McFarland](https://www.adammcfarland.com/) for internal use within [Pure Adapt, Inc](https://www.pureadapt.com/).  Special thanks to the [Skeleton](http://getskeleton.com/) CSS framework, which is used for the layout of Localbox.

## License
This software is distributed under the [MIT License](LICENSE)
