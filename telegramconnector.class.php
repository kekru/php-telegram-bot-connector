<?php

//date_default_timezone_set('Europe/Berlin');

class TelegramConnector {
	
  private $key;
	private $botname;
	private $chatid;
	private $command;
	private $commandParam = '';
	private $messageData;
	private $lang;

    function __construct($key, $botname, $lang='en'){
		$this->key = $key;
		$this->botname = strtolower($botname);
		
		$data = json_decode(file_get_contents('php://input'));
		$this->messageData = $data;

		$this->chatid = $data->message->chat->id;
		$text = replaceNoBreakSpaces($data->message->text);	
		
		$this->setCommandtext($text);		
		$this->lang = $lang;
		
		if($this->commandIs('/share')){
			$this->sendShareInfos();
		}
		if($this->commandIs('/shareqr')){
			$this->sendShareQRCode();
		}
	}
	
	function getBotLink(){
		return 'https://telegram.me/'.substr($this->botname, 1);
	}
	
	function sendShareInfos(){
		$message = null;
		$link = $this->getBotLink();
		
		if($this->lang == 'de'){
			$message = 'Um diesen Bot mit anderen zu teilen, sende in einem Telegram Chat '.$this->botname.', ansonsten '.$link.' oder generiere einen QR Code, indem du /shareqr schreibst.';
		}elseif($this->lang == 'en'){
			$message = 'When you want to share this bot, send '.$this->botname.' within a Telegram chat, otherwise send '.$link.' or write /shareqr to get a QR code.';
		}
		
		if($message !== null){
			$this->sendMessage(utf8_encode($message));
		}
	}
	
	function sendShareQRCode(){
		$filename = './shareqr.png';
		copy('https://whiledo.de/api/util/qr.php?q='.urlencode($this->getBotLink()), $filename);
		echo $this->sendImage($filename);
		unlink($filename);	
	}
	
	function setChatid($chatid){
		$this->chatid = $chatid;
	}
	
	function getChatid(){
		return $this->chatid;
	}
	
	function setCommandtext($text){				
		$paramPos = strpos($text, ' ');
		if($paramPos !== FALSE){
			$this->commandParam = trim(substr($text, $paramPos));
			$command = substr($text, 0, $paramPos);
		}else{
			$command = $text;
		}

		$this->command = strtolower($command);
	}
	
	function getCommand(){
		return $this->command;
	}

	function getCommandParam(){
		return $this->commandParam;
	}
	
	function getLocation(){
		$location = $this->messageData->message->location;
		if(is_null($location)){
			return false;
		}
		
		return $location;
	}
	
	function sendMessage($text, $chatid=null, $markdown=false){
		if(is_null($chatid)){
			$chatid = $this->chatid;
		}
		$data = array('chat_id' => $chatid, 'text' => $text, 'disable_web_page_preview' => 'true');
		if($markdown === true){
			$data['parse_mode'] = 'Markdown';
		}
		return $this->send('sendMessage', $data);
	}
	
	function sendImage($imagepath, $chatid=null){
		if(is_null($chatid)){
			$chatid = $this->chatid;
		}		
		return $this->send('sendPhoto', array('chat_id' => $chatid, 'photo' => $this->getFileToSend($imagepath)), false);
	}
	
	function sendAudio($audiopath, $chatid=null){
		if(is_null($chatid)){
			$chatid = $this->chatid;
		}
		return $this->send('sendAudio', array('chat_id' => $chatid, 'audio' => $this->getFileToSend($audiopath, 'audio/mp3', 'audio.mp3')), false);
	}
	
	function getFileToSend($filename, $filetype='image/png', $filenametarget='qr.png'){
		//For PHP Version <= 4
		//return '@'.realpath($filename);
		
		//For PHP Version >= 5
		return new CurlFile($filename, $filetype, $filenametarget);
	}
	
	function send($command, $contentarray, $use_http_build_query=true){
		if($use_http_build_query){
			$contentarray = http_build_query($contentarray);
		}		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,'https://api.telegram.org/bot'.$this->key.'/'.$command);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $contentarray); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);
		$output = $server_output;
		curl_close($ch);
		return $output;
	}
	
	function commandIs($command){
		return $command == $this->command || $command.''.$this->botname == $this->command;
	}
	
}

function replaceNoBreakSpaces($str){
	return str_replace("\xC2", "", str_replace("\xA0", " ", $str)); //remove no-break spaces https://de.wikipedia.org/wiki/Gesch%C3%BCtztes_Leerzeichen
}

?>
