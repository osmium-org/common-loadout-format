<?php
/* Author : Romain "Artefact2" Dalmaso <artefact2@gmail.com>
* This program is free software. It comes without any warranty, to
* the extent permitted by applicable law. You can redistribute it
* and/or modify it under the terms of the Do What The Fuck You Want
* To Public License, Version 2, as published by Sam Hocevar. See
* http://sam.zoy.org/wtfpl/COPYING for more details. */

namespace CommonLoadoutFormat;

/* CLF validation library. You can include this file in your project
 * if you want. */

require __DIR__.'/helpers.php';
require __DIR__.'/clf1.php';

const FATAL = 2;
const OK = 0;
const OK_WITH_WARNINGS = 1;

function validate_clf($jsonstring, &$errors = array()) {
	global $__clf_had_warnings;
	global $__clf_errors;

	/* Reset global state */
	$__clf_had_warnings = false;
	$__clf_errors = array();

	$json = json_decode($jsonstring, true);
	if(($error = json_last_error()) !== JSON_ERROR_NONE) {
		if($error === JSON_ERROR_SYNTAX) {
			fatal("JSON syntax error");
		} else {
			fatal("JSON decoding error ".$error);
		}
		$errors = $__clf_errors;
		return FATAL;
	}

	if(!is_array($json)) {
		fatal("the root element must be a JSON object");
		$errors = $__clf_errors;
		return FATAL;
	}

	if(!isset($json['clf-version'])) {
		fatal("required key 'clf-version' cannot be found in the root object");
		$errors = $__clf_errors;
		return FATAL;
	}

	if(!is_int($json['clf-version'])) {
		fatal("key 'clf-version' must be an integer (got ".gettype($json['clf-version']).")");
		$errors = $__clf_errors;
		return FATAL;
	}

	$funcname = __NAMESPACE__.'\validate_clf_version_'.$json['clf-version'];
	if(!function_exists($funcname)) {
		fatal("this validator does not support this version of the common loadout format");
		$errors = $__clf_errors;
		return FATAL;
	}

	if($funcname($json) === FATAL) {
		$errors = $__clf_errors;
		return FATAL;
	}

	$errors = $__clf_errors;
	return ($__clf_had_warnings ? OK_WITH_WARNINGS : OK);
}