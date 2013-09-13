<?php

/**
 * Library file containing general utility functions
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

/**
 * Shorten a URL using the easy peasy http://is.gd API
 * 
 * @author Daniel Rhodes
 * 
 * @param string $url_without_the_http_bit URL to shorten
 * @return string the shortened URL
 */
function shorten_url($url_without_the_http_bit)
{
	return file_get_contents('http://is.gd/create.php?format=simple&url=' . urlencode($url_without_the_http_bit));
}

/**
 * Log any string (or variable) to the specified log file.
 * Our core logger.
 * 
 * @author Daniel Rhodes
 * @todo possibly some backtrace info about the calling file:line etc
 * 
 * @param mixed $message message string (you don't explicitly need a trailing newline) or variable to log
 * @param string $file log file to write to
 */
function log_to_file($message, $file)
{
	if(!is_string($message))	//then assume it's an array or an object or something
	{
		$message = var_export($message, true);
	}
	
	$datetime = date('Y-m-d H:i:s');	//now!
	error_log("[$datetime] {$message}\n", 3, $file);
}

/**
 * Write to application error log
 *
 * @author Daniel Rhodes
 * 
 * @param mixed $message error string (you don't explicitly need a trailing newline) or variable to log
 */
function log_error($message)
{
	log_to_file($message, APPLICATION_ERROR_LOG);
}

/**
 * Write to application message log (if debugging enabled)
 *
 * @author daniel
 * 
 * @param mixed $message message string (you don't explicitly need a trailing newline) or variable to log
 */
function log_message($message)
{
    if(!APPLICATION_MESSAGE_LOGGING_ACTIVE)
    {
        return;
    }
    
	log_to_file($message, APPLICATION_MESSAGE_LOG);
}

/**
 * Write to application language log
 * 
 * @author Daniel Rhodes
 * 
 * @param mixed $message language string (you don't explicitly need a trailing newline) or variable to log
 */
function log_language($message)
{
    log_to_file($message, APPLICATION_LANGUAGE_LOG);
}

/**
 * Take a string like "/one/two/three/" and make it like "one; two; three"
 * (for our definitions in the tweets)
 * 
 * @author Daniel Rhodes
 * 
 * @param string $string
 * @return string
 */
function format_slashes($string)
{
    return str_replace('/', '; ', trim($string, '/'));
}
