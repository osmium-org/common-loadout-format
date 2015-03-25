<?php
/* Author : Romain "Artefact2" Dalmaso <artefact2@gmail.com>
* This program is free software. It comes without any warranty, to
* the extent permitted by applicable law. You can redistribute it
* and/or modify it under the terms of the Do What The Fuck You Want
* To Public License, Version 2, as published by Sam Hocevar. See
* http://sam.zoy.org/wtfpl/COPYING for more details. */

namespace CommonLoadoutFormat;

/* Validator for the 1st version of the CLF */
/* TODO: check slot/hardpoint usage */

function validate_clf_version_1($json) {
	check_extraneous_properties($json,
	                            array('clf-version', 'client-version',
	                                  'metadata', 'ship',
	                                  'presets', 'drones'));

	if(isset($json['metadata'])) {
		if(!is_array($json['metadata'])) {
			warning("key 'metadata' is present but does not contain a JSON object");
		} else {
			if(validate_metadata_1($json['metadata']) === FATAL) return FATAL;
		}
	}

	if(!isset($json['ship'])) {
		fatal("required key 'ship' not found in the root object");
		return FATAL;
	} else {
		if(!is_array($json['ship'])) {
			fatal("required key 'ship' is present but does not contain a JSON object");
			return FATAL;
		}

		if(validate_ship_1($json['ship']) === FATAL) return FATAL;
	}

	if(assume_array('presets', $json)) {
		foreach($json['presets'] as $preset) {
			if(validate_preset_1($preset) === FATAL) {
				return FATAL;
			}
		}

		assume_unique_names($json['presets']);
	}

	if(assume_array('drones', $json)) {
		foreach($json['drones'] as $dp) {
			if(validate_drone_preset_1($dp) === FATAL) {
				return FATAL;
			}
		}

		assume_unique_names($json['drones']);
	}
}

function validate_metadata_1($metadata) {
	check_extraneous_properties($metadata,
	                            array('title', 'description', 'creationdate'));

	assume_string('title', $metadata);
	assume_string('description', $metadata);

	if(isset($metadata['creationdate'])) {
		$datetime = date_create_from_format(\DateTime::RFC2822, $metadata['creationdate']);
		if($datetime === false) {
			warning("key 'creationdate' is present but does not contain a valid RFC 2822-formatted date", $metadata);
		}
	}
}

function validate_ship_1($ship) {
	check_extraneous_properties($ship, array('typeid', 'typename'));

	if(assume_thing_with_typeid("ship", $ship) === FATAL) {
		return FATAL;
	}
}

function validate_preset_1($preset) {
	check_extraneous_properties($preset,
	                            array('presetname', 'presetdescription',
	                                  'modules', 'implants', 'boosters',
	                                  'chargepresets'));

	assume_string('presetname', $preset);
	assume_string('presetdescription', $preset);

	if(assume_array('modules', $preset)) {
		foreach($preset['modules'] as $module) {
			if(validate_module_1($preset, $module) === FATAL) {
				return FATAL;
			}
		}
	}

	if(assume_array('implants', $preset)) {
		foreach($preset['implants'] as $implant) {
			if(validate_implant_1($preset, $implant) === FATAL) {
				return FATAL;
			}
		}
	}

	if(assume_array('boosters', $preset)) {
		foreach($preset['boosters'] as $booster) {
			if(validate_booster_1($preset, $booster) === FATAL) {
				return FATAL;
			}
		}
	}

	if(assume_array('chargepresets', $preset)) {
		foreach($preset['chargepresets'] as $cp) {
			if(validate_charge_preset_1($preset, $cp) === FATAL) {
				return FATAL;
			}
		}

		assume_unique_names($preset['chargepresets'], 'name');

		/* Enforce preset id uniqueness */
		$ids = array();
		foreach($preset['chargepresets'] as $cp) {
			$id = $cp['id'];
			if(isset($ids[$id])) {
				warning("charge preset has a duplicate id", $cp);
			} else {
				$ids[$id] = true;
			}
		}
	}
}

function validate_module_1($preset, $module) {
	check_extraneous_properties($module,
	                            array('typeid', 'typename',
	                                  'slottype', 'index',
	                                  'state', 'charges'));

	if(assume_thing_with_typeid("module", $module) === FATAL) {
		return FATAL;
	}

	if(isset($module['slottype']) &&
	   !in_array($module['typeid'], [ 'low', 'medium', 'high', 'rig', 'subsystem' ])) {
		warning("key 'slottype' specified, but it contains an invalid value", $module);
		/* TODO: check that slottype is correct */
	}

	assume_integer('index', $module);

	if(isset($module['state'])) {
		if(!in_array($module['state'], array('offline', 'online', 'active', 'overloaded'), true)) {
			warning("key 'state' specified, but it contains an invalid value", $module);
		} else {
			/* TODO: check that the state is possible for this module */
		}
	}

	if(assume_array('charges', $module)) {
		foreach($module['charges'] as $charge) {
			check_extraneous_properties($charge,
			                            array('typeid', 'typename', 'cpid'));

			if(assume_thing_with_typeid("charge", $charge) === FATAL) {
				return FATAL;
			}

			/* TODO: make sure charge can be fitted to module */

			if(assume_integer('cpid', $charge)) {
				/* Make sure there is a charge preset with this id */
				if(!isset($preset['chargepresets'])) {
					warning("key 'cpid' specified, but there are no chargepresets", $charge);
				} else {
					$found = false;
					foreach($preset['chargepresets'] as $cp) {
						if(isset($cp['id']) && $cp['id'] === $charge['cpid']) {
							$found = true;
							break;
						}
					}

					if(!$found) {
						warning("key 'cpid' specified, but there is no charge preset with the same id", $charge);
					}
				}
			}
		}
	}
}

function validate_implant_1($preset, $implant) {
	check_extraneous_properties($implant,
	                            array('typeid', 'typename', 'slot'));

	if(assume_thing_with_typeid("implant", $implant) === FATAL) {
		return FATAL;
	}

	/* TODO: check if specified slot is correct */
	assume_integer('slot', $implant);
}

function validate_booster_1($preset, $booster) {
	check_extraneous_properties($booster,
	                            array('typeid', 'typename', 'slot'));

	if(assume_thing_with_typeid("booster", $booster) === FATAL) {
		return FATAL;
	}

	/* TODO: check if specified slot is correct */
	assume_integer('slot', $booster);
}

function validate_charge_preset_1($preset, $cp) {
	check_extraneous_properties($cp,
	                            array('id', 'name', 'description'));

	if(!isset($cp['id'])) {
		fatal("required key 'id' not found", $cp);
		return FATAL;
	}
	if(!is_int($cp['id'])) {
		fatal("required key 'id' specified, but it does not contain an integer", $cp);
		return FATAL;
	}

	if(!isset($cp['name'])) {
		fatal("required key 'name' not found", $cp);
		return FATAL;
	}
	if(!is_string($cp['name'])) {
		fatal("required key 'name' specified, but it does not contain a string", $cp);
		return FATAL;
	}

	assume_string('description', $cp);	
}

function validate_drone_preset_1($dp) {
	check_extraneous_properties($dp,
	                            array('presetname', 'presetdescription',
	                                  'inbay', 'inspace'));

	assume_string('presetname', $dp);
	assume_string('presetdescription', $dp);

	if(assume_array('inbay', $dp)) {
		foreach($dp['inbay'] as $drone) {
			if(validate_drone_1($dp, $drone) === FATAL) {
				return FATAL;
			}
		}
	}

	if(assume_array('inspace', $dp)) {
		foreach($dp['inspace'] as $drone) {
			if(validate_drone_1($dp, $drone) === FATAL) {
				return FATAL;
			}
		}
	}
}

function validate_drone_1($dronepreset, $drone) {
	check_extraneous_properties($drone,
	                            array('typeid', 'typename', 'quantity'));

	if(assume_thing_with_typeid("drone", $drone) === FATAL) {
		return FATAL;
	}

	if(!isset($drone['quantity'])) {
		fatal("required key 'quantity' not found", $drone);
		return FATAL;
	}
	if(!is_int($drone['quantity']) || $drone['quantity'] < 0) {
		fatal("required key 'quantity' specified, but it does not contain a positive integer", $drone);
		return FATAL;
	}
}
