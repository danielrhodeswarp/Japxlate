<?php

/**
 * Digest the Tatoeba.org "links.csv" file into our database.
 * The file defines which sentences are equivalent (ie. translations of each other)
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

//RUN THIS SCRIPT AFTER RUNNING eat_tatoeba_project_sentences.csv.php

mb_language('Japanese');
mb_internal_encoding('UTF-8');

//----Initialise----------------

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/..';

include $_SERVER['DOCUMENT_ROOT'] . '/conf/include_files.php';

//----/Initialise---------------

//----Start----------------

$handle = @fopen($_SERVER['DOCUMENT_ROOT'] . '/csv/from_tatoeba.org/links.csv', 'r');

if($handle)
{
    while(($line = fgets($handle, 4096)) !== false)
    {
        //echo $line;
        
        $parts = explode("\t", $line);
        
        $sentence_id = $parts[0];
        $translation_id = trim($parts[1]);
        
        //skip if we don't have both sentences (ie. skip non-eng and non-jpn)
        //(tatoeba project has many other languages)
        $sentence = MySQL::queryRow("SELECT * FROM sentence WHERE id = {$sentence_id}");
        $translation = MySQL::queryRow("SELECT * FROM sentence WHERE id = {$translation_id}");
        if(!is_array($sentence) or !is_array($translation))
        {
        	continue;
        }
        
        MySQL::exec("INSERT INTO sentence_link(sentence_id, translation_id) VALUES({$sentence_id}, {$translation_id})");
    }
    
    if(!feof($handle))
    {
        echo "Error: unexpected fgets() fail\n";
    }
    
    fclose($handle);
}
