<?php

/**
 * Libwrary file containing functions to manipulate ja <--> en xlations
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

//SOME JUNK TO 'FORCE' UNICODE FILE OPENING IN CERTAIN IDEs
//@japxlate morning'  [OK]
//[] '@japxlate good-for-nothing'  [OK]
//[] '@japxlate 正しい'  [OK]
//[] '@japxlate 主張'  [OK]
//[] '@japxlate why hello there'  [NG]
//[] '@japxlate 正しい  主張'  [NG]

/**
 * Check if the passed $string is multibyte or not
 * 
 * @author Daniel Rhodes
 * @note see also PHP's is_unicode() function
 * 
 * @param string $string the string to check
 * @return bool true if $string contains at least one multibyte character
 */
function is_mb($string)
{
    //if character length is not same as *byte* length, then at least one char is multibyte
	return(strlen($string) != mb_strlen($string));
}

/**
 * Convert hiragana (if present) in Japanese string into katakana
 * 
 * @author Daniel Rhodes
 * @note "hankaku" is ignored here
 * @note function return is same as input if input has no hiragana
 * 
 * @param string $japanese Japanese string with (potential) hiragana
 * @return string as $japanese but with hiragana converted into katakana
 */
function hira_to_kata($japanese)
{
	//'C' = Convert "zen-kaku hira-gana" to "zen-kaku kata-kana"
	return mb_convert_kana($japanese, 'C');
}

/**
 * Convert katakana (if present) in Japanese string into hiragana
 * 
 * @author Daniel Rhodes
 * @note "hankaku" is ignored here
 * @note function return is same as input if input has no katakana
 * 
 * @param string $japanese Japanese string with (potential) katakana
 * @return string as $japanese but with katakana converted into hiragana
 */
function kata_to_hira($japanese)
{
	//'C' = Convert "zen-kaku kata-kana" to "zen-kaku hira-gana"
	return mb_convert_kana($japanese, 'c');
}

/**
 * Convert zenkaku kana to romaji
 * 
 * @author Daniel Rhodes
 * @note many many gaps in this!
 * @note perhaps use PHP's recode_string() instead
 * @note logs to language log if any transliteration errors
 * 
 * @param string $kana_string source string in katakana and / or hiragana
 * @return string the source string converted into romaji
 */
function kana_to_romaji($kana_string)
{
	//force KATAkana
	$katakanaString = hira_to_kata($kana_string);
	
	$multiSearch = array
	(
		'チャ',
		'チュ',
		'チェ',
		'チョ',
		
		'シャ',
		'シュ',
		'シェ',
		'ショ',
		
		'ジャ',
		'ジュ',
		'ジェ',
		'ジョ',
		
		'キャ',
		'キュ',
		'キョ',
		
		'ギャ',
		'ギュ',
		'ギョ',
		
		'リュ',
		'リョ',
		
		'ミャ',
		'ミュ',
		'ミョ',
		
		'ヒャ',
		'ヒュ',
		'ヒョ',
		
		'ニャ',
		'ニュ',
		'ニョ',
		
		'ビャ',
		'ビュ',
		'ビョ',
		
		'ピャ',
		'ピュ',
		'ピョ',
		
		'ヂャ',
		'ヂュ',
		'ヂョ',
		
		'ファ',
		'フィ',
		'フェ',
		'フォ',
		
		'ウィ',
		'ウェ',
		'ウォ',
		
		'ヴァ',
		'ヴィ',
		'ヴェ',
		'ヴォ',
		
		'ティ',
		'ディ'
	);
	
	$multiReplace = array
	(
		'cha',
		'chu',
		'che',
		'cho',
		
		'sha',
		'shu',
		'she',
		'sho',
		
		'ja',
		'ju',
		'je',
		'jo',
		
		'kya',
		'kyu',
		'kyo',
		
		'gya',
		'gyu',
		'gyo',
		
		'ryu',
		'ryo',
		
		'mya',
		'myu',
		'myo',
		
		'hya',
		'hyu',
		'hyo',
		
		'nya',
		'nyu',
		'nyo',
		
		'bya',
		'byu',
		'byo',
		
		'pya',
		'pyu',
		'pyo',
		
		'dya',
		'dyu',
		'dyo',
		
		'fa',
		'fi',
		'fe',
		'fo',
		
		'wi',
		'we',
		'wo',
		
		'va',
		'vi',
		've',
		'vo',
		
		'ti',
		'di'
	);
	
	$basicSearch = array
	(
		'ア',
		'イ',
		'ウ',
		'エ',
		'オ',
		
		'ヴ',
		
		'カ',
		'キ',
		'ク',
		'ケ',
		'コ',
		
		'ガ',
		'ギ',
		'グ',
		'ゲ',
		'ゴ',
		
		'タ',
		'チ',
		'ツ',
		'テ',
		'ト',
		
		'ダ',
		'ヂ',
		'ヅ',
		'デ',
		'ド',
		
		'ハ',
		'ヒ',
		'フ',
		'ヘ',
		'ホ',
		
		'バ',
		'ビ',
		'ブ',
		'ベ',
		'ボ',
		
		'パ',
		'ピ',
		'プ',
		'ペ',
		'ポ',
		
		'マ',
		'ミ',
		'ム',
		'メ',
		'モ',
		
		'ナ',
		'ニ',
		'ヌ',
		'ネ',
		'ノ',
		
		'サ',
		'シ',
		'ス',
		'セ',
		'ソ',
		
		'ザ',
		'ジ',
		'ズ',
		'ゼ',
		'ゾ',
		
		'ラ',
		'リ',
		'ル',
		'レ',
		'ロ',
		
		'ヤ',
		'ユ',
		'ヨ',
		
		'ン',
		'ワ',
		'ヲ'
	);
	
	$basicReplace = array
	(
		'a',
		'i',
		'u',
		'e',
		'o',
		
		'vu',
		
		'ka',
		'ki',
		'ku',
		'ke',
		'ko',
		
		'ga',
		'gi',
		'gu',
		'ge',
		'go',
		
		'ta',
		'chi',
		'tsu',
		'te',
		'to',
		
		'da',
		'di',
		'du',
		'de',
		'do',
		
		'ha',
		'hi',
		'fu',
		'he',
		'ho',
		
		'ba',
		'bi',
		'bu',
		'be',
		'bo',
		
		'pa',
		'pi',
		'pu',
		'pe',
		'po',
		
		'ma',
		'mi',
		'mu',
		'me',
		'mo',
		
		'na',
		'ni',
		'nu',
		'ne',
		'no',
		
		'sa',
		'shi',
		'su',
		'se',
		'so',
		
		'za',
		'ji',
		'zu',
		'ze',
		'zo',
		
		'ra',
		'ri',
		'ru',
		're',
		'ro',
		
		'ya',
		'yu',
		'yo',
		
		'n',
		'wa',
		'wo'
	);
	
	$romajiString = str_replace($multiSearch, $multiReplace, $katakanaString);
	
	$romajiString = str_replace($basicSearch, $basicReplace, $romajiString);
	
	$romajiString = preg_replace('|ッchi|', 'tchi', $romajiString);
	
	$romajiString = preg_replace('|ッ([a-z]{1})|', '${1}${1}', $romajiString);
	
	//Smash hyphens ('-') used as 'ー'
	$romajiString = preg_replace('|([^0-9])[-]([^0-9])|', '${1}ー${2}', $romajiString);
	
	$romajiString = preg_replace('|([a-z]{1})ー|', '${1}${1}', $romajiString);
	
    //log any transliteration failures (ie. $romajiString still has katakana in it)
    if(is_mb($romajiString))
    {
        log_language("NOTE '{$kana_string}' has been transliterated to '{$romajiString}'");
    }
    
	return $romajiString;
}

/**
 * Convert romaji to zenkaku hiragana
 * 
 * @author Daniel Rhodes
 * @note many many gaps in this!
 * @note perhaps use PHP's recode_string() instead
 * @note get small tsu etc working
 * 
 * @param string $romaji_string source string in romaji
 * @return string the source string converted into hiragana
 */
function romaji_to_hiragana($romaji_string)
{
	$multiReplace = array
	(
		'チャ',
		'チュ',
		'チェ',
		'チョ',
		
		'シャ',
		'シュ',
		'シェ',
		'ショ',
		
		'ジャ',
		'ジュ',
		'ジェ',
		'ジョ',
		
		'キャ',
		'キュ',
		'キョ',
		
		'ギャ',
		'ギュ',
		'ギョ',
		
		'リュ',
		'リョ',
		
		'ミャ',
		'ミュ',
		'ミョ',
		
		'ヒャ',
		'ヒュ',
		'ヒョ',
		
		'ニャ',
		'ニュ',
		'ニョ',
		
		'ビャ',
		'ビュ',
		'ビョ',
		
		'ピャ',
		'ピュ',
		'ピョ',
		
		'ヂャ',
		'ヂュ',
		'ヂョ',
		
		'ファ',
		'フィ',
		'フェ',
		'フォ',
		
		'ウィ',
		'ウェ',
		'ウォ',
		
		'ヴァ',
		'ヴィ',
		'ヴェ',
		'ヴォ',
		
		'ティ',
		'ディ'
	);
	
	$multiSearch = array
	(
		'cha',
		'chu',
		'che',
		'cho',
		
		'sha',
		'shu',
		'she',
		'sho',
		
		'ja',
		'ju',
		'je',
		'jo',
		
		'kya',
		'kyu',
		'kyo',
		
		'gya',
		'gyu',
		'gyo',
		
		'ryu',
		'ryo',
		
		'mya',
		'myu',
		'myo',
		
		'hya',
		'hyu',
		'hyo',
		
		'nya',
		'nyu',
		'nyo',
		
		'bya',
		'byu',
		'byo',
		
		'pya',
		'pyu',
		'pyo',
		
		'dya',
		'dyu',
		'dyo',
		
		'fa',
		'fi',
		'fe',
		'fo',
		
		'wi',
		'we',
		'wo',
		
		'va',
		'vi',
		've',
		'vo',
		
		'ti',
		'di'
	);
	
	$basicReplace = array
	(
		'ヴ',
		
		'カ',
		'キ',
		'ク',
		'ケ',
		'コ',
		
		'ガ',
		'ギ',
		'グ',
		'ゲ',
		'ゴ',
		
		'タ',
		'チ',
		'ツ',
		'テ',
		'ト',
		
		'ダ',
		'ヂ',
		'ヅ',
		'デ',
		'ド',
		
		'ハ',
		'シ',
		'フ',
		'ヘ',
		'ホ',
		
		'バ',
		'ビ',
		'ブ',
		'ベ',
		'ボ',
		
		'パ',
		'ピ',
		'プ',
		'ペ',
		'ポ',
		
		'マ',
		'ミ',
		'ム',
		'メ',
		'モ',
		
		'ナ',
		'ニ',
		'ヌ',
		'ネ',
		'ノ',
		
		'サ',
		'ヒ',
		'ス',
		'セ',
		'ソ',
		
		'ザ',
		'ジ',
		'ズ',
		'ゼ',
		'ゾ',
		
		'ラ',
		'リ',
		'ル',
		'レ',
		'ロ',
		
		'ヤ',
		'ユ',
		'ヨ',
		
		'ン',
		'ワ',
		'ヲ',
		
		'ア',
		'イ',
		'ウ',
		'エ',
		'オ'
	);
	
	$basicSearch = array
	(
		'vu',
		
		'ka',
		'ki',
		'ku',
		'ke',
		'ko',
		
		'ga',
		'gi',
		'gu',
		'ge',
		'go',
		
		'ta',
		'chi',
		'tsu',
		'te',
		'to',
		
		'da',
		'di',
		'du',
		'de',
		'do',
		
		'ha',
		'shi',
		'fu',
		'he',
		'ho',
		
		'ba',
		'bi',
		'bu',
		'be',
		'bo',
		
		'pa',
		'pi',
		'pu',
		'pe',
		'po',
		
		'ma',
		'mi',
		'mu',
		'me',
		'mo',
		
		'na',
		'ni',
		'nu',
		'ne',
		'no',
		
		'sa',
		'hi',
		'su',
		'se',
		'so',
		
		'za',
		'ji',
		'zu',
		'ze',
		'zo',
		
		'ra',
		'ri',
		'ru',
		're',
		'ro',
		
		'ya',
		'yu',
		'yo',
		
		'n',
		'wa',
		'wo',
		
		'a',
		'i',
		'u',
		'e',
		'o'
	);
	
	$katakanaString = str_replace($multiSearch, $multiReplace, $romaji_string);
	
	$katakanaString = str_replace($basicSearch, $basicReplace, $katakanaString);
	
	//$katakanaString = preg_replace('|ッchi|', 'tchi', $katakanaString);
	
	//$katakanaString = preg_replace('|ッ([a-z]{1})|', '${1}${1}', $katakanaString);
	
	//Smash hyphens ('-') used as 'ー'
	//$katakanaString = preg_replace('|([^0-9])[-]([^0-9])|', '${1}ー${2}', $katakanaString);
	
	//$katakanaString = preg_replace('|([a-z]{1})ー|', '${1}${1}', $katakanaString);
	
	//force HIRAgana
	$hiraganaString = kata_to_hira($katakanaString);
	
	return $hiraganaString;
}

/**
 * Search dictionary for a definition of the specified English word
 * 
 * @author Daniel Rhodes
 * 
 * @param string $word English word (OR ROMAJI) to get a translation for
 * @return string empty string if no translation found ELSE full definition line
 */
function get_xlation_for_en($word)
{
    //search for exact match in definition list
	$best_matches = MySQL::queryAll('SELECT * FROM edict WHERE definition LIKE \'%/' . MySQL::esc($word) . '/%\'');
	
	if(!empty($best_matches))
	{
		//$row = $best_matches[0];	//or randomise?
		$row = $best_matches[mt_rand(0, count($best_matches) - 1)];	//or prioritise?
		
		$romaji = kana_to_romaji($row['kana']);
		
		$definition = format_slashes($row['definition']);
		
		return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
	}
	
	else
	{
        //search for partial match in definition list
		$second_best_matches = MySQL::queryAll('SELECT * FROM edict WHERE definition LIKE \'%' . MySQL::esc($word) . '%\'');
	
		if(!empty($second_best_matches))
		{
			//$row = $second_best_matches[0];	//or randomise?
			$row = $second_best_matches[mt_rand(0, count($second_best_matches) - 1)];	//or prioritise?
			
			$romaji = kana_to_romaji($row['kana']);
			
			$definition = format_slashes($row['definition']);
			
			return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
		}
		
		else	//try word as jap word in romaji
		{
			$word_in_kana = romaji_to_hiragana($word);
			
			$romaji_matches = MySQL::queryAll('SELECT * FROM edict WHERE kana LIKE \'' . MySQL::esc($word_in_kana) . '\'');
	
			if(!empty($romaji_matches))
			{
				//$row = $romaji_matches[0];	//or randomise?
				$row = $romaji_matches[mt_rand(0, count($romaji_matches) - 1)];	//or prioritise?
				
				$romaji = kana_to_romaji($row['kana']);
				
				$definition = format_slashes($row['definition']);
				
				return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
			}
		}
	}
	
	return '';
}

/**
 * Search dictionary for a definition of the specified Japanese word
 * 
 * @author Daniel Rhodes
 * 
 * @param string $word Japanese word (kanji or kana) to get a translation for
 * @return string empty string if no translation found ELSE full definition line
 */
function get_xlation_for_ja($word)
{
    //search for match as kanji
	$best_matches = MySQL::queryAll('SELECT * FROM edict WHERE kanji LIKE \'' . MySQL::esc($word) . '\'');
	
	if(!empty($best_matches))
	{
		//$row = $best_matches[0];	//or randomise?
		$row = $best_matches[mt_rand(0, count($best_matches) - 1)];	//or prioritise?
		
		$romaji = kana_to_romaji($row['kana']);
		
		$definition = format_slashes($row['definition']);
		
		return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
	}
	
	else
	{
        //search for match as kana
		$second_best_matches = MySQL::queryAll('SELECT * FROM edict WHERE kana LIKE \'' . MySQL::esc(kata_to_hira($word)) . '\'');
	
		if(!empty($second_best_matches))
		{
			//$row = $second_best_matches[0];	//or randomise?
			$row = $second_best_matches[mt_rand(0, count($second_best_matches) - 1)];	//or prioritise?
			
			$romaji = kana_to_romaji($row['kana']);
			
			$definition = format_slashes($row['definition']);
			
			return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
		}
	}
	
	return '';
}

/**
 * For the given Japanese word, try to get an example usage sentence from our
 * sentences DB tables
 * 
 * @author Daniel Rhodes
 * @note could poss unroll the query for better performance
 * @note query (and jpn_indices.csv eating script) could prob be tweaked for more accurate results
 * 
 * @param string $kanji word in kanji
 * @param string $kana word in kana
 * @param bool $include_link_to_tatoeba_project optional. default true. true to include a link (in the returned string) to the sentence on tatoeba.org
 * @return string empty string if no sentence found ELSE example sentence using word
 */
function get_sentence_for_ja_word($kanji, $kana, $include_link_to_tatoeba_project = true)
{
	$sentence = '';
	
	$safe_kanji = MySQL::esc($kanji);
	$safe_kana = MySQL::esc($kana);
	
	$max_length_of_sentences = 140;
	if($include_link_to_tatoeba_project)
	{
		//bitly seems to use 6 characters (ie. http://bit.ly/e7hGkz)
		//and so is 20 characters long.
		//SO, search for shorter sentences
		//$max_length_of_sentences = 120;
		
		//UPDATE: Twitter now using http://t.co/X7ZeJQ7n (displays as t.co/X7ZeJQ7n)
				
		//ANOTHER UPDATE: NOT SURE IT WORKS LIKE THIS DANIEL! (whatever long link you type in at the
		//tweet box seems to count as characters in the tweet. It is *then* shortened
		//for display purposes etc)
		//so use a link shortener manually to get a short link BEFORE the Tweet is submitted
		
		//assume that the link shortener we use always uses a same size shortened URL
		$short_url_length = strlen(shorten_url('www.example.com')); //hmmm
		
		$max_length_of_sentences = 140 - $short_url_length;
	}
	
	//this SQL not v. accurate it has to be said...
	$sql = <<<SQL
SELECT
	sentences_per_word.checked,
	sent.id AS ja_sent_id,
	sent.sentence AS ja_sent,
	tran.sentence AS en_sent
FROM
	sentence as sent,
	sentence as tran,
	sentences_per_word,
	edict 
WHERE
	sent.id = sentences_per_word.sentence_id AND tran.id = sentences_per_word.meaning_id
	AND (sentences_per_word.kanji = edict.kanji 
-- following gives usually more answers but can be sometimes off
 	OR sentences_per_word.kana = edict.kana
	)
	
-- following is super duper slow
-- AND sentences_per_word.kana = edict.kana

	AND edict.kanji = '{$safe_kanji}' AND edict.kana = '{$safe_kana}'

-- a double-checking idea/experiment
-- and sent.sentence like '%KANJI_OR_ROOT_OF_KANJI%'

-- not enough checked translation in the tatoeba project FTTB!
-- and sentences_per_word.checked = 'y'

	AND CHAR_LENGTH(CONCAT(sent.sentence, ' / ', tran.sentence)) < {$max_length_of_sentences}
LIMIT
	0,10
SQL;

	$results = MySQL::queryAll($sql);
	
	if(!empty($results))
	{
		shuffle($results);
		
		$the_one = $results[0];
		
		$sentence = $the_one['ja_sent'] . ' / ' . $the_one['en_sent'];
		
		if($include_link_to_tatoeba_project)
		{
			$sentence .= ' ' . shorten_url('http://tatoeba.org/eng/sentences/show/' . $the_one['ja_sent_id']);
		}
	}
	
	return $sentence;
}
