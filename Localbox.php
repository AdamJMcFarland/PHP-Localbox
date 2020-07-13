<?php

class Localbox
{	
	
	//CONFIG
	private $database_host = 'localhost';
	private $database_user = '';
	private $database_password = '';
	private $database_name = '';
	private $database_port = 'default';
	private $debug = false; //set to true to show MySQL errors
	private $message_allowable_tags = '<html><head><meta><title><style><body><img><h1><h2><h3><h4><h5><p><div><a><strong><em><ol><ul><li><table><thead><tbody><th><tr><td><br>'; //allowable tags in HTML emails
	//END CONFIG		
	
	public $status;
	private $mysqli;	
	
	public function __get($name) {
		return $this->$name;
	}

	public function __set($name, $value){
		$this->$name = $value;
	}
	
	public function __construct() {
		//make sure database fields are filled out
		if ((strlen($this->database_host)>0) && (strlen($this->database_user)>0) && (strlen($this->database_password)>0) && (strlen($this->database_name)>0) && (strlen($this->database_port)>0)) {
			
			//connect to database
			if ($this->database_port == 'default') {
				$this->mysqli = new mysqli($this->database_host, $this->database_user, $this->database_password, $this->database_name);
			} else {
				$this->mysqli = new mysqli($this->database_host, $this->database_user, $this->database_password, $this->database_name, $this->database_port);
			}
			if ($this->mysqli->connect_errno) {
				if ($this->debug) {
					trigger_error("Failed to connect to MySQL: " . $this->mysql->connect_error);
				}
				exit();
			}
			if (!$this->mysqli->set_charset("utf8")) {
				if ($this->debug) {
					trigger_error("Error loading character set utf8: " . $this->mysql->connect_error);				
				}
				exit();
			}
			
			//check to see if localbox is installed
			if ($this->checkInstalled()) {
				$this->status = 'installed';				
			} else {
				$this->status = 'uninstalled';
			}
			
		} else {
			$this->status = 'database_variables_undefined';
		}
		
	}
	
	private function checkInstalled() {		
		$stmt = $this->mysqli->stmt_init();
		if ($stmt->prepare("SHOW TABLES LIKE 'localbox'")) {			
			$stmt->execute();
			$result = $stmt->get_result();
			if (($this->debug) && (strlen($stmt->error)>0)) {
				echo $stmt->error;
			}
			$stmt->close();
			if ($result != false) {				
				$num = $result->num_rows;				
				if ($num == 1) {
					return true;
				} else {
					return false;
				}
			}
		}
		return $rows;
	}
	
	public function install() {
		$stmt = $this->mysqli->stmt_init();
		if ($stmt->prepare("CREATE TABLE IF NOT EXISTS `localbox` (
							  `localbox_id` int(11) NOT NULL AUTO_INCREMENT,
							  `time_sent` datetime NOT NULL,
							  `archived` char(1) NOT NULL DEFAULT 'n',
							  `sender` varchar(255) NOT NULL,
							  `sender_name` varchar(255) NOT NULL,
							  `recipient` varchar(255) NOT NULL,
							  `subject` varchar(255) NOT NULL,
							  `message` longtext NOT NULL,
							  `reply_to` varchar(255) NOT NULL,
							  `headers` text NOT NULL,
							  PRIMARY KEY (`localbox_id`),
							  KEY `archived` (`archived`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;")) {			
			$stmt->execute();			
			if (strlen($stmt->error)>0) {
				$stmt_error = $stmt->error;
			}
			$stmt->close();			
			if ((isset($stmt_error)) && (strlen($stmt_error)>0)) {
				return $stmt_error;				
			} else if ($this->checkInstalled()) {
				return 'success';
			} else {
				return 'Unknown error';
			}
		} else {
			return 'Check your database user to make sure that it has CREATE privileges';
		}	
	}
	
	public function Mail($sender, $recipient, $subject, $message, $sender_name = '', $reply_to = '', $headers = '') {
		//filter inputs
		$sender = filter_var($sender, FILTER_SANITIZE_EMAIL);				
		$recipient = filter_var($recipient, FILTER_SANITIZE_EMAIL);
		$subject = filter_var($subject, FILTER_SANITIZE_STRING);
		$message = strip_tags($message, $this->message_allowable_tags);		
		$sender_name = filter_var($sender_name, FILTER_SANITIZE_STRING);
		if (strlen($reply_to) == 0) {
			$reply_to = $sender;
		} else {
			$reply_to = filter_var($reply_to, FILTER_SANITIZE_EMAIL);		
		}		
		$headers = filter_var($headers, FILTER_SANITIZE_STRING);
		//insert into database
		$insert_id = 0;
		$stmt = $this->mysqli->stmt_init();
		if ($stmt->prepare("INSERT INTO localbox (time_sent, sender, sender_name, recipient, subject, message, reply_to, headers) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?);")) {
			$stmt->bind_param("sssssss", ...array($sender, $sender_name, $recipient, $subject, $message, $reply_to, $headers)); 
			$stmt->execute();
			$insert_id = $this->mysqli->insert_id;
			if (($this->debug) && (strlen($stmt->error)>0)) {
				echo $stmt->error;
			}			
			$stmt->close();
		}
		//return
		if ($insert_id == 0) {
			return false;
		} else {
			return true;
		}		
	}
	
	public function getInbox($archived = 'n') {
		$emails = array();
		
		$query = "SELECT localbox_id, time_sent, sender, sender_name, recipient, subject FROM localbox WHERE archived = ? ORDER BY localbox_id DESC";
		if ($archived == 'y') {
			$query.= " LIMIT 100";
		}
		
		$stmt = $this->mysqli->stmt_init();		
		if ($stmt->prepare($query)) {
			$stmt->bind_param('s', ...array($archived));
			$stmt->execute();
			$result = $stmt->get_result();
			if (($this->debug) && (strlen($stmt->error)>0)) {
				echo $stmt->error;
			}
			$stmt->close();
			if ($result != false) {
				$row = $result->fetch_assoc();
				$num = $result->num_rows;
				if ($num > 0) {
					do {
						array_push($emails, array(
							'localbox_id' => filter_var($row['localbox_id'],FILTER_SANITIZE_NUMBER_INT),
							'time_sent' => filter_var($row['time_sent'], FILTER_SANITIZE_STRING),
							'sender' => filter_var($row['sender'], FILTER_SANITIZE_EMAIL),
							'sender_name' => filter_var($row['sender_name'], FILTER_SANITIZE_STRING),
							'recipient' => filter_var($row['recipient'], FILTER_SANITIZE_EMAIL),
							'subject' => filter_var($row['subject'], FILTER_SANITIZE_STRING),					
						));
					} while ($row = $result->fetch_assoc());	
				}
			}
		}

		return $emails;
	}
	
	public function getMessage($localbox_id) {
		$email = array();
		
		$query = "SELECT * FROM localbox WHERE localbox_id = ? LIMIT 1";
		
		$stmt = $this->mysqli->stmt_init();
		if ($stmt->prepare($query)) {
			$stmt->bind_param('i', ...array($localbox_id));
			$stmt->execute();
			$result = $stmt->get_result();
			if (($this->debug) && (strlen($stmt->error)>0)) {
				echo $stmt->error;
			}
			$stmt->close();
			if ($result != false) {
				$row = $result->fetch_assoc();
				$num = $result->num_rows;
				if ($num == 1) {			
					$message = strip_tags($row['message'], $this->message_allowable_tags);
					//attempt to detect plan text vs html
					$allowable_tags_regex = substr(str_replace(array("<", ">"), array("\\<", "\\>|"), $this->message_allowable_tags), 0, -1);										
					if (preg_match('/(' . $allowable_tags_regex . ')/i', $message)) {											
						$message = '<!doctype html>' . "\n" . $message; //html - add in doctype declaration since strip_tags always strips it
					} else {					
						$message = nl2br($message); //plain text - allow for line breaks
					}
					$email = array(
						'localbox_id' => filter_var($row['localbox_id'],FILTER_SANITIZE_NUMBER_INT),
						'archived' => filter_var($row['archived'], FILTER_SANITIZE_STRING),
						'time_sent' => filter_var($row['time_sent'], FILTER_SANITIZE_STRING),
						'sender' => filter_var($row['sender'], FILTER_SANITIZE_EMAIL),
						'sender_name' => filter_var($row['sender_name'], FILTER_SANITIZE_STRING),
						'recipient' => filter_var($row['recipient'], FILTER_SANITIZE_EMAIL),
						'subject' => filter_var($row['subject'], FILTER_SANITIZE_STRING),
						'message' => $message,
						'reply_to' => filter_var($row['reply_to'], FILTER_SANITIZE_EMAIL),
						'headers' => strip_tags(nl2br($row['headers']), '<br>')
					);					
				}
			}
		}					
		
		return $email;
	}
	
	public function archiveMessage($localbox_id) {
		$affected_rows = 0;
		
		$query = "UPDATE localbox SET archived = ? WHERE localbox_id = ? LIMIT 1";
		
		$stmt = $this->mysqli->stmt_init();
		if ($stmt->prepare($query)) {
			$stmt->bind_param('si', ...array('y', $localbox_id)); 
			$stmt->execute();
			$affected_rows = $stmt->affected_rows;
			if (($this->debug) && (strlen($stmt->error)>0)) {
				echo $stmt->error;
			}
			$stmt->close();
		}		

	}

		
}
?>