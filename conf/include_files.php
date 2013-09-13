<?php

/**
 * Global includes
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

//This stops newer PHPs from moaning (or you can put it in php.ini)
date_default_timezone_set('Europe/London');

//Libraries
include $_SERVER['DOCUMENT_ROOT'] . '/php/lib/utility.php';
include $_SERVER['DOCUMENT_ROOT'] . '/php/lib/linguistics.php';

//3rd party stuff
//include $_SERVER['DOCUMENT_ROOT'] . '/php/3rd/twitteroauth/OAuth.php';    //included by below
include $_SERVER['DOCUMENT_ROOT'] . '/php/3rd/twitteroauth/twitteroauth.php';

//Static utility classes
include $_SERVER['DOCUMENT_ROOT'] . '/php/class/mysql.php';
include $_SERVER['DOCUMENT_ROOT'] . '/php/class/twitter.php';

//Main robot class
include $_SERVER['DOCUMENT_ROOT'] . '/php/class/twitter_robot.php';

//DB and Twitter config
include $_SERVER['DOCUMENT_ROOT'] . "/conf/configuration.php";
