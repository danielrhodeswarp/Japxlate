# japxlate

## What is it?

It's a Twitter bot that tweets out random Japanese words - with definitions - once an hour. It also attempts to define people's words (Japanese or English) that they Tweet in. Usage example
sentences are occasionally Tweeted.

英語定義が欲しい場合は「@japxlate 日本語単語」をツイートしてください。リプライで教えます。Japxlate は日本語母語話者用にも便利！

## What's new?

* Finally comes with a script to process the EDICT dictionary file (EDICT2)
* Updated INSTALL instructions
* Full docblock comments for all PHP classes, methods and functions
* Restructured class internals
* Updated for Twitter API v1.1
* Improved logging

## How does it work?

The EDICT dictionary project (http://www.csse.monash.edu.au/~jwb/cgi-bin/wwwjdic.cgi) is used for the random words and to define people's words.
The Tatoeba project (http://tatoeba.org) is used for usage example sentences.
Abraham Williams' TwitterOAuth class (https://github.com/abraham/twitteroauth) is used to connect to Twitter.

## What does it need to run?

* Commandline PHP
* mbstring extension for PHP
* JSON extension for PHP
* mysqli extension for PHP (but should be easy to alter everything for another MySQL extension or indeed another RDBMS)
* MySQL (with Unicode ability)
* The EDICT2 dictionary file
* Some CSV files from Tatoeba.org
* You'll need a Twitter account and then a "My application" (so you can get some OAuth access tokens etc). You'll want "Read, Write and Access direct messages" permission.

## What's hot?

* Source dictionary (EDICT) is of top quality
* Interactive - Twitter users can use the bot as a word defining service
* Bot owner can seed the next *random* word by using a to-self direct message
* Easily modified for any other source dictionary

## What's not?

* Usage example sentences *should* match the previous random word, but sometimes don't
* Direct message seeding can be iffy - I don't think Twitter likes it when users DM themselves!
* Lacks any kind of "cashing in" mechanism for current trending hashtags (ie. defining
the current trend words but in Japanese would be nifty)
* As a timed robot it's pretty cheesy because all it does is call sleep() LOL!
* Obviously you've got all your DB and Twitter credentials in plaintext in the configuration.php
* Defining people's words needs to be less strict and "hit" the dictionary more
* Definition formatting logic is duplicated in MANY places in the code - must consolidate
