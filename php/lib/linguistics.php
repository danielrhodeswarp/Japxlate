<?php

/**
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @copyright  Copyright (c) 2011 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

	//SOME JUNK TO 'FORCE' UNICODE FILE OPENING IN IDE
	//@japxlate morning'  [OK]
	//[] '@japxlate good-for-nothing'  [OK]
	//[] '@japxlate 正しい'  [OK]
	//[] '@japxlate 主張'  [OK]
	//[] '@japxlate why hello there'  [NG]
	//[] '@japxlate 正しい  主張'  [NG]

//
function merge_multiple_sentences(array $sentences)
{
	$result = '';
	
	if(count($sentences) > 3)
	{
		$sentences = array_slice($sentences, 0, 3);
	}
	
	foreach($sentences as $sentence)
	{
		//$words = preg_split("/\s+/", $sentence);
		$words = preg_split('/\s+/', $sentence);
		
		//if($count($words) < 4))
		//{
		//	
		//}
		
		$result .= "{$words[0]} {$words[1]} {$words[2]} {$words[3]}";
	}
	
	return $result;
}

//Returns true if passed string contains multibyte characters
//(in fact, PHP already *seems* to have an "is_unicode" function!)
function is_mb($string)
{
	return(strlen($string) != mb_strlen($string));
}

//Convert hiragana (if present) into katakana
function hira_to_kata($japanese)
{
	//'C' = Convert "zen-kaku hira-gana" to "zen-kaku kata-kana"
	return mb_convert_kana($japanese, 'C');
}

//Convert katakana (if present) into hiragana
function kata_to_hira($japanese)
{
	//'C' = Convert "zen-kaku kata-kana" to "zen-kaku hira-gana"
	return mb_convert_kana($japanese, 'c');
}

//no truck with this one :-(
//
//reason that if converting the entire string into katakana results
//in no change, then the string must already be katakana
//(and same for hiragana so string is hira *or* kata kana)
//
//use levenshtein() or similar_text() [both of which work at the BYTE level and not the character level btw...] ??
/*
function is_kanaFART($string)
{
	if((levenshtein($string, hira_to_kata($string)) == strlen($string)) or (levenshtein($string, kata_to_hira($string)) == strlen($string)))
	{
		return true;
	}
	
	return false;
}
*/

//no truck with this one :-(
/*
function is_kana($string)
{
	return mb_ereg_match('/^[¤¢-¤ó]{1,}$/i', $string);
}
*/

//Convert zenkaku kana to romaji
//(perhaps also look at recode_string() )
//many many gaps in this!
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
	
	return $romajiString;
}

//Convert romaji to zenkaku hiragana
//REVERSE VERSION OF kana_to_romaji()!
//(get small tsu etc working)
//(perhaps also look at recode_string() )
//many many gaps in this!
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

//
function get_xlation_for_en($word)
{
	$best_matches = MySQL::queryAll('SELECT * FROM edict WHERE definition LIKE \'%; ' . MySQL::esc($word) . ';%\' ORDER BY wordtype ASC');
	
	if(!empty($best_matches))
	{
		//$row = $best_matches[0];	//or randomise?
		$row = $best_matches[rand(0, count($best_matches) - 1)];	//or prioritise?
		
		$romaji = kana_to_romaji($row['kana']);
		
		$definition = trim($row['definition'], ' ;');
		
		return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
	}
	
	else
	{
		$second_best_matches = MySQL::queryAll('SELECT * FROM edict WHERE definition LIKE \'%' . MySQL::esc($word) . '%\' ORDER BY wordtype ASC');
	
		if(!empty($second_best_matches))
		{
			//$row = $second_best_matches[0];	//or randomise?
			$row = $second_best_matches[rand(0, count($second_best_matches) - 1)];	//or prioritise?
			
			$romaji = kana_to_romaji($row['kana']);
			
			$definition = trim($row['definition'], ' ;');
			
			return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
		}
		
		else	//try word as jap word in romaji
		{
			$word_in_kana = romaji_to_hiragana($word);
			
			$romaji_matches = MySQL::queryAll('SELECT * FROM edict WHERE kana LIKE \'' . MySQL::esc($word_in_kana) . '\' ORDER BY wordtype ASC');
	
			if(!empty($romaji_matches))
			{
				//$row = $romaji_matches[0];	//or randomise?
				$row = $romaji_matches[rand(0, count($romaji_matches) - 1)];	//or prioritise?
				
				$romaji = kana_to_romaji($row['kana']);
				
				$definition = trim($row['definition'], ' ;');
				
				return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
			}
		}
	}
	
	return '';
}

//
function get_xlation_for_ja($word)
{
	$best_matches = MySQL::queryAll('SELECT * FROM edict WHERE kanji LIKE \'' . MySQL::esc($word) . '\' ORDER BY wordtype ASC');
	
	if(!empty($best_matches))
	{
		//$row = $best_matches[0];	//or randomise?
		$row = $best_matches[rand(0, count($best_matches) - 1)];	//or prioritise?
		
		$romaji = kana_to_romaji($row['kana']);
		
		$definition = trim($row['definition'], ' ;');
		
		return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
	}
	
	else
	{
		$second_best_matches = MySQL::queryAll('SELECT * FROM edict WHERE kana LIKE \'' . MySQL::esc(kata_to_hira($word)) . '\' ORDER BY wordtype ASC');
	
		if(!empty($second_best_matches))
		{
			//$row = $second_best_matches[0];	//or randomise?
			$row = $second_best_matches[rand(0, count($second_best_matches) - 1)];	//or prioritise?
			
			$romaji = kana_to_romaji($row['kana']);
			
			$definition = trim($row['definition'], ' ;');
			
			return "{$row['kanji']} / {$row['kana']} ({$romaji}) / {$definition}";
		}
	}
	
	return '';
}

//could poss unroll the query for better performance
//also, query (and jpn_indices.csv eating script) could prob be tweaked for more accurate results
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
		$short_url_length = strlen(shorten_url('www.example.com'));
		
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
