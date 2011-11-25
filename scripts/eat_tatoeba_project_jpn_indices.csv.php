<?php

/**
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @copyright  Copyright (c) 2011 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

//DO AFTER eating the sentences

mb_language('Japanese');
mb_internal_encoding('UTF-8');

//----Initialise----------------

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/..';

include $_SERVER['DOCUMENT_ROOT'] . '/conf/include_files.php';

//----/Initialise---------------

//----Start----------------

$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/from_tatoeba.org/jpn_indices.csv', 'r');

if($handle)
{
	while(($line = fgets($handle, 4096)) !== false)
	{
        //echo $line;
        
        $parts = explode("\t", $line);
        
        $sentence_id = $parts[0];	//jap
        $meaning_id = $parts[1];	//eng
        $text = trim($parts[2]);
        
        //FTTB remove all "sense number" crap in square brackets
        $text = preg_replace('/[\[][0-9]{1,}[\]]/', '', $text) . "\n";
        
        $jap_words_in_the_sentence = explode(' ', $text);
        
        foreach($jap_words_in_the_sentence as $word)
        {
        	$checked = 'n';
        	
        	$tilde_trimmed_word = rtrim($word, '~');
        	
        	if(mb_strlen($word) !== mb_strlen($tilde_trimmed_word))	//ie. if tilde was found and removed
        	{
        		$checked = 'y';
        		$word = $tilde_trimmed_word;
        	}
        	
        	//FTTB treat the word "form" - which is in braces - as the kanji's reading
        	//(which means the kanji in our sentences_per_word table will be the root form of the word
        	//with the furigana poss being a different form)
        	//
        	//SO, if we have either a curly bracketed furigana (the "reading") OR a brace bracketed furigana
        	// - treat either as the reading
        	//
        	//BUT IF we have both types of furigana for a word, use the brace bracketed one!
        	
        	$reading = $word;
        	
        	preg_match('/[(].*[)]/', $word, $matches);
        	
        	if(!empty($matches))
        	{
        		$reading = trim($matches[0], '()');
        		
        		$word = preg_replace('/[(].*[)]/', '', $word);
        	}
        	
        	preg_match('/[{].*[}]/', $word, $matches);
        	
        	if(!empty($matches))
        	{
        		$reading = trim($matches[0], '{}');
        		
        		$word = preg_replace('/[{].*[}]/', '', $word);
        	}
        	
        	//for some reason, we occasionally have rougue newlines on the end of words/readings...
        	$word = str_replace("\n", '', $word);
        	$reading = str_replace("\n", '', $reading);
        	
        	$safe_word = MySQL::esc($word);
        	$safe_reading = MySQL::esc($reading);
        	MySQL::exec("INSERT INTO sentences_per_word(kanji, kana, sentence_id, meaning_id, checked) VALUES('{$safe_word}', '{$safe_reading}', {$sentence_id}, {$meaning_id}, '{$checked}')");
        	
        	//ECHO "'{$word}', '{$reading}'\n";
        }
        
        //can use this without looping to blast it all into a flat holding table
        //MySQL::exec("INSERT INTO sentence_breakdown(sentence_id, meaning_id, breakdown) VALUES({$sentence_id}, {$meaning_id}, '{$text}')");
    }
    
    if(!feof($handle))
    {
        echo "Error: unexpected fgets() fail\n";
    }
    
    fclose($handle);
}
