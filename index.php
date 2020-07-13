<?php 

//CONFIG
include("Localbox.php"); //enter path to Localbox class, or a configuration file with a class autoloader like spl_autoload_register https://www.php.net/manual/en/function.spl-autoload-register.php
//END CONFIG

$localbox = new Localbox;

if ($localbox->status == 'installed') {

	if ((isset($_GET['iframe'])) && (is_numeric($_GET['iframe']))) {

		$view = 'iframe';
		$email = $localbox->getMessage((int)$_GET['iframe']);

	} else if ((isset($_GET['email'])) && (is_numeric($_GET['email']))) {

		$view = 'single';
		$email = $localbox->getMessage((int)$_GET['email']);		

	} else if ((isset($_GET['archive'])) && (is_numeric($_GET['archive']))) {

		$localbox->archiveMessage((int)$_GET['archive']);
		header("Location: index.php?archive_complete=y");

	} else {
		
		if (isset($_GET['view'])) {			
			$view = preg_replace("/[^a-z]/", "", $_GET['view']); //only allow lower-case alpha characters															
		} else {
			$view = 'inbox';
		}

		if ($view == 'inbox') {
			$emails = $localbox->getInbox();
		} else if ($view == 'archive') {
			$emails = $localbox->getInbox('y');
		}
	}
	
} else if (($localbox->status == 'uninstalled') && (isset($_GET['install'])) && ($_GET['install'] == 'y')) {
	
	$install_result = $localbox->install();
	if ($install_result == 'success') {
		header("Location: index.php?install_complete=y");
	} else {
		$install_error = $install_result;
	}
}

?>
<?php if ((isset($view)) && ($view == 'iframe')) { ?>
<?php echo $email['message']; ?>
<?php } else { ?>
<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>PHP Localbox</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.2/normalize.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
		<style>
			p.alert {
				color: darkred;
			}
			p.notify {
				color: darkgreen;
			}
			p.notify:empty:before {
				content: "\200b";
			}
			.heading-wrapper {
				text-align: right;
			}
			h1.heading {
				color: #66bb6a;
			}
			.button.button-primary {
				background-color: #66bb6a;	
				border-color: #4caf50;
			}
			.button.button-primary:hover, .button.button-primary:focus {
				background-color: #2e7d32;
				border-color: #2e7d32;
			}
			a, a:link, a:visited {
				color: #66bb6a;	
			}
			a:hover, a:active {
				color: #2e7d32;
			}
			@media (max-width: 1000px) {
				.hide-below-desktop {
					display: none;
				}
			  }
			@media (max-width: 550px) {
				.hide-below-phablet {
					display: none;
				}
				.heading-wrapper {
					text-align: left;
				}
				h1.heading {
					margin-top: 1rem;
					margin-bottom: 0;
				}
			  }
		</style>
	</head>
	<body>
	<div class="container" style="margin-top:50px; max-width: 1200px">
		<div class="row">		
			<div class="row">				
				<div class="six columns">
					<div style="width: 100px; height: 50px; border: 10px solid #66bb6a;">
						<div style="width: 0; height: 0; border-left: 50px solid transparent; border-right: 50px solid transparent; border-top: 50px solid #66bb6a;"></div>
					</div>
				</div>
				<div class="six columns heading-wrapper">
					<h1 class="heading">Localbox</h1>
				</div>
			</div>
			<?php if ($localbox->status == 'database_variables_undefined') { ?>
				
				<p>Welcome to Localbox!  Fill in the database variables at the top of the Localbox.php class file to get started.</p>
			
			<?php } else if ($localbox->status == 'uninstalled') { ?>
			
				<?php if ((isset($install_error)) && (strlen($install_error)>0)) { ?><p class="alert"><?php echo $install_error; ?></p><?php } ?>
				<p>Ready to get started?  Click the button below to install the Localbox database table</p>
				<p><a href="index.php?install=y" class="button button-primary">Install</a></p>
			
			<?php } else if ($localbox->status == 'installed') { ?>						
			
				<p class="notify"><?php if ((isset($_GET['archive_complete'])) && ($_GET['archive_complete'] == 'y')) { echo 'Message archived!'; } else if ((isset($_GET['install_complete'])) && ($_GET['install_complete'] == 'y')) { echo 'Install complete.  You are good to go!'; } ?></p>		
				<a href="index.php?view=inbox" class="button <?php if ((isset($view)) && ($view == 'inbox')) { ?>button-primary<?php } ?>" >Inbox</a> <span style="margin:0 5px;"></span>
				<a href="index.php?view=archive" class="button <?php if  ((isset($view)) && ($view == 'archive')) { ?>button-primary<?php } ?>" >Archived</a>

				<?php if ($view == 'single') { ?>
					<table class="u-full-width">				
						<tr>
							<td><strong>To:</strong></td>
							<td><?php echo $email['recipient']; ?></td>
						</tr>
						<tr>
							<td><strong>From:</strong></td>
							<td><?php echo $email['sender_name'] . ' &lt;' . $email['sender'] . '&gt;'; ?></td>
						</tr>
						<tr>
							<td><strong>Reply-To:</strong></td>
							<td><?php echo $email['reply_to']; ?></td>
						</tr>
						<tr>
							<td><strong>Subject:</strong></td>
							<td><?php echo $email['subject']; ?></td>
						</tr>
						<tr>
							<td><strong>Time:</strong></td>
							<td><?php echo date("r (T)", strtotime($email['time_sent'])); ?></td>
						</tr>
						<?php if (strlen($email['headers'])>0) { ?>
							<tr>
								<td><strong>Headers:</strong></td>
								<td><?php echo $email['headers']; ?></td>
							</tr>
						<?php } ?>
						<?php if ($email['archived'] == 'n') { ?>
							<tr>
								<td><a class="button button-primary" href="index.php?archive=<?php echo $email['localbox_id']; ?>">Archive</a></td>
								<td>&nbsp;</td>
							</tr>
						<?php } ?>
					</table>
					<div class="row">					
						<iframe style="display: block; border: none; height: calc(100vh); width: 100%;" src="index.php?iframe=<?php echo $email['localbox_id']; ?>"></iframe>
					</div>

				<?php } else { ?>
					<?php if (sizeof($emails) == 0) { ?>
						<div class="row" style="margin-top:50px;">
							<?php if ($view == 'archive') { ?>
								<p>Nothing in the Archive yet!</p>
							<?php } else { ?>
								<p>Your Localbox is empty! Don't you wish inbox zero was this easy with real email?</p>
							<?php } ?>
						</div>
					<?php } else { ?>
						<?php if (($view == 'archive') && (sizeof($emails) == 100)) { ?>
							<p style="margin-bottom: 0;">Displaying the most recent 100 messages</p>
						<?php } ?>
						<table class="u-full-width">
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th class="hide-below-desktop">&nbsp;</th>
									<th class="hide-below-phablet">To</th>
									<th class="hide-below-desktop">From</th>
									<th>Subject</th>
									<th class="hide-below-phablet">Time</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($emails as $email) { ?>
								<tr>
									<td><a href="index.php?email=<?php echo $email['localbox_id']; ?>">View</a></td>
									<td class="hide-below-desktop"><a href="index.php?archive=<?php echo $email['localbox_id']; ?>">Archive</a></td>
									<td class="hide-below-phablet"><?php echo $email['recipient']; ?></td>
									<td class="hide-below-desktop"><?php echo $email['sender_name'] . ' &lt;' . $email['sender'] . '&gt;'; ?></td>
									<td><?php echo $email['subject']; ?></td>
									<td class="hide-below-phablet"><?php echo date("n/j/Y H:i:s", strtotime($email['time_sent'])); ?></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					<?php } ?>
				<?php } ?>
			
			<?php } ?>
		</div>
	</div>
	</body>
	</html>
<?php } ?>
