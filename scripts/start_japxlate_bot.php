<?php

/**
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @copyright  Copyright (c) 2011 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */


mb_language('Japanese');
mb_internal_encoding('UTF-8');

//----Initialise----------------

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/..';

include $_SERVER['DOCUMENT_ROOT'] . '/conf/include_files.php';

//----/Initialise---------------

//----Start----------------

//internal delay of 60 seconds, external delay of 10 minutes
//NO! slow it down to:
//internal delay of 6 minutes, external delay of 60 minutes
$bot = new TwitterRobot(60 * 6, 60 * 60, array('#kanji', '#japanese', '#nihongo'), array('#porn', '#sex', '#adult'));/*#kanji OR #japanese OR #nihongo*/  /*-#porn -#sex -#adult*/
$bot->start();
