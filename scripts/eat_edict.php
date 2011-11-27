<?php

/**
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @copyright  Copyright (c) 2011 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */
 

//WHERE HAS THIS GOT TO? THIS IS JUST AN ALMOST THERE
//SCRATCHPAD!!!

mb_language('Japanese');
mb_internal_encoding('UTF-8');

//----Initialise----------------

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/..';

include $_SERVER['DOCUMENT_ROOT'] . '/conf/include_files.php';

//----/Initialise---------------

//----Start----------------

//note file as downloaded from jdic.com is EUC-JP :-/
//$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/edict/edict', 'r');
//$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/edict/edict_newer', 'r');
$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/edict/edict_subset', 'r');

$lines_in_file = 0;
$correct_lines = 0;

if($handle)
{
	while(($line = fgets($handle, 4096)) !== false)
	{
		$lines_in_file++;
		
        //echo $line;
        
        $line = mb_convert_encoding($line, 'UTF-8', 'EUC-JP');
        
        //echo $line;
        
        $parts = explode("/", $line);
        
        //print_r($parts);
        
        $kanji_and_poss_kana = trim($parts[0], '] ');
        
        $kanji_and_kana_parts = explode(' [', $kanji_and_poss_kana);
        
        $kanji = $kanji_and_poss_kana;
        $kana = $kanji_and_poss_kana;
        $have_kana = false;
        if(count($kanji_and_kana_parts) > 1)
        {
        	$kana = $kanji_and_kana_parts[1];
        	$kanji = $kanji_and_kana_parts[0];
        	$have_kana = true;
        }
        
        
        $fart = array_shift($parts);
        
        preg_match('/^([(].*[)][ ]){1,}/', $parts[0], $matches);
        //print_r($matches);
        
        $wordtype_ish = trim($matches[0]);
        
        
        
        $parts[0] = str_replace($wordtype_ish, '', $parts[0]);
        
       	$clean_parts = array_map('remove_bracketed_numbers', $parts);
       	$clean_parts = array_map('trim', $clean_parts);
        
        $flat_defs = implode('/', $clean_parts);
        
        
        $result = array();
        $result['kanji'] = "'{$kanji}'";
        $result['kana'] = "'{$kana}'";
        $result['wordtype'] = "'{$wordtype_ish}'";
        $result['definition'] = $flat_defs;
        
        
        
        
        
        echo $line;
        print_r($result);
        echo "----------------------------\n";
        
        /*
        $pattern = "{$kanji} /";
        if($have_kana)
        {
        	$pattern = "{$kanji} [{$kana}] /";
        }
        
        //$match = preg_match($pattern, $line);
        //mb_regex_encoding('UTF-8');
        //$match = mb_ereg_match($pattern, $line);
        $match = mb_strpos($line, $pattern);
        
        if($match === 0)
        {
        	
        	$correct_lines++;
        }
        
        else
        {
        	//echo $line;
        	//print_r($result);
        	//echo "{$pattern}\n";
        }
        */
        
	}
}

//echo "\n{$lines_in_file} total lines :: {$correct_lines} correct lines\n";




//UTILITY----------------

function remove_bracketed_numbers($string)
{
	return preg_replace('/[(][0-9]{1}[)]/', '', $string);
}







