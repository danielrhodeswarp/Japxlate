<?php

/**
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @copyright  Copyright (c) 2011 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

class TwitterRobot
{
	
	private $internal_delay;	//in seconds
	private $external_delay;	//in seconds
	private $finished;
	private $jingles = array();	//blurbs to tweet when quiet to prevent "dead air time"
	private $searchTags = array();	//tags to search for to reply to people with "reminders"
	private $dontSearchTags = array();	//tags to hard exclude when searching
	
	private $usersTweeted = array();	//prob better to use a DB
	
	//delays are passed in seconds
	function __construct($internal_delay, $external_delay, array $search_tags, array $dont_search_tags)
	{
		$this->internal_delay = $internal_delay;
		$this->external_delay = $external_delay;
		$this->finished = false;
		
		$this->searchTags = $search_tags;
		$this->dontSearchTags = $dont_search_tags;
		
		$this->initiateJingles();
	}
	
	//
	//change these for your feed name and etc
	private function initiateJingles()
	{
		$this->jingles[] = 'Japxlate is powered by the wonderful EDICT #dictionary project as found at http://www.csse.monash.edu.au/~jwb/cgi-bin/wwwjdic.cgi';
		$this->jingles[] = 'Japxlate\'s example sentences come from the excellent Tatoeba Project as found at http://tatoeba.org';
		$this->jingles[] = 'Did you know that written #Japanese is actually a mixture of three different scripts? #Kanji, #hiragana and #katakana are used';
		$this->jingles[] = 'Want to know how to say your favourite word in #Japanese? Tweet @japxlate yourWord for the answer!';
		$this->jingles[] = 'Did you know that #katakana and #hiragana are purely phonetic? The character *is* the sound, the sound *is* the character!';
		$this->jingles[] = 'Tweet @japxlate word for definitions. Word can be in English, Japanese script (kanji or kana) or romaji (Japanese words written in abc)';
		$this->jingles[] = '#Kanji are ideographic characters taken from ancient Chinese';
		$this->jingles[] = '#Hiragana is a rounded, cursive script. It\'s used to add grammar to a sentence and to give pronunciation hints';
		$this->jingles[] = '英語定義が欲しい場合は「@japxlate 日本語単語」をツイートしてください。リプライで教えます。Japxlate は日本語母語話者用にも便利！';
		$this->jingles[] = '#Katakana is a hard, angluar script. It\'s used to give emphasis to words and to write foreign words';
		$this->jingles[] = 'Studying #kanji? Tweet @japxlate 漢字 for a definition!';
		$this->jingles[] = 'Want an online map of Japan with English labels and searching? Head over to @Mapanese at http://mapanese.info';
		$this->jingles[] = 'Heard a new #Japanese word? Give it to us in #romaji and we can still define it! Tweet @japxlate kotoba for a definition';
		$this->jingles[] = 'Don\'t forget that Japxlate is interactive! Tweet @japxlate JapOrEnWord for a #definition';
		$this->jingles[] = 'Are you an #expat in Japan and wanna share the cool places? Check http://gaijinavi.com !';
	}
	
	
	//
	public function start()
	{
		$nop_time = 0;
		
		while(!$this->finished)
		{
			if(Twitter::rawTest() == '"ok"')	//NOP if twitter down
			{
				$something_tweeted = $this->doMainLoop();	//cache - and reply to - our mentions
				
				//also handle seeds
				if(!$something_tweeted)
				{
					$something_tweeted = $this->processSeeds();
				}
			}
			
			sleep($this->internal_delay);
			
			if(!$something_tweeted and Twitter::rawTest() == '"ok"')
			{
				$nop_time += $this->internal_delay;
				
				if($nop_time > $this->external_delay)
				{
									
					if(mt_rand(1, 100) > 90)
					{
						Twitter::statusUpdate($this->jingles[mt_rand(0, count($this->jingles) - 1)]);
						//ECHO "Jingled!\n";
						
						if(mt_rand(1, 100) > 65)
						{
							//ALSO SEARCH FOR APPROPRIATE TAGS
							//AND REPLY TO THOSE MATCHING TWEETERS
							//"REMINDING" THEM THAT WE ARE HERE
							//(POSS CACHING SO WE DON'T ANNOY THE SAME PERSON TWICE!)
							$this->remindPeople();
						}
					}
					
					else
					{
						//$direct_messages = Twitter::get('direct_messages');
						
						//if($this->haveSeed($direct_messages))
						//{
						//	$this->tweetSeed($direct_messages);
						//}
						
						//else
						//{
							$this->tweetRandomWord($this->getRandomTwitterTag());
						//}
					}
					
					$nop_time = 0;
				}
			}
		}
	}
	
	
	//
	private function doMainLoop()
	{
		//routine
		//[1] cache unprocessed tweets
		//[2] process one cached tweet
		//[3] handle seeds
		
		//format of returned mentions:
		/*
		array(1) {
		  [0]=>
		  object(stdClass)#5 (16) {
		    ["in_reply_to_user_id"]=>
		    int(192887405)
		    ["geo"]=>
		    NULL
		    ["in_reply_to_screen_name"]=>
		    string(8) "japxlate"
		    ["retweeted"]=>
		    bool(false)
		    ["truncated"]=>
		    bool(false)
		    ["created_at"]=>
		    string(30) "Mon Sep 20 14:19:10 +0000 2010"
		    ["source"]=>
		    string(3) "web"
		    ["retweet_count"]=>
		    NULL
		    ["contributors"]=>
		    NULL
		    ["place"]=>
		    NULL
		    ["user"]=>
		    object(stdClass)#6 (32) {
		      ["followers_count"]=>
		      int(32)
		      ["description"]=>
		      string(0) ""
		      ["listed_count"]=>
		      int(1)
		      ["profile_sidebar_fill_color"]=>
		      string(6) "E6F6F9"
		      ["url"]=>
		      string(23) "http://www.knobdrop.com"
		      ["show_all_inline_media"]=>
		      bool(false)
		      ["notifications"]=>
		      bool(false)
		      ["time_zone"]=>
		      string(9) "Edinburgh"
		      ["friends_count"]=>
		      int(0)
		      ["lang"]=>
		      string(2) "en"
		      ["statuses_count"]=>
		      int(2925)
		      ["created_at"]=>
		      string(30) "Sun Sep 12 15:48:49 +0000 2010"
		      ["profile_sidebar_border_color"]=>
		      string(6) "DBE9ED"
		      ["location"]=>
		      string(9) "Edinburgh"
		      ["favourites_count"]=>
		      int(0)
		      ["contributors_enabled"]=>
		      bool(false)
		      ["profile_use_background_image"]=>
		      bool(true)
		      ["following"]=>
		      bool(false)
		      ["geo_enabled"]=>
		      bool(false)
		      ["profile_background_color"]=>
		      string(6) "DBE9ED"
		      ["profile_background_image_url"]=>
		      string(60) "http://s.twimg.com/a/1284949838/images/themes/theme17/bg.gif"
		      ["protected"]=>
		      bool(false)
		      ["profile_image_url"]=>
		      string(67) "http://s.twimg.com/a/1284949838/images/default_profile_3_normal.png"
		      ["verified"]=>
		      bool(false)
		      ["profile_text_color"]=>
		      string(6) "333333"
		      ["name"]=>
		      string(9) "Knob Drop"
		      ["follow_request_sent"]=>
		      bool(false)
		      ["profile_background_tile"]=>
		      bool(false)
		      ["screen_name"]=>
		      string(8) "knobdrop"
		      ["id"]=>
		      int(189915771)
		      ["utc_offset"]=>
		      int(0)
		      ["profile_link_color"]=>
		      string(6) "CC3366"
		    }
		    ["favorited"]=>
		    bool(false)
		    ["id"]=>
		    float(25028856448)
		    ["coordinates"]=>
		    NULL
		    ["in_reply_to_status_id"]=>
		    NULL
		    ["text"]=>
		    string(19) "@japxlate  twospace"
		  }
		}
		*/
		$parms_array = array();
		$since_id = $this->getSinceId();
		if(!is_null($since_id))
		{	
			$parms_array['since_id'] = $since_id;
		}
		
		$current_mentions = Twitter::get('statuses/mentions', $parms_array);
		
		
		
		//[1] cache unprocessed tweets
		foreach($current_mentions as $mention)
		{
			if(!$this->tweetAlreadyProcessed($mention->id_str))
			{
				if(!$this->tweetAlreadyCached($mention->id_str))
				{
					$this->cacheTweet($mention);
				}
			}
		}
		
		//[2] process one cached tweet (the oldest one)
		return $this->processTweet();
		
	}
	
	//
	private function tweetAlreadyProcessed($tweet_id)
	{
		$row = MySQL::queryRow('SELECT * FROM handled_tweets WHERE tweet_id = \'' . $tweet_id . '\'');
		
		return is_array($row);
	}
	
	//
	private function tweetAlreadyCached($tweet_id)
	{
		$row = MySQL::queryRow('SELECT * FROM cached_tweets WHERE tweet_id = \'' . $tweet_id . '\'');
		
		return is_array($row);
	}
	
	//
	private function cacheTweet($tweet)
	{
		$tweet_id = MySQL::esc($tweet->id_str);
		$tweet_text = MySQL::esc($tweet->text);
		$tweet_user = MySQL::esc($tweet->user->screen_name);
		
		MySQL::exec("INSERT INTO cached_tweets(tweet_id, tweet_text, tweet_user) VALUES('{$tweet_id}', '{$tweet_text}', '{$tweet_user}')");
	}
	
	//returns true if anything was tweeted, false otherwise
	private function processTweet()
	{
		$tweeted = false;
		
		//get oldest tweeted tweet in cache
		$tweet = MySQL::queryRow('SELECT * FROM cached_tweets ORDER BY tweet_id ASC');
		
		if(!is_array($tweet))
		{
			return false;
		}
		
		$string_to_tweet = '';
		
		//tweet acceptance rules:
		//[] must start with '@japxlate' (ie. your TWITTER_FEED_NAME)
		//[] must contain only one '@' (for the '@japxlate')
		//[] must contain only one token (with no internal whitespace) after '@japxlate'
		//                  (how about zenkaku space!?!?!)
		//
		//examples:
		//[] '@japxlate morning'  [OK]
		//[] '@japxlate good-for-nothing'  [OK]
		//[] '@japxlate 正しい'  [OK]
		//[] '@japxlate 主張'  [OK]
		//[] '@japxlate why hello there'  [NG]
		//[] '@japxlate 正しい  主張'  [NG]
		//[] '@japxlate ikimasu?'    [OK after we trim]
		//[] '@japxlate ikimasu.'    [OK after we trim]
		//[] '@japxlate ikimasu!'    [OK after we trim]
		
		$regex = '/^[@]' . TWITTER_FEED_NAME . '[\s]{1,}([^\s]{1,})$/i';
		if(preg_match($regex, $tweet['tweet_text'], $matches))
		{
			//echo $tweet['tweet_text'] . "  --  OK\n";
			
			$word_to_translate = trim($matches[1], '?!.');	//but could be en or ja at this stage!
			
			if(is_mb($word_to_translate))	//kanji or kana - therefore Japanese
			{
				//kanji or kana?
				/*
				if(is_kana($word_to_translate))
				{
					ECHO "word is kana\n\n";
				}
				
				else
				{
					ECHO "word is kanji\n\n";
				}
				*/
				//ECHO "Kanji or all-kana\n";
				
				$xlation = get_xlation_for_ja($word_to_translate);
				
				if(!empty($xlation))
				{
					$string_to_tweet = '@' . $tweet['tweet_user'] . ' \'' . $word_to_translate . '\' is: ' . $xlation;
					//ECHO mb_strlen($string_to_tweet) . "\n";
					//ECHO $string_to_tweet . "\n\n";
					
				}
				else
				{
					//ECHO "that kanji or kana word ({$word_to_translate}) isnt in my database\n";
					$string_to_tweet = '@' . $tweet['tweet_user'] . ' Oops. I couldn\'t find \'' . $word_to_translate . '\' in my dictionaries!';
				}
				
			}
			
			else	//English script BUT could be Japanese word in romaji!
			{
				//ECHO "English *or* romaji\n";
				
				$xlation = get_xlation_for_en($word_to_translate);
				
				if(!empty($xlation))
				{
					$string_to_tweet = '@' . $tweet['tweet_user'] . ' \'' . $word_to_translate . '\' is: ' . $xlation;
					//ECHO mb_strlen($string_to_tweet) . "\n";
					//ECHO $string_to_tweet . "\n\n";
				}
				else
				{
					//ECHO "that english or romaji word ({$word_to_translate}) isnt in my database\n";
					$string_to_tweet = '@' . $tweet['tweet_user'] . ' Oops. I couldn\'t find \'' . $word_to_translate . '\' in my dictionaries!';
				}
			}
		}
		
		else	//silently ignore invalid requests (OR assume it's a sentence for translation
		{		//and blast it off to Excite or something??)
			//echo $mention->text . "  --  NG\n\n";
			
			
		}
		
		
		//tweet if we have a valid request with a matching xlation
		//(or a valid request with a "sorry" message)
		if(!empty($string_to_tweet))
		{
			Twitter::statusUpdate($string_to_tweet);
			$tweeted = true;
		}
		
		
		//mark this tweet request as "processed" (if valid or not; if translated or not)
		MySQL::exec('INSERT INTO handled_tweets(tweet_id) VALUES(\'' . $tweet['tweet_id'] . '\')');
		MySQL::exec('DELETE FROM cached_tweets WHERE tweet_id = \'' . $tweet['tweet_id'] . '\'');
		
		return $tweeted;
	}
	
	//
	/*
	GET statuses/mentions
		Parameters
			Optional
		
		    * since_id Returns results with an ID greater than (that is, more recent than) the specified ID. There are limits to the number of Tweets which can be accessed through the API. If the limit of Tweets has occured since the since_id, the since_id will be forced to the oldest ID available.
		          o http://api.twitter.com/1/statuses/mentions.json?since_id=12345

	*/
	private function getSinceId()
	{
		return MySQL::queryOne('SELECT MAX(tweet_id) FROM cached_tweets');
	}
	
	//
	private function haveSeed(array $direct_messages = null)
	{
		$have_seed = false;
		
		if(is_null($direct_messages))
		{
			$direct_messages = Twitter::get('direct_messages');
		}
		
		foreach($direct_messages as $direct_message)
		{
			if($direct_message->sender_screen_name == TWITTER_FEED_NAME
				and $direct_message->recipient_screen_name == TWITTER_FEED_NAME
				and mb_strpos($direct_message->text, 'seed ') === 0)
			{
				//here we basically qualify for a seed message
				
				$have_seed = true;
				break;	//have at least one seed direct message so can stop looping to check
			}
		}
		
		return $have_seed;
	}
	
	//
	private function tweetSeed(array $direct_messages = null)
	{
		$tweeted = false;
		
		if(is_null($direct_messages))
		{
			$direct_messages = Twitter::get('direct_messages');
		}
		
		foreach($direct_messages as $direct_message)
		{
			if($direct_message->sender_screen_name == TWITTER_FEED_NAME
				and $direct_message->recipient_screen_name == TWITTER_FEED_NAME
				and mb_strpos($direct_message->text, 'seed ') === 0)
			{
				//here we basically qualify for a seed message
				
				//assume WORD of 'seed WORD' (in $direct_message->text) is our edict *kanji* entry!
				
				preg_match('/^seed[ ]([^\s]{1,})$/i', $direct_message->text, $matches);
				
				$seed_word = $matches[1];
				
				$word = MySQL::queryRow('SELECT * FROM edict WHERE kanji = \'' . MySQL::esc($seed_word) . '\'');
				
				if(is_array($word))
				{
					//tweet it
					//(very similar to logic in tweetRandomWord(), separate this logic into own function?)
					
					$romaji = kana_to_romaji($word['kana']);
					
					$definition = trim($word['definition'], ' ;');
					
					$tag = $this->getRandomTwitterTag();
					
					Twitter::statusUpdate("A random {$tag} word: {$word['kanji']} / {$word['kana']} ({$romaji}) / {$definition}");
					
					$tweeted = true;
				}
				
				//delete it
				Twitter::post('direct_messages/destroy', array('id' => $direct_message->id_str));
				
				break;	//tweeted (or at least *processed*) one seed direct message so can stop looping to check
			}
		}
		
		return $tweeted;
	}
	
	//
	private function processSeeds()
	{
		$tweeted = false;
		
		$direct_messages = Twitter::get('direct_messages');
		
		if($this->haveSeed($direct_messages))
		{
			$tweeted = $this->tweetSeed($direct_messages);
		}
		
		return $tweeted;
	}
	
	//or somehow reuse get_xlation_for_xy() inside this function?
	//$tag being a twitter hash tag
	private function tweetRandomWord($tag)
	{
		$max_word_id = MySQL::queryOne('SELECT MAX(id) FROM edict');
		
		$random_word_id = mt_rand(1, $max_word_id);
		
		$word = MySQL::queryRow('SELECT * FROM edict WHERE id = ' . $random_word_id);
		
		$romaji = kana_to_romaji($word['kana']);
			
		$definition = trim($word['definition'], ' ;');
		
		Twitter::statusUpdate("A random {$tag} word: {$word['kanji']} / {$word['kana']} ({$romaji}) / {$definition}");
		
		$example_sentence = get_sentence_for_ja_word($word['kanji'], $word['kana']);
		
		if(empty($example_sentence))
		{
			//ECHO "{$word['kanji']} / {$word['kana']} | nowt\n";
		}
		
		else
		{
			//ECHO "{$word['kanji']} / {$word['kana']} | {$example_sentence}\n";
			Twitter::statusUpdate($example_sentence);
		}
	}
	
	//
	private function getRandomTwitterTag()
	{
		$tags = array();
		$tags[] = '#nihongo';
		$tags[] = '#japanese';
		
		return $tags[mt_rand(0, count($tags) - 1)];
	}
	
	//
	//SEARCH FOR APPROPRIATE TAGS
	//AND REPLY TO THOSE MATCHING TWEETERS
	//"REMINDING" THEM THAT WE ARE HERE
	//(POSS CACHING SO WE DON'T ANNOY THE SAME PERSON TWICE!)
	private function remindPeople()
	{
		$matches = json_decode(Twitter::rawSearch(implode(' OR ', $this->searchTags) . ' -' . implode(' -', $this->dontSearchTags)));
		
		foreach($matches->results as $result)
		{
			//skip already tweeted people (in this run of the robot)
			if(in_array($result->from_user, $this->usersTweeted))
			{
				continue;
			}
			
			//skip self!
			if($result->from_user == TWITTER_FEED_NAME)
			{
				continue;
			}
			
			//
			//skip users who already follow us!!
			$following_screen_names = array();
			
			$followers = Twitter::get('statuses/followers');	//returns only 100 most recent followers

			foreach($followers as $user)
			{
				$following_screen_names[] = $user->screen_name;
			}
			
			if(in_array($result->from_user, $following_screen_names))
			{
				continue;
			}
			
			//tweet this user with a "reminder"
			//change for your feed name etc
			$tweet_text = "@{$result->from_user} Hi there, we are doing Japanese to English (and vice versa) word definitions over at @japxlate !";
			Twitter::statusUpdate($tweet_text);
			$this->usersTweeted[] = $result->from_user;	//flag user as tweeted
			break;	//do only one reminder tweet per turn
		}
	}
}
