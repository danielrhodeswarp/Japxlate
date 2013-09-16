<?php

/**
 * Our main application robot loop handling tweets in and out
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */
class TwitterRobot
{
	/**
     * How frequently, in seconds, to process incoming mentions (tweets like "@japxlate myWord")
     * and seed DMs (a DM that TWITTER_FEED_NAME sends to self like "seed someKanjiWordInOurDictionary")
     */
	private $internal_delay;
	
    /**
     * Seconds of inactivity (ie. no mentions or seed DMs handled) to wait before
     * tweeting out a random word or possibly a jingle
     */
    private $external_delay;
	
    /**
     * Master loop control
     */
    private $finished;
	
    /**
     * fixed blurbs to tweet out very occasionally instead of a random word
     */
    private $jingles = array();
    
    /**
     * some different Twitter hash tags to mean "Japanese"
     */
    private $japaneseTags = array();
	
    /**
     * tags to search for users to reply to with "reminders" about our feed
     */
    private $searchTags = array();
	
    /**
     * tags to hard exclude when searching for users to remind
     * (ie. '#porn' or things that would look bad associated with your feed)
     */
    private $dontSearchTags = array();
	
    /**
     * users already "reminded" about our feed (in this run of the robot)
     * so that we don't annoy them again and get reported LOL
     * 
     * @note prob better to use a DB
     */
	private $usersTweeted = array();
	
	/**
     * Class constructor
     * 
     * @author Daniel Rhodes
     * 
     * @param int $internal_delay in seconds
     * @param int $external_delay in seconds
     * @param string[] $search_tags search tags to grab people to remind
     * @param string[] $dont_search_tags exclude search tags when searching for remind users
     */
	function __construct($internal_delay, $external_delay, array $search_tags, array $dont_search_tags)
	{
		$this->internal_delay = $internal_delay;
		$this->external_delay = $external_delay;
		$this->finished = false;
		
		$this->searchTags = $search_tags;
		$this->dontSearchTags = $dont_search_tags;
		
		$this->jingleInitiateAll();
        $this->japaneseTagInitiateAll();
	}
	
	/**
     * Let's get this party started! Starts the master control (endless) loop.
     * your external scripts will call this after constructing an object
     * 
     * @author Daniel Rhodes
     */
	public function start()
	{
		log_message('TwitterRobot::start()');
		
		$nop_time = 0;  //"no operation" time
		
		while(!$this->finished)
		{
			$something_tweeted = false;
			
			if(Twitter::test() == '"ok"')	//NOP if twitter down
			{
                //cache - and reply to - our mentions
				$something_tweeted = $this->mentionProcessAll();
				
				//also handle seeds
				if(!$something_tweeted)
				{
					$something_tweeted = $this->seedProcessAll();
				}
			}
			
			sleep($this->internal_delay);
			
			if(!$something_tweeted and Twitter::test() == '"ok"')
			{
				$nop_time += $this->internal_delay;
				
				if($nop_time > $this->external_delay)
				{
					//10% chance of tweeting a jingle
					if(mt_rand(1, 100) > 90)
					{
						Twitter::statusUpdate($this->jingleGet());
						//ECHO "Jingled!\n";
						
                        //35% chance of tweeting a reminder
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
						$this->definitionTweet($this->japaneseTagGet());
					}
					
                    //reset "no operation" time
					$nop_time = 0;
				}
			}
		}
	}
	
    //----mention handling methods----------------------------------------------
    
	/**
     * Cache any new mentions (for later processing) and then process the oldest
     * cached mention
     * 
     * @author Daniel Rhodes
     * 
     * @return bool true if a mention response was tweeted out (else false)
     */
	private function mentionProcessAll()
	{
		//routine
		//[1] cache unprocessed mentions
		//[2] process one cached mention
		
		//format of returned mentions:
		//see https://dev.twitter.com/docs/api/1.1/get/statuses/mentions_timeline
		
		log_message('TwitterRobot::mentionProcessAll()');
		
		$parms_array = array();
		$since_id = $this->mentionGetMaxId();
		if(!is_null($since_id))
		{	
			$parms_array['since_id'] = $since_id;
		}
		
		$current_mentions = Twitter::get('statuses/mentions_timeline', $parms_array);
		
		if (!is_array($current_mentions)) {
			log_message("Twitter::get('statuses/mentions_timeline') did not return an array. It returned:");
			log_message($current_mentions);
		} else {
		
            //[1] cache unprocessed mentions
            foreach($current_mentions as $mention)
            {
                if(!$this->mentionIsProcessed($mention->id_str))
                {
                    if(!$this->mentionIsCached($mention->id_str))
                    {
                        $this->mentionCache($mention);
                    }
                }
            }
		}
		
		//[2] process *one* cached mention (the oldest one)
		return $this->mentionProcessSingle();
	}
	
	/**
     * Has the specified mention already been processed (ie. replied to
     * (successfully or not))?
     * 
     * @author Daniel Rhodes
     * 
     * @param string $tweet_id tweet id as string numeral
     * @return bool true if specified tweet has already been processed (else false)
     */
	private function mentionIsProcessed($tweet_id)
	{
		log_message('TwitterRobot::mentionIsProcessed()');
		
		$row = MySQL::queryRow('SELECT * FROM handled_tweets WHERE tweet_id = \'' . $tweet_id . '\'');
		
		return is_array($row);
	}
	
	/**
     * Has the specified mention already been cached (for later processing) or not?
     * 
     * @author Daniel Rhodes
     * 
     * @param string $tweet_id tweet id as string numeral
     * @return bool true if specified tweet has already been processed (else false)
     */
	private function mentionIsCached($tweet_id)
	{
		log_message('TwitterRobot::mentionIsCached()');
		
		$row = MySQL::queryRow('SELECT * FROM cached_tweets WHERE tweet_id = \'' . $tweet_id . '\'');
		
		return is_array($row);
	}
	
	/**
     * Cache the specified mention (for later processing)
     * 
     * @author Daniel Rhodes
     * @note we don't save *every* bit of data that the tweet object has!
     * 
     * @param \stdClass $tweet the tweet object as returned from the API
     */
	private function mentionCache($tweet)
	{
		log_message('TwitterRobot::mentionCache()');
		
		$tweet_id = MySQL::esc($tweet->id_str);
		$tweet_text = MySQL::esc($tweet->text);
		$tweet_user = MySQL::esc($tweet->user->screen_name);
		
		MySQL::exec("INSERT INTO cached_tweets(tweet_id, tweet_text, tweet_user) VALUES('{$tweet_id}', '{$tweet_text}', '{$tweet_user}')");
	}
	
	/**
     * Process, ie. attempt to reply to, the oldest mention in the cache
     * 
     * @return bool true if a reply was tweeted (else false)
     */
    private function mentionProcessSingle()
	{
		log_message('TwitterRobot::mentionProcessSingle()');
		
		$tweeted = false;
		
		//get oldest tweeted tweet in cache
		$tweet = MySQL::queryRow('SELECT * FROM cached_tweets ORDER BY tweet_id ASC');
		
		if(!is_array($tweet))
		{
			return false;
		}
		
		$string_to_tweet = '';
		
        //
		//tweet acceptance rules:
        //
		//[] must start with '@japxlate' (ie. your TWITTER_FEED_NAME)
		//[] followed by at least one whitespace
		//[] followed by at least one printing character (ie. one or more words - can have spaces but prob won't hit much - see below)
		//
		//examples:
        //
		//[] '@japxlate morning'  [OK] and will prob hit
		//[] '@japxlate good-for-nothing'  [OK] but prob won't hit
		//[] '@japxlate 正しい'  [OK] and will prob hit
		//[] '@japxlate 主張'  [OK] and will prob hit
		//[] '@japxlate why hello there'  [OK] and *might* hit
		//[] '@japxlate 正しい  主張'  [OK] but *def* won't hit
		//[] '@japxlate ikimasu?'    [OK after we trim]
		//[] '@japxlate "ikimasu"'    [OK after we trim]
		//[] '@japxlate ikimasu!'    [OK after we trim]
        //[] '@japxlate ！主張！'    [OK after we trim]
		
		$regex = '/^[@]' . TWITTER_FEED_NAME . '[\s]{1,}(.*?)[\s]{0,}$/i';    //TODO what if strange characters in TWITTER_FEED_NAME? 
		if(preg_match($regex, $tweet['tweet_text'], $matches))
		{
			//echo $tweet['tweet_text'] . "  --  OK\n";
			
			$word_to_translate = mb_punctuation_trim($matches[1]);  //but could be en or ja at this stage!
            
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
			
			log_language("NOTE did not attempt to handle [{$tweet['tweet_text']}] as a def req");
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
	
    /**
     * return current MAX tweet ID of all cached mentions
     * (eg. for use as since_id parm of API's GET statuses/mentions)
     * 
     * @author Daniel Rhodes
     * 
     * @return int current MAX tweet ID of all cached mentions
     */
	private function mentionGetMaxId()
	{
		log_message('TwitterRobot::mentionGetMaxId()');
		
		return MySQL::queryOne('SELECT MAX(tweet_id) FROM cached_tweets');
	}
    
    //----/end mention handling methods-----------------------------------------
    
	//----seed handling methods-------------------------------------------------
	
	/**
     * Do we have any seed DMs (a DM that TWITTER_FEED_NAME sends to self like "seed someWord")
     * in the specified array of DMs?
     * 
     * @author Daniel Rhodes
     * 
     * @param array $direct_messages as returned from Twitter API
     * @return bool true if $direct_messages contains any seed DMs
     */
	private function seedPresent(array $direct_messages = null)
	{
		log_message('TwitterRobot::seedPresent()');
		
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
				//here we *basically* qualify for a seed message
				
				$have_seed = true;
				break;	//have at least one seed direct message so can stop looping to check
			}
		}
		
		return $have_seed;
	}
	
	/**
     * Process the first seed message found in the specified array of DMs
     * 
     * @author Daniel Rhodes
     * @note we delete the seed DM after processing it
     * 
     * @param array $direct_messages as returned from Twitter API
     * @return bool was the seed word's definition tweeted out?
     */
	private function seedProcessSingle(array $direct_messages = null)
	{
		log_message('TwitterRobot::seedProcessSingle()');
		
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
					//(very similar to logic in definitionTweet(), separate this logic into own function?)
					
					$romaji = kana_to_romaji($word['kana']);
					
					$definition = format_slashes($word['definition']);
					
					$tag = $this->japaneseTagGet();
					
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
	
	/**
     * Process one seed DM *if* there is at least one available (else NOP)
     * 
     * @author Daniel Rhodes
     * 
     * @return bool true if a seed DM was present *and* its definition tweeted out
     */
	private function seedProcessAll()
	{
		log_message('TwitterRobot::seedProcessAll()');
		
		$tweeted = false;
		
		$direct_messages = Twitter::get('direct_messages');
		
		if (is_array($direct_messages)) {
		    if($this->seedPresent($direct_messages))    //if at least one seed present
            {
                $tweeted = $this->seedProcessSingle($direct_messages);
            }
		} else {
            log_message("Twitter::get('direct_messages') did not return an array. It returned:");
            log_message($direct_messages);
		}
		
		return $tweeted;
	}
	
    //----/end seed handling methods--------------------------------------------
    
    //----definition handling methods-------------------------------------------
    
	/**
     * Tweet out a random word definition from our dictionary.
     * Possibly followed by an example sentence tweet
     * 
     * @author Daniel Rhodes
     * @note or somehow reuse get_xlation_for_xy() inside this function?
     * 
     * @param string $tag Twitter hash tag to mean "Japanese" eg. '#nihongo' (used to introduce the definition)
     */
	private function definitionTweet($tag)
	{
		log_message('TwitterRobot::definitionTweet()');
		
		$max_word_id = MySQL::queryOne('SELECT MAX(id) FROM edict');
		
		$random_word_id = mt_rand(1, $max_word_id);
		
		$word = MySQL::queryRow('SELECT * FROM edict WHERE id = ' . $random_word_id);
		
		$romaji = kana_to_romaji($word['kana']);
			
		$definition = format_slashes($word['definition']);
		
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
	
    //----/end definition handling methods--------------------------------------
    
    //----methods for "reminding" users about our Twitter feed------------------
    
    /**
     * SEARCH FOR APPROPRIATE TAGS AND REPLY TO THOSE MATCHING TWEETERS
     * "REMINDING" THEM THAT WE ARE HERE (CACHING SO WE DON'T ANNOY THE SAME PERSON TWICE!)
     * 
     * @author Daniel Rhodes
     */
	private function remindPeople()
	{
		log_message('TwitterRobot::remindPeople()');
		
		$matches = Twitter::get('search/tweets', array('q' => implode(' OR ', $this->searchTags) . ' -' . implode(' -', $this->dontSearchTags)));
		
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
			
			$followers = Twitter::get('statuses/followers');	//TODO (?) returns only 100 most recent followers

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
    
    //----/end methods for "reminding" users about our Twitter feed-------------
    
    //----utility and helper methods--------------------------------------------
	
    /**
     * set up some different Twitter hash tags to mean "Japanese" in whatever language
     * 
     * @author Daniel Rhodes
     * @note change these for your feed name and etc
     */
	private function japaneseTagInitiateAll()
	{
        $this->japaneseTags[] = '#nihongo'; //romaji
		$this->japaneseTags[] = '#japanese';    //en
        $this->japaneseTags[] = '#にほんご';    //ja hiragana
        $this->japaneseTags[] = '#日本語';  //ja kanji
        $this->japaneseTags[] = '#일본어';   //ko
        $this->japaneseTags[] = '#日语'; //chinese
        $this->japaneseTags[] = '#Японскийязык';    //russian
        $this->japaneseTags[] = '#giapponese';  //italian
        $this->japaneseTags[] = '#japonesa';    //portuguese
        $this->japaneseTags[] = '#japonés';    //spanish
        $this->japaneseTags[] = '#japones';    //spanish
        //arabic?
    }
    
    /**
     * Return a random Twitter hash tag to mean "Japanese" in whatever language
     * 
     * @author Daniel Rhodes
     * 
     * @return string one "Japanese" Twitter hash tag
     */
	private function japaneseTagGet()
	{
		log_message('TwitterRobot::japaneseTagGet()');
		
		return $this->japaneseTags[mt_rand(0, count($this->japaneseTags) - 1)];
	}
    
    /**
     * set up fixed blurbs to tweet out very occasionally instead of a random word
     * 
     * @author Daniel Rhodes
     * @note change these for your feed name and etc
     */
	private function jingleInitiateAll()
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
		$this->jingles[] = 'Did you know that in #Japanese the singular or plural distinction is not important and barely exists?';
		$this->jingles[] = "Did you know that #Japanese doesn't have articles (a, an, the)?";
        $this->jingles[] = 'Your definition requests in English can now have multiple word phrases! For example [@japxlate bad weather] - without the square brackets!';
        $this->jingles[] = 'Definition replies favour the 20,000 (or so!) "common" dictionary entries. This gives you more natural sounding words!';
	}
    
    /**
     * Return, randomly, one of our fixed jingles
     * 
     * @author Daniel Rhodes
     * 
     * @return string one of our jingles
     */
    private function jingleGet()
    {
        log_message('TwitterRobot::jingleGet()');
        
        return $this->jingles[mt_rand(0, count($this->jingles) - 1)];
    }
    
    //----/end utility and helper methods---------------------------------------
}
