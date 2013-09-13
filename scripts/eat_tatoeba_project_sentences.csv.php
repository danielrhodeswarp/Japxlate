<?php

/**
 * Digest the Tatoeba.org "sentences.csv" file into our database.
 * The file is a list of sentences in different languages
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

//RUN THIS SCRIPT BEFORE RUNNING eat_tatoeba_project_links.csv.php

mb_language('Japanese');
mb_internal_encoding('UTF-8');

//----Initialise----------------

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/..';

include $_SERVER['DOCUMENT_ROOT'] . '/conf/include_files.php';

//----/Initialise---------------

//----Start----------------

$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/from_tatoeba.org/sentences.csv', 'r');

if($handle)
{
    while(($line = fgets($handle, 4096)) !== false)
    {
        //echo $line;
        
        $parts = explode("\t", $line);
        
        $id = $parts[0];
        $lang_code = $parts[1];
        
        //skip non-eng and non-jpn (tatoeba project has many other languages)
        if($lang_code != 'jpn' and $lang_code != 'eng')
        {
        	continue;
        }
        
        $sentence = trim($parts[2]);
        $safe_sentence = MySQL::esc($sentence);
        
        //echo $sentence . "\n";
        
        MySQL::exec("INSERT INTO sentence(id, lang_code, sentence) VALUES({$id}, '{$lang_code}', '{$sentence}')");
    }
    
    if(!feof($handle))
    {
        echo "Error: unexpected fgets() fail\n";
    }
    
    fclose($handle);
}
