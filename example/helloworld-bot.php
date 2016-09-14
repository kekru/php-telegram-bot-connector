<?php
include_once 'telegramconnector.class.php';

//Enter access-token, which you got from https://telegram.me/botfather, and your bot's name
$tc = new TelegramConnector('123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkl', '@myfancybot');
 
//----------------------------------
//Enable for Debugging (to enable PHP error messages)
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//To debug your bot, override Chatid and Commandtext, so you can call https://yourwebsite.org/helloworld-bot.php via your browser, instead of writing in telegram
//$tc->setChatid(13346357); //your chatid. If you don't know, go to https://telegram.me/whiledoinfobot and ask that bot with "/chatid"
//$tc->setCommandtext('/name Kevin'); //the command, you would normally send in the telegram chat
//----------------------------------

//Don't forget to register your helloworld-bot.php at telegram's api, by calling the following URL (replace example-token with you access-token)
//https://api.telegram.org/bot123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkl/setWebhook?url=https://yourwebsite.org/helloworld-bot.php

$commandParam = $tc->getCommandParam();

//if the user sends "/hi", the bot will answer "Hello World"
if($tc->commandIs('/hi')){		
	  echo $tc->sendMessage('Hello World');
}

if($commandParam != ''){	
  
  //if the user sends "/name Kevin", the bot will answer "Hello Kevin"
	if($tc->commandIs('/name')){		
		echo $tc->sendMessage('Hello '.$commandParam);
	}
}	
?>
