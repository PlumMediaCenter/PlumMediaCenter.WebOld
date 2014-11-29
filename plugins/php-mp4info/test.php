<?php
/**
 * MP4Info Test Script
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/test.php $
 */

// ---

include "MP4Info.php";

print '<h1>MP4Info test script</h1>';
print '<p><small>'.__FILE__.' $Id: test.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $</small></p>';
print '<hr />';

$dir = './TestFiles/';
$de = opendir($dir);
if ($de) {
	while (($file = readdir($de)) !== false) {
		$path = $dir.$file;
		if ((!is_file($path)) || (!is_readable($path)) || (strtolower(pathinfo($path,PATHINFO_EXTENSION) != 'f4v'))) continue;
		
		print '<h2>'.$file.'</h2>';
		print "<pre>";
		try {
			print_r(MP4Info::getInfo($path));
		} catch (MP4Info_Exception $e) {
			print 'Caught MP4Info_Exception with message '.$e->getMessage();
			throw ($e);
		} catch (Exception $e) {
			print 'Cauth Exception with message '.$e->getMessage();
			throw ($e);
		}
		print "</pre>";
		print '<hr/>';
	}
} else {
	print '<strong>Could not open directory "'.$dir.'".';
}

