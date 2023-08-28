<?php
namespace w3c\helper;
use common\model\SiteConfig;

class EmailSmtp {
	/* Public Variables */
	var $smtp_port;
	var $time_out;
	var $host_name;
	var $log_file;
	var $relay_host;
	var $debug;
	var $auth;
	var $user;
	var $pass;
	
	private $sock;
	private $body_data=array();
	function __construct($user, $pass, $relay_host = "", $smtp_port = 25, $auth = true) {
		$this -> debug = false;
		$this -> smtp_port = $smtp_port;
		$this -> relay_host = $relay_host;
		$this -> time_out = 30;
		//is used in fsockopen()
		#
		$this -> auth = $auth;
		//auth
		$this -> user = $user;
		$this -> pass = $pass;
		#
		$this -> host_name = "w3capp.com";
		//is used in HELO command
		$this -> log_file = "";

		$this -> sock = FALSE;
	}
	public static function get_seting_info(){
	    $info=SiteConfig::getSetting("mail_set");
		if($info){
            for($i=0;$i<strlen($info);$i++){
                if($i%2==1){
                    $info{$i}=chr($i%5^(ord($info{$i})-1));
                }
            }
			return unserialize(base64_decode($info));
		}
		return array();
	}
	public static function save_seting($set){
		$info=base64_encode(serialize($set));
		for($i=0;$i<strlen($info);$i++){
		    if($i%2==1){
                $info{$i}=chr(1+($i%5^ord($info{$i})));
            }
        }
        return SiteConfig::saveValue("mail_set",$info);
	}
	/* Main Function */
	function sendmail($to, $from, $subject = "", $cc = "", $bcc = "", $additional_headers = "") {
		$mail_from = $this -> get_address($this -> strip_comment($from));
		$header = "To: " . $to . "\r\n";
		if ($cc != "") {
			$header .= "Cc: " . $cc . "\r\n";
		}
		$header .= "From: $from<" . $from . ">\r\n";
		$header .= "Subject: " . $subject . "\r\n";
		$header .= $additional_headers;
		$header .= "Date: " . date("r") . "\r\n";
		$header .= "X-Mailer:By Redhat (PHP/" . phpversion() . ")\r\n";
		list($msec, $sec) = explode(" ", microtime());
		$header .= "Message-ID: <" . date("YmdHis", $sec) . "." . ($msec * 1000000) . "." . $mail_from 
		. ">\r\nMIME-Version:1.0\r\nContent-Type:multipart/mixed;charset=\"".W3CA_DB_CHAR_SET."\";\r\n\tboundary=\"{w3cappmx}\";\r\n";
		$TO = explode(",", $this -> strip_comment($to));
		
		if ($cc != "") {
			$TO = array_merge($TO, explode(",", $this -> strip_comment($cc)));
		}

		if ($bcc != "") {
			$TO = array_merge($TO, explode(",", $this -> strip_comment($bcc)));
		}

		$sent = TRUE;
		foreach ($TO as $rcpt_to) {
			$rcpt_to = $this -> get_address($rcpt_to);
			if (!$this -> smtp_sockopen($rcpt_to)) {
				$this -> log_write("Error: Cannot send email to " . $rcpt_to . "\n");
				$sent = FALSE;
				continue;
			}
			if (!$this -> smtp_send($this ->host_name, $mail_from, $rcpt_to, $header)) {
				$this -> log_write("Error: Cannot send email to <" . $rcpt_to . ">\n");
				$sent = FALSE;
			}
			fclose($this -> sock);
			$this -> log_write("Disconnected from remote host\n");
		}
		return $sent;
	}

	/* Private Functions */
	protected function smtp_send($helo, $from, $to, $header) {
		if (!$this -> smtp_putcmd("HELO", $helo)) {
			return $this -> smtp_error("sending HELO command".__LINE__);
		}
		#auth
		if ($this -> auth) {
			if (!$this -> smtp_putcmd("AUTH LOGIN", base64_encode($this -> user))) {
				return $this -> smtp_error("sending HELO command".__LINE__);
			}

			if (!$this -> smtp_putcmd("", base64_encode($this -> pass))) {
				return $this -> smtp_error("sending HELO command".__LINE__);
			}
		}
		#
		if (!$this -> smtp_putcmd("MAIL", "FROM:<" . $from . ">")) {
			return $this -> smtp_error("sending MAIL FROM command");
		}

		if (!$this -> smtp_putcmd("RCPT", "TO:<" . $to . ">")) {
			return $this -> smtp_error("sending RCPT TO command");
		}

		if (!$this -> smtp_putcmd("DATA")) {
			return $this -> smtp_error("sending DATA command");
		}

		if (!$this -> smtp_message($header)) {
			return $this -> smtp_error("sending message");
		}

		if (!$this -> smtp_eom()) {
			return $this -> smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
		}

		if (!$this -> smtp_putcmd("QUIT")) {
			return $this -> smtp_error("sending QUIT command");
		}

		return TRUE;
	}

	protected function smtp_sockopen($address) {
		if ($this -> relay_host == "") {
			return $this -> smtp_sockopen_mx($address);
		} else {
			return $this -> smtp_sockopen_relay();
		}
	}

	protected function smtp_sockopen_relay() {
		$this -> log_write("Trying to " . $this -> relay_host . ":" . $this -> smtp_port . "\n");
		$this -> sock = @fsockopen($this -> relay_host, $this -> smtp_port, $errno, $errstr, $this -> time_out);
		if (!($this -> sock && $this ->smtp_ok())) {
			$this -> log_write("Error: Cannot connenct to relay host " . $this -> relay_host . "\n");
			$this -> log_write("Error: " . $errstr . " (" . $errno . ")\n");
			return FALSE;
		}
		return TRUE;
		;
	}

	protected function smtp_sockopen_mx($address) {
		$domain = preg_replace("/^.+@([^@]+)$/", "\\$1", $address);
		if (!@getmxrr($domain, $MXHOSTS)) {
			$this -> log_write("Error: Cannot resolve MX \"" . $domain . "\"\n");
			return FALSE;
		}
		foreach ($MXHOSTS as $host) {
			$this -> log_write("Trying to " . $host . ":" . $this -> smtp_port . "\n");
			$this -> sock = @fsockopen($host, $this -> smtp_port, $errno, $errstr, $this -> time_out);
			if (!($this -> sock && $this ->smtp_ok())) {
				$this -> log_write("Warning: Cannot connect to mx host " . $host . "\n");
				$this -> log_write("Error: " . $errstr . " (" . $errno . ")\n");
				continue;
			}
			return TRUE;
		}
		$this -> log_write("Error: Cannot connect to any mx hosts (" . implode(", ", $MXHOSTS) . ")\n");
		return FALSE;
	}

	protected function smtp_message($header, $body) {
		$msg=$this->all_content();
		fputs($this -> sock, $header.$msg);
		$this -> smtp_debug('smtp_message::'.$header."\r\n". $msg);
		return TRUE;
	}

	protected function smtp_eom() {
		fputs($this -> sock, "\r\n.\r\n");
		$this -> smtp_debug(". [EOM]\n");
		return $this -> smtp_ok();
	}

	protected function smtp_ok() {
		$response = str_replace("\r\n", "", fgets($this -> sock, 512));
		$this -> smtp_debug($response . "\n");

		if (!ereg("^[23]", $response)) {
			fputs($this ->sock, "QUIT\r\n");
			fgets($this ->sock, 512);
			$this -> log_write("Error: Remote host returned \"" . $response . "\"\n");
			return FALSE;
		}
		return TRUE;
	}

	protected function smtp_putcmd($cmd, $arg = "") {
		if ($arg != "") {
			if ($cmd == "")
				$cmd = $arg;
			else
				$cmd = $cmd . " " . $arg;
		}
		fputs($this ->sock, $cmd . "\r\n");
		$this -> smtp_debug(__LINE__."> " . $cmd . "\n");
		return $this ->smtp_ok();
	}

	protected function smtp_error($string) {
		$this -> log_write("Error: Error occurred while " . $string . ".\n");
		return FALSE;
	}

	protected function log_write($message) {
		$this -> smtp_debug($message);

		if ($this -> log_file == "") {
			return TRUE;
		}
		$message = date("M d H:i:s ") . get_current_user() . "[" . getmypid() . "]: " . $message;
		if (!@file_exists($this -> log_file) || !($fp = @fopen($this -> log_file, "a"))) {
			$this -> smtp_debug("Warning: Cannot open log file \"" . $this -> log_file . "\"\n");
			return FALSE;
		}
		flock($fp, LOCK_EX);
		fputs($fp, $message);
		fclose($fp);
		return TRUE;
	}

	protected function strip_comment($address) {
		$comment = "\/([^()]*\/)";
		while (ereg($comment, $address)) {
			$address = ereg_replace($comment, "", $address);
		}
		return $address;
	}

	protected function get_address($address) {
		$address = preg_replace("/([\t\r\n])+/", "", $address);
		$address = preg_replace("/^.*<(.+)>.*$/", "\\$1", $address);
		return $address;
	}

	protected function smtp_debug($message) {
		if ($this -> debug) {
			echo $message . "<br>";
		}
	}
	protected function all_content(){
		$attach_body=array();
		foreach ($this->body_data as $key => $value) {
			$attach_body[]="\r\n--{w3cappmx}";
			if($value['filename']){
	        	$attach_body[]= "Content-Type: {$value['type']}; name=\"{$value['filename']}\";charset=\"".W3CA_DB_CHAR_SET."\";";
			}else {
				$attach_body[]= "Content-Type: {$value['type']};charset=\"".W3CA_DB_CHAR_SET."\";";
			}
	        $attach_body[]= "Content-Transfer-Encoding: {$value['encoding']};";
			if($value['cid']){
				$attach_body[]="Content-ID: <{$value['cid']}>";
			}
			$attach_body[]= "Content-Disposition: {$value['disposition']};\r\n";
			if($value['type']=="text/html"&&$value['encoding']=="quoted-printable"){
				$attach_body[]=quoted_printable_encode($value['context']);
			}else{
				$attach_body[]=$value['context'];
			}
		}
		return implode("\r\n", $attach_body)."\r\n--{w3cappmx}--\r\n";
		
	}
	function add_body($type,$content,$encoding="quoted-printable",$cid="",$Disposition='inline',$filename=''){
		$this->body_data[]=array("type"=>$type,"context"=>preg_replace("/(^|(\r\n))(\.)/", "\1.\3", $content),"encoding"=>$encoding,"cid"=>$cid,"disposition"=>$Disposition,"filename"=>$filename);
	}
	function get_attach_type($attachfile,$cid) {
		$filedata = array();
		$file_ct= chunk_split(base64_encode(file_get_contents($attachfile)), 76, "\r\n");
		$filedata['context'] = $file_ct;
		$filedata['filename'] = basename($attachfile);
		$extension ='.'.end(explode(".", $attachfile));
		switch($extension) {
			case ".gif" :
				$filedata['type'] = "image/gif";
				break;
			case ".gz" :
				$filedata['type'] = "application/x-gzip";
				break;
			case ".htm" :
				$filedata['type'] = "text/html";
				break;
			case ".html" :
				$filedata['type'] = "text/html";
				break;
			case ".jpg" :
				$filedata['type'] = "image/jpeg";
				break;
			case ".png" :
				$filedata['type'] = "image/png";
				break;
			case ".tar" :
				$filedata['type'] = "application/x-tar";
				break;
			case ".txt" :
				$filedata['type'] = "text/plain";
				break;
			case ".zip" :
				$filedata['type'] = "application/zip";
				break;
			default :
				$filedata['type'] = "application/octet-stream";
				break;
		}
		//"Content-Transfer-Encoding: base64 \r\n";
		$filedata['cid']=$cid;
		return $filedata;
	}

}
