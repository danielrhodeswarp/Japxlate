<?php

/**
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @copyright  Copyright (c) 2011 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

//Simple Twitter API (a wrapper for Abraham Williams' TwitterOAuth class) (defaults to JSON format)
//Also note that Twitter - rather sensibly - wants everything in UTF-8 (there's also a Japanese version of it and etc)
class Twitter
{
	//----internal Stuff for this Twitter class of ours------------------------------------------------------
	private static $format = 'json';	//Default format. One of 'xml', 'json', 'rss', 'atom'
	private static $authenticated = false;
	private static $connection = false;
	
	
	
	//
	public static function setFormat($new_format)
	{
		self::$format = $new_format;
		if(self::$connection)	//set format of TwitterOAuth class too
		{
			self::$connection->format = $new_format;
		}
	}
	
	//
	public static function getFormat()
	{
		return self::$format;
	}
	
	//
	public static function isAuthenticated()
	{
		return self::$authenticated;
	}
	
	//
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
	
	//
	private static function getTwitterOAuthConnectionWithAccessToken()
	{
		self::$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_SECRET);
		
		if(!function_exists('json_decode'))	//in an optional PHP module this one
		{
			self::$connection->decode_json = false;	//so now we can only tweet - we can't parse search results
		}
	}
	
	
	
	//----/internal Stuff for this Twitter class of ours-----------------------------------------------------
	
	
	//----Proper API stuff (as per http://apiwiki.twitter.com/Twitter-API-Documentation)------------
	
	//Can p'raps use this to test if Twitter itself is down?
	//(RAW because we bypass TwitterOAuth)
	public static function rawTest($format = '')	//GET; xml, json; auth not needed
	{
		if(!empty($format))
		{
			self::setFormat($format);
		}
		
		else 
		{
			$format = self::$format;
		}
		
		return file_get_contents('http://twitter.com/help/test.' . $format);
	}
	
	//
	//(RAW because we bypass TwitterOAuth)
	public static function rawSearch($query, $tweets_per_page = 10, $format = '')	//GET; json, atom (the undocumented 'rss' also seem to work...); auth not needed
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
		
		//return file_get_contents('http://search.twitter.com/search.' . $format . '?rpp=' . $tweets_per_page . '&lang=en&q=' . urlencode($query) . '&geocode=54.2460%2C-4.5154%2C520km');
		return file_get_contents('http://search.twitter.com/search.' . $format . '?rpp=' . $tweets_per_page . '&lang=en&q=' . urlencode($query));
		
		
	}
	
	//
	//(RAW because we bypass TwitterOAuth)
	public static function rawGetTrends($format = '')	//GET; json; auth not needed; rate limited
	{
		if(!empty($format))
		{
			self::setFormat($format);
		}
		
		else 
		{
			$format = self::$format;
		}
		
		return file_get_contents('http://api.twitter.com/1/trends.' . $format);
	}
	
	
	//generic REST getter
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
	
	//generic REST poster
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
	
	
	//
	public static function getAccountRateLimitStatus($format = '')	//GET; json, xml; auth needed IF you want the user's limit and not the IP's limit; not rate limited
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
		
		return self::$connection->get('account/rate_limit_status');
	}
	
	
	//Post a new "status" (ie. miniblog entry) of 140 characters or less
	public static function statusUpdate($status, $format = '')	//POST; xml, json, rss, atom; auth needed
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
	
	
	//Returns tweets that match a specified query
	/*
	Parameters
	Required
	
	    * q Search query. Should be URL encoded. Queries will be limited by complexity.
	          o http://search.twitter.com/search.json?q=@noradio
	
	Optional
	
	    * callback Only available for JSON format. If supplied, the response will use the JSONP format with a callback of the given name.
	    * lang Restricts tweets to the given language, given by an ISO 639-1 code.
	    * locale Specify the language of the query you are sending (only ja is currently effective). This is intended for language-specific clients and the default should work in the majority of cases.
	          o http://search.twitter.com/search.json?locale=ja
	    * rpp The number of tweets to return per page, up to a max of 100.
	          o http://search.twitter.com/search.json?rpp=100
	    * page The page number (starting at 1) to return, up to a max of roughly 1500 results (based on rpp * page).
	          o http://search.twitter.com/search.json?page=10
	    * since_id Returns results with an ID greater than (that is, more recent than) the specified ID.
	          o http://search.twitter.com/search.json?since_id=12345
	    * until Optional. Returns tweets generated before the given date. Date should be formatted as YYYY-MM-DD.
	          o http://search.twitter.com/search.json?until=2010-03-28
	    * geocode Returns tweets by users located within a given radius of the given latitude/longitude. The location is preferentially taking from the Geotagging API, but will fall back to their Twitter profile. The parameter value is specified by "latitude,longitude,radius", where radius units must be specified as either "mi" (miles) or "km" (kilometers). Note that you cannot use the near operator via the API to geocode arbitrary locations; however you can use this geocode parameter to search near geocodes directly.
	          o http://search.twitter.com/search.json?geocode=37.781157,-122.398720,1mi
	    * show_user When true, prepends ":" to the beginning of the tweet. This is useful for readers that do not display Atom's author field. The default is false.
	    * result_type Optional. Specifies what type of search results you would prefer to receive. The current default is "mixed." Valid values include:
	          o mixed: Include both popular and real time results in the response.
	          o recent: return only the most recent results in the response
	          o popular: return only the most popular results in the response.
	          o http://search.twitter.com/search.json?result_type=mixed
	          o http://search.twitter.com/search.json?result_type=recent
	          o http://search.twitter.com/search.json?result_type=popular


	*/
	public static function search($query, $tweets_per_page = 10, $format = '')	//GET; json, atom (the undocumented 'rss' also seem to work...); auth not needed
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
		
		//return fileGetContents('http://search.twitter.com/search.' . $format . '?rpp=' . $tweets_per_page . '&lang=en&q=' . urlencode($query) . '&geocode=54.2460%2C-4.5154%2C520km');
		//return fileGetContents('http://search.twitter.com/search.' . $format . '?rpp=' . $tweets_per_page . '&lang=en&q=' . urlencode($query));
		
		return self::$connection->get('search', array('q' => $query, 'rpp' => $tweets_per_page, 'lang' => 'en'));
	}
	//----/proper API stuff-------------------------------------------------------------------------
}
