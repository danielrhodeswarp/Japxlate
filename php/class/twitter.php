<?php

/**
 * Simple Twitter API (a wrapper for Abraham Williams' TwitterOAuth class) (defaults to JSON format)
 * Also note that Twitter - rather sensibly - wants everything in UTF-8 (there's also a Japanese version of it and etc)
 * 
 * @note Twitter "API v1.1 will support JSON only"
 * @note everything, including search, needs authorisation with Twitter API v1.1
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */
class Twitter
{
	//----internal Stuff for this Twitter class of ours-------------------------
	
    /**
     * Default format. One of 'xml', 'json', 'rss', 'atom'
     * @note Twitter API v1.1 supports only JSON
     */
    private static $format = 'json';	
	
    /**
     * Are we currently authenticated - yes or no?
     */
    private static $authenticated = false;
	
    /**
     * \TwitterOAuth object representing the oAuth connection to Twitter
     */
    private static $connection = false;
	
	/**
     * Set format of this object (and embedded \TwitterOAuth object)
     * 
     * @author Daniel Rhodes
     * @note Twitter API v1.1 supports only JSON
     * 
     * @param string $new_format format to set
     */
	public static function setFormat($new_format)
	{
		self::$format = $new_format;
		if(self::$connection)	//set format of \TwitterOAuth class too
		{
			self::$connection->format = $new_format;
		}
	}
	
	/**
     * Get format of this object (and therefore of the embedded \TwitterOAuth object)
     * 
     * @author Daniel Rhodes
     * @note Twitter API v1.1 supports only JSON
     * 
     * @return string the currently set format
     */
	public static function getFormat()
	{
		return self::$format;
	}
	
	/**
     * Check if we are currently authenticated or not
     * 
     * @author Daniel Rhodes
     * 
     * @return bool currently authenticated - true or false?
     */
	public static function isAuthenticated()
	{
		return self::$authenticated;
	}
	
	/**
     * Authenticate an oAuth connection if not done so already
     * 
     * @author Daniel Rhodes
     */
	private static function authenticate()
	{
		if(!self::$authenticated)
		{
			self::getTwitterOAuthConnectionWithAccessToken();
			
			if(self::$connection)
			{
				self::$authenticated = true;
			}
		}
	}
	
	/**
     * attempt to get (and store) an oAUth connection using the application's credentials
     * 
     * @author Daniel Rhodes
     */
	private static function getTwitterOAuthConnectionWithAccessToken()
	{
		self::$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_SECRET);
		
		if(!function_exists('json_decode'))	//json_decode() is in a *possibly* unavailable PHP module
		{
			self::$connection->decode_json = false;	//so now we can only tweet - we can't, eg, parse search results
		}
	}
	
	//----/internal Stuff for this Twitter class of ours------------------------
	
	
	//----Proper API stuff
    //----(as per http://apiwiki.twitter.com/Twitter-API-Documentation)---------
	
	/**
     * Can p'raps use this to test if Twitter itself is down?
     * 
     * @author Daniel Rhodes
     * @note "GET help/test" is no longer in Twitter API v1.1
     * 
     * @param type $format
     * @return string
     */
    public static function test($format = '')	//GET; xml, json; auth not needed
	{
		return '"ok"';	//FTTB
		
		//this was the API v1.0 style:
		/*
		if(!empty($format))
		{
			self::setFormat($format);
		}
		
		else 
		{
			$format = self::$format;
		}
		
		//supress warnings
		return @file_get_contents('http://api.twitter.com/1/help/test.' . $format);	//url no longer valid
        */
	}
	
	/**
     * generic REST getter
     * 
     * @author Daniel Rhodes
     * 
     * @param string $resource REST resource to get. eg "followers/list"
     * @param array $parms optional. default null. any extra parameters for the resource as a key => value array
     * @param string $format optional. default is '' which means use the currently set format. else pass in the desired format (which will then be set as the current format going forward)
     * @return \stdClass|string json_decoded() response from Twitter ELSE response as a string if self::getTwitterOAuthConnectionWithAccessToken() sets self::$connection->decode_json to false
     */
	public static function get($resource, array $parms = null, $format = '')
	{
		self::authenticate();	//assume needed? (could put as a parm...)
		
		if(!empty($format))
		{
			self::setFormat($format);
		}
		
		else 
		{
			$format = self::$format;
		}
		
		return self::$connection->get($resource, $parms);
	}
	
	/**
     * generic REST poster
     * 
     * @author Daniel Rhodes
     * 
     * @param string $resource REST resource to get. eg "followers/list"
     * @param array $parms optional. default null. any extra parameters for the resource as a key => value array
     * @param string $format optional. default is '' which means use the currently set format. else pass in the desired format (which will then be set as the current format going forward)
     * @return \stdClass|string json_decoded() response from Twitter ELSE response as a string if self::getTwitterOAuthConnectionWithAccessToken() sets self::$connection->decode_json to false
     */
	public static function post($resource, array $parms = null, $format = '')
	{
		self::authenticate();	//assume needed? (could put as a parm...)
		
		if(!empty($format))
		{
			self::setFormat($format);
		}
		
		else 
		{
			$format = self::$format;
		}
		
		return self::$connection->post($resource, $parms);
	}
	
	/**
     * Post a new "status" (ie. miniblog entry) of 140 characters or less
     * 
     * @author Daniel Rhodes
     * 
     * @param string $status the miniblog entry text
     * @param string $format optional. default is '' which means use the currently set format. else pass in the desired format (which will then be set as the current format going forward)
     * @return \stdClass|string json_decoded() response from Twitter ELSE response as a string if self::getTwitterOAuthConnectionWithAccessToken() sets self::$connection->decode_json to false
     */
    public static function statusUpdate($status, $format = '')
	{
		self::authenticate();
		
		if(!empty($format))
		{
			self::setFormat($format);
		}
		
		else 
		{
			$format = self::$format;
		}
		
		$parms = array('status' => $status);
		
		return self::$connection->post('statuses/update', $parms);
	}
	
	/**
     * Return TWEETS matching the specified search $query
     * 
     * @author Daniel Rhodes
     * 
     * @param string $query search query as per Twitter search syntax
     * @param int $tweets_per_page how many results to return in one page
     * @param string $format optional. default is '' which means use the currently set format. else pass in the desired format (which will then be set as the current format going forward)
     * @return \stdClass|string json_decoded() response from Twitter ELSE response as a string if self::getTwitterOAuthConnectionWithAccessToken() sets self::$connection->decode_json to false
     */
	public static function search($query, $tweets_per_page = 10, $format = '')
	{
		self::authenticate();
		
		if(!empty($format))
		{
			self::setFormat($format);
		}
		
		else 
		{
			$format = self::$format;
		}
		
		//Note that we can more-or-less encircle the UK (and Ireland) using a circle of radius
		//520km originating from the Isle of Man (see http://esa.ilmari.googlepages.com/circle.htm)
		
		return self::$connection->get('search/tweets', array('q' => $query, 'count' => $tweets_per_page, 'lang' => 'en'));
	}
	//----/proper API stuff-----------------------------------------------------
}
