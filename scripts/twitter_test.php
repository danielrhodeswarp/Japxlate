<?php

/**
 * Quick test to see that we can authorise to Twitter and use the API etc
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

mb_language('Japanese');
mb_internal_encoding('UTF-8');

//----Initialise----------------

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/..';

include $_SERVER['DOCUMENT_ROOT'] . '/conf/include_files.php';

//----/Initialise---------------

//----Start----------------

//VAR_DUMP(Twitter::get('statuses/mentions_timeline'));

//VAR_DUMP(Twitter::get('search/tweets', array('q' => 'fart')));
VAR_DUMP(Twitter::search('fart', 1));

//var_dump(Twitter::get('account/verify_credentials'));

exit;


$rez = Twitter::get('account/verify_credentials');
VAR_DUMP($rez);

$rez = Twitter::get('search/tweets', array('q' => 'fart'));
VAR_DUMP($rez);



//EXIT;
//echo 'Return from GET direct_messages is: ' . print_r(Twitter::get('direct_messages'), true) . "\n";

/*
$direct_messages = Twitter::get('direct_messages');

echo count($direct_messages) . ' direct messages' . "\n";

foreach($direct_messages as $direct_message)
{
	echo 'count orig is ' . count($direct_messages) . "\n";
	
	if($direct_message->sender_screen_name == TWITTER_FEED_NAME
		and $direct_message->recipient_screen_name == TWITTER_FEED_NAME
		and mb_strpos($direct_message->text, 'seed ') === 0)
	{
		//here we basically qualify for a seed message
		
		echo "'" . $direct_message->text . "'" . ' is a seed direct message (from ' . TWITTER_FEED_NAME . ' to ' . TWITTER_FEED_NAME . ')' . "\n";
		echo 'doing something with this direct message' . "\n";
		Twitter::post('direct_messages/destroy', array('id' => $direct_message->id_str));
		echo 'deleted this direct message' . "\n";
	}
	
	else
	{
		echo "'" . $direct_message->text . "'" . ' is NOT a seed direct message' . "\n";
	}
}
exit;*/

/*
$rez = Twitter::statusUpdate("test ichi\r\ntest ni " . time());
VAR_DUMP($rez);

if(property_exists($rez, 'error'))
{
	echo "error in result\n";
}

else
{
	echo "no error in result\n";
}
 */
