<?php

/**
 * Digest the EDICT2 file (which is EUC-JP encoding) into our database
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */
 
mb_language('Japanese');
mb_internal_encoding('UTF-8');

//----Initialise----------------

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/..';

include $_SERVER['DOCUMENT_ROOT'] . '/conf/include_files.php';

//----/Initialise---------------

//----Start----------------

//note file as downloaded from jdic.com is EUC-JP :-/
$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/edict/edict2', 'r');
//$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/edict/edict_newer', 'r');
//$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/edict/edict_subset', 'r');

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
        
        //skip "header" line (actually not at top of edict2 file!?!)
        if($parts[0] == '　？？？ ')
        {
            continue;
        }
        
        $common = 'n';
        
        //print_r($parts);
        if($parts[count($parts) - 3] == '(P)')
        {
            //print_r($parts);
            $common = 'y';
        }
        //continue;
        
        $kanji_and_poss_kana = trim($parts[0], '] ');
        
        $kanji_and_kana_parts = explode(' [', $kanji_and_poss_kana);
        
        //
        
        
        $kanji = '';
        $kana = '';
        $have_kana = false; //do we have kana for a kanji word?
        
        //kanji word and kana reading both present
        if(count($kanji_and_kana_parts) > 1)
        {
            //take only first of multiple spellings / readings
            
        	$kana = remove_bracketed_all(get_first_from_semicolon_list($kanji_and_kana_parts[1]));
        	$kanji = remove_bracketed_meta(get_first_from_semicolon_list($kanji_and_kana_parts[0]));
        	$have_kana = true;
        }
        
        else    //word not in kanji in the first place (ie. same as reading)
        {
            //take only first of multiple spellings / readings
            
            $kanji = remove_bracketed_meta(get_first_from_semicolon_list($kanji_and_poss_kana));
            $kana = remove_bracketed_all(get_first_from_semicolon_list($kanji_and_poss_kana));
        }
        
        //print_r(array($kanji, $kana));
        
        
        array_shift($parts);
        
        $definitions = array();
        
        foreach($parts as $defSegment)
        {
            $defSegment = trim($defSegment);
            
            if(empty($defSegment))
            {
                continue;
            }
            
            if(strpos($defSegment, 'EntL') === 0)
            {
                continue;
            }
            
            $def = remove_bracketed_meta($defSegment);
            
            if(!empty($def))
            {
                $definitions[] = $def;
            }
        }
        
        if(empty($definitions))
        {
            echo "NOTE no defs" . PHP_EOL;
        }
        
        //print_r($definitions);
        
        
        //echo '----------------' . PHP_EOL;
        
        
        $flat_defs = implode('/', $definitions);
        
        
          
        
        $safeKanji = MySQL::esc($kanji);
        $safeKana = MySQL::esc($kana);
        $safeDefinition = MySQL::esc("/{$flat_defs}/");   //add head and tail slashes for easier searching (in liguistics.php)
        
        MySQL::exec("INSERT INTO edict(kanji, kana, definition, common) VALUES('{$safeKanji}', '{$safeKana}', '{$safeDefinition}', '{$common}')");
        
        
	}
}

//echo "\n{$lines_in_file} total lines :: {$correct_lines} correct lines\n";




//UTILITY----------------

/**
 * remove meta type information in brackets (eg . "(See blah)" or "(vs)" or "(1)")
 * but not if like "(one two)" [ie. with internal space] because these are useful
 * to the definition.
 * note this also removes the actual brackets.
 * 
 * @author Daniel Rhodes
 * 
 * @param string $string
 * @return string
 */
function remove_bracketed_meta($string)
{
    //'U' modifier means ungreedy
    
	$temp = preg_replace('/[(][A-Za-z0-9,:-]{1,}[)]/', '', $string);
    
    $temp = preg_replace('/[(]See[ ].*[)]/U', '', $temp);
            
    return trim($temp);
}

/**
 * remove anything in brackets (including the brackets)
 * 
 * @author Daniel Rhodes
 * 
 * @param string $string
 * @return string
 */
function remove_bracketed_all($string)
{
    //'U' modifier means ungreedy
    //'u' modifier mean utf-8 unicode
    
	//$temp = preg_replace('/[(][A-Za-z0-9,:-]{1,}[)]/', '', $string);
    
    $temp = preg_replace('/[(].*[)]/U', '', $string);
            
    return trim($temp);
}

/**
 * return first item from a semicolon-separated list of items
 * 
 * @author Daniel Rhodes
 * 
 * @param string $list
 * @return string
 */
function get_first_from_semicolon_list($list)
{
    $items = explode(';', $list);
    
    if(is_array($items))
    {
        return $items[0];
    }
    
    return $items;  //no semicolons anyway
}
