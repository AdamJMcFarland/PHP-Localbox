<?php

//run this script after installation to insert three test messages into your Localbox

//CONFIG
include("Localbox.php"); //enter path to Localbox class, or a configuration file with a class autoloader like spl_autoload_register https://www.php.net/manual/en/function.spl-autoload-register.php
//END CONFIG


$localbox = new Localbox;

if ($localbox->status == 'installed') {

	//Example #1 - a simple plan text email with a line break
	$sender = 'sender@yourdomain.com';
	$recipient = 'recipient@yourdomain.com';
	$subject = 'My First Message';
	$message = "This is an example of a plain text email with line breaks.\n\nThis is on a different line.";

	$localbox->Mail($sender, $recipient, $subject, $message);

	//Example #2 - HTML content
	$sender = 'sender@yourdomain.com';
	$sender_name = 'Sender Name';
	$recipient = 'recipient@yourdomain.com';
	$subject = 'My First HTML Message';
	$reply_to = 'reply@yourdomain.com';
	$message = '<html>
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
				<title>Title</title>
				</head>
				<style type="text/css">
				a:link, a:hover, a:active, a:visited {
						color:#042890;
						text-decoration:none;
					}

				body {
					font-size:10pt;
					font-family:Arial, Helvetica, sans-serif;
				}

				img {
					border: none;	
				}

				table{
					border-collapse: collapse;
					border-color: #DDD;
					border-spacing: 0;
					border-style: solid;
					border-width: 0 0 1px 1px;
				}

				table td{
					border-color: #DDD;
					border-style: solid;
					border-width: 1px 1px 0 0;
					margin: 0;
					padding: 4px;
				}

				</style>
				</head>

				<body>
				<h1>Heading</h1>
				<p>Paragraph!</p>
				<ul>
				<li>List</li>
				<li><a href="https://phplocalbox.com">PHP Localbox home page link</a></li>
				</ul>
				</body>
				</html>';

	$headers = '';
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: ' . $sender_name . '<' . $sender . '>' . "\r\n";
	$headers .= 'Reply-To: ' . $reply_to . "\r\n";			
	$headers .= 'Return-Path: abuse@detailedimage.com' . "\r\n";	


	$localbox->Mail($sender, $recipient, $subject, $message, $sender_name, $reply_to, $headers);
	
	//Example #3 - HTML content without headers or a <html> declaration
	$sender = 'sender@yourdomain.com';
	$sender_name = 'Sender Name';
	$recipient = 'recipient@yourdomain.com';
	$subject = 'My Second HTML Message';
	$reply_to = 'reply@yourdomain.com';
	$message = '<p>A super simple HTML message, without the <em>html</em> tag or headers, but still displays as HTML and <strong>not</strong> text.';
	$headers = '';		
	
	$localbox->Mail($sender, $recipient, $subject, $message, $sender_name, $reply_to, $headers);
	
	echo 'Success! Check your Localbox for three new messages';
	
} else {
	echo 'Install Loclbox first before running this script';
}
?>