<?php
/* Author : Romain "Artefact2" Dalmaso <artefact2@gmail.com>
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details. */

/* Skeleton script that outputs a JSON object. */

if($argc == 1) {
	fwrite(STDERR, "Usage: ".$argv[0]." <sqlitedump1> <sqlitedump2> ...\n");
	die();
}

if(!class_exists('SQLite3')) {
	fwrite(STDERR, "SQLite3 functions not found. Make sure you enabled the sqlite3 extension in your php.ini.\n");
	die();
}

$dumps = $argv;
array_shift($dumps); /* Get rid of the first element (program name) */

$result = array();

foreach($dumps as $dumpfile) {
	try {
		$db = new SQLite3($dumpfile, SQLITE3_OPEN_READONLY);
		process($db, $result);
	} catch(Exception $e) {
		fwrite(STDERR, "Error while processing $dumpfile: ".$e->getMessage()."\n");
	}

	if($db instanceof SQLite3) {
		$db->close();
	}
}

if(function_exists('customsort')) {
	customsort($result);
}

echo json_encode($result, JSON_PRETTY_PRINT)."\n";
