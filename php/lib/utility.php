<?php

/**
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @copyright  Copyright (c) 2011 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

//use the easy peasy http://is.gd API to shorten a URL
function shorten_url($url_without_the_http_bit)
{
	return file_get_contents('http://is.gd/create.php?format=simple&url=' . urlencode($url_without_the_http_bit));
}
