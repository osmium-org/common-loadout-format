Common Loadout Format

# Common Loadout Format
## Draft specification, version 1

**********

# Abstract

The common loadout format (CLF) is a JSON based format for defining a
fitting in the EVE Online multiplayer game. The format strives to
achieve portability, completeness, extendability and longevity.

# Status of this memo

This document is a draft, and it is inappropriate to rely on it as it
is still a work in progress.

This document is placed under the public domain. Discussions about the
format should take place on `#evefit` at `irc.coldfront.net`.

This draft is set to expire the 15th of July 2012. After this date,
this draft will either be promoted to become a standard, or a new
draft will be published.

# Conformance

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT",
"SHOULD", "SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this
document are to be interpreted as described in RFC 2119[^1].

[^1]: [RFC 2119](http://www.ietf.org/rfc/rfc2119.txt), Key words for
use in RFCs to Indicate Requirement Levels

# Credits

- Romain "Artefact2" Dalmaso, &lt;romaind at artefact2 dot com&gt;
- Diego "Sakari" Duclos, &lt;diego dot duclos at gmail dot com&gt;
- Norman Dankert, &lt;norman dot dankert at gmail dot com&gt;

# Table of contents

- [1. Introduction](#introduction)
  - [1.1. Purpose](#purpose)
  - [1.2. Terminology](#terminology)
- [2. Format specification](#format-specification)
  - [2.1. MIME type](#mime-type)
  - [2.2. Top-level structure](#top-level-structure)
  - [2.3. Encoding](#encoding)
  - [2.4. Additional fields](#additional-fields)
  - [2.5. `metadata` section](#metadata-section)
  - [2.6. `ship` section](#ship-section)
  - [2.7. `presets` section](#presets-section)
     - [2.7.1. `module` element](#module-element)
     - [2.7.2. `charge` element](#charge-element)
     - [2.7.3. `implant` element](#implant-element)
     - [2.7.4. `booster` element](#booster-element)
     - [2.7.5. `charge preset` element](#charge-preset-element)
  - [2.8. `drones` section](#drones-section)
     - [2.8.1. `drone` element](#drone-element)
- [3. Parsing rules and recommendations](#parsing-rules)
  - [3.1. Duplicates and conflicts](#duplicates-and-conflicts)
  - [3.2. Overflows](#overflows)
  - [3.3. Interpolating data](#interpolating-data)
  - [3.4. Nonsensical data](#nonsensical-data)

**********

# 1. Introduction                               {#introduction}

## 1.1. Purpose                                 {#purpose}

The common loadout format is designed to be easy to parse by programs
and easy to read by humans. It is also unambiguous as it uses
`TypeID`s to uniquely determine all the items involved in the
fitting. It is designed to be both suited for long-term backup
storage, and for interoperability between different EVE Online related
third-party applications.

## 1.2. Terminology                             {#terminology}

fitting
loadout
: A given ship with information about its state, including fitted modules,
fitted charges, drones, implants, etc. and possibly variations of those.

entity
: Refers to a program which is trying to support the common loadout
format.

preset
: Defines one possible variation of the loadout. Includes modules,
charges (possibly with presets of their own), boosters and implants,
but not drones.

charge preset
: Defines one possible configuration for all the charges in fitted
modules. One preset (as defined above) can contain multiple charge
presets; charge presets can only belong in one preset.

drone preset
: Defines one possible configuration of the drone bay of the ship,
such as number and type of drones, and which drones are in space.

# 2. Format specification                       {#format-specification}

## 2.1. MIME type                               {#mime-type}

A loadout in the common loadout format is nothing more than a JSON
file, so it should be transmitted using the JSON MIME type when
applicable, that is `application/json`.

## 2.2. Encoding                                {#encoding}

As per recommended in the RFC 4627[^3], all JSON text SHALL be encoded
in Unicode, the default encoding being UTF-8.

[^3]: [RFC 4627](http://www.ietf.org/rfc/rfc4627.txt), The
application/json Media Type for JavaScript Object Notation (JSON)


## 2.3. Top-level structure                     {#top-level-structure}

A loadout in the common loadout format typically looks like:

~~~~~~~~~~
{
    "clf-version": 1,
    "client-version": 384443,
    "metadata": ...,
    "ship": ...,
    "presets": ...,
    "drones": ...
}
~~~~~~~~~~

Each subcategory gets its own section below with a more detailed
specification.

The main object can contain the following keys:

- `clf-version` (REQUIRED), an integer representing the version of the
  format used.

  This document describes the version 1 of the common loadout format;
  see older or newer revisions of this document for other versions of
  the format.

- `client-version` (OPTIONAL), an integer representing the build
  number of the EVE Online client that should be used for this
  loadout.

## 2.4. Additional fields                       {#additional-fields}

Entities MAY add additional fields to the JSON loadouts, but they MUST
prepend `X-` to the key names to avoid namespace conflicts with future
revisions of the format. If the data stored by the entity is only
useful to the entity itself, the key names SHOULD be prefixed by
`X-ApplicationName-` instead (like `X-Pyfa-`, `X-Osmium-`, `X-EFT-`,
etc.).

If such a field has been used to introduce a new JSON object in the
loadout, only the top-level key has to be prefixed. The following
example is correct:

~~~~~~~~~~
{
    "clf-version": 1,
    "X-foo": {
        "bar": ...,
        "baz": ...
    },
    ...
}
~~~~~~~~~~

Prefixing `bar` and `baz` is not necessary as `X-Foo` is already
prefixed.

**Adding extraneous `X-` properties is NOT RECOMMENDED and SHOULD NOT
  be done unless you are aware of the full implications and have
  carefully weighted your decision.**

## 2.5. `metadata` section                      {#metadata-section}

The `metadata` section is OPTIONAL. If present, it must be a JSON
object and can contain any of the following keys:

- `title` (OPTIONAL), a short title for this loadout;

- `description` (OPTIONAL), a description for this loadout, assumed to be plain
  text;

- `creationdate` (OPTIONAL), which is a RFC 2822[^2]-formatted date.

These `metadata` sections are correct:

~~~~~~~~~~
{
    "metadata": {
        "title": "My loadout",
        "description": "This is a possibly long description explaining why my loadout is amazing, and all you need to know in order to get the best of it.",
        "creationdate": "Mon, 11 Jun 2012 09:54:49 +0000"
    },
    ...
}

{
    "metadata": {
        "title": "Another loadout"
    },
    ...
}
~~~~~~~~~~

[^2]: [RFC 2822](http://www.ietf.org/rfc/rfc2822.txt), Internet
Message Format

## 2.6. `ship` section                          {#ship-section}

The `ship` section is REQUIRED.

It must be a JSON object with two keys:

  - `typeid` (REQUIRED), the `TypeID` of the ship;

  - `typename` (OPTIONAL), the name of the ship.

The two following examples are valid `ship` sections:

~~~~~~~~~~
{
    "ship": { "typeid": 587 },
    ...
}

{
    "ship": {
        "typeid": 587,
        "typename": "Rifter"
    },
    ...
}
~~~~~~~~~~

## 2.7. `presets` section                       {#presets-section}

The `presets` section is OPTIONAL.

If it is present it must be a JSON array of presets.

A preset is a JSON object that can contain the keys:

- `presetname` (OPTIONAL), the name of the preset. Preset names SHOULD
  be unique;

- `presetdescription` (OPTIONAL), a longer description of the preset
  (plaintext assumed);

- `modules` (OPTIONAL), a JSON array containing any number of `module`s;

- `implants` (OPTIONAL), a JSON array containing any number of
  `implant`s;

- `boosters` (OPTIONAL), a JSON array containing any number of
  `booster`s;

- `chargepresets` (OPTIONAL), a JSON array of `charge preset`s.

### 2.7.1. `module` element                     {#module-element}

A `module` is a JSON object containing the following keys:

- `typeid` (REQUIRED), the `TypeID` of the fitted module;

- `typename` (OPTIONAL), the name of the fitted module;

- `slottype` (OPTIONAL), a string containing either `high`,
  `medium`, `low`, `rig` or `subsystem`;

- `index` (OPTIONAL), a number indicating the index of the slot this
  module occupies (eg. `0` means the first slot, `1` the second
  slot, etc.);

- `state` (OPTIONAL), a string containing either `offline`,
  `online`, `active` or `overloaded`;

- `charges` (OPTIONAL), a JSON array containing any number of
  `charges`.

### 2.7.2. `charge` element                     {#charge-element}

A `charge` is a JSON object with the following keys:

- `typeid` (REQUIRED), the `TypeID` of the charge;

- `typename` (OPTIONAL), the name of the charge;

- `cpid` (OPTIONAL), the `id` of the charge preset this charge belongs
  to (if unspecified, a value of 0 MUST be assumed);

### 2.7.3. `implant` element                    {#implant-element}

An `implant` element is a JSON object with the following keys:

- `typeid` (REQUIRED), the `TypeID` of the implant;

- `typename` (OPTIONAL), the name of the implant;

- `slot` (OPTIONAL), an integer between 1 and 10 reprenting the slot
  number the implants goes in.

### 2.7.4. `booster` element                    {#booster-element}

A `booster` element is a JSON object with the following keys:

- `typeid` (REQUIRED), the `TypeID` of the booster;

- `typename` (OPTIONAL), the name of the booster;

- `slot` (OPTIONAL), an integer between 1 and 3 reprenting the slot
  number the booster occupies.

### 2.7.5. `charge preset` element              {#charge-preset-element}

A `charge preset` is a JSON object with the following keys:

- `id` (REQUIRED), an integer that uniquely identifies this charge
  preset (in the current preset);

- `name` (REQUIRED), the name of the charge preset. Specified charge
  preset names SHOULD be unique (in the current preset);

- `description` (OPTIONAL), a longer description of this charge preset
  (plaintext assumed).

Here is an example of a `presets` section with multiple presets and
charge presets (only high slots included for brevity):

~~~~
{
        "ship": {"typeid": 24698, "typename": "Drake" },
        "presets": [
            {
                "presetname": "Meta 4 launchers",
                "modules": [
                    { "typeid": 8105, "charges": [{ "typeid": 209 }] },
                    { "typeid": 8105, "charges": [{ "typeid": 209 }] },
                    { "typeid": 8105, "charges": [{ "typeid": 209 }] },
                    { "typeid": 8105, "charges": [{ "typeid": 209 }] },
                    { "typeid": 8105, "charges": [{ "typeid": 209 }] },
                    { "typeid": 8105, "charges": [{ "typeid": 209 }] },
                    { "typeid": 8105, "charges": [{ "typeid": 209 }] }
                ],
                "chargepresets": [
                    { "id": 0, "name": "Scourge missiles" }
                ]
            },
            {
                "presetname": "Tech II launchers",
                "modules": [
                    {
                        "typeid": 2410, 
                        "charges": [
                            { "typeid": 209 },
                            { "typeid": 2629, "cpid": 1 },
                            { "typeid": 24513, "cpid": 2 }
                        ]
                    },
                    {
                        "typeid": 2410, 
                        "charges": [
                            { "typeid": 209 },
                            { "typeid": 2629, "cpid": 1 },
                            { "typeid": 24513, "cpid": 2 }
                        ]
                    },
                    {
                        "typeid": 2410, 
                        "charges": [
                            { "typeid": 209 },
                            { "typeid": 2629, "cpid": 1 },
                            { "typeid": 24513, "cpid": 2 }
                        ]
                    },
                    {
                        "typeid": 2410, 
                        "charges": [
                            { "typeid": 209 },
                            { "typeid": 2629, "cpid": 1 },
                            { "typeid": 24513, "cpid": 2 }
                        ]
                    },
                    {
                        "typeid": 2410, 
                        "charges": [
                            { "typeid": 209 },
                            { "typeid": 2629, "cpid": 1 },
                            { "typeid": 24513, "cpid": 2 }
                        ]
                    },
                    {
                        "typeid": 2410, 
                        "charges": [
                            { "typeid": 209 },
                            { "typeid": 2629, "cpid": 1 },
                            { "typeid": 24513, "cpid": 2 }
                        ]
                    },
                    {
                        "typeid": 2410, 
                        "charges": [
                            { "typeid": 209 },
                            { "typeid": 2629, "cpid": 1 },
                            { "typeid": 24513, "cpid": 2 }
                        ]
                    }
                ],
                "chargepresets": [
                    { "id": 0, "name": "Scourge missiles" },
                    { "id": 1, "name": "Scourge fury missiles" },
                    { "id": 2, "name": "Scourge precision missiles" }
                ]
            }
        ],
        ...
}
~~~~

## 2.8. `drones` section                        {#drones-section}

The `drones` section is optional. If specified, it must be a JSON
array of drone presets.

A drone preset is a JSON object containing the following keys:

- `presetname` (OPTIONAL), the name of the drone preset. Specified
  drone preset names SHOULD be unique;

- `presetdescription` (OPTIONAL), a longer description of the drone
  preset (plaintext assumed);

- `inbay` (OPTIONAL), a JSON array containing any number of `drone`s;

- `inspace` (OPTIONAL), a JSON array containing any number of `drone`s.

### 2.8.1. `drone` element                      {#drone-element}

A `drone` is a JSON object, with the following keys:

- `typeid` (MANDATORY), the `TypeID` of the drone;

- `typename` (OPTIONAL), the typename of the drone;

- `quantity` (MANDATORY), the number of drones considered.

Example of a `drone` section with multiple presets:

~~~~
{
    "ship": { "typeid": 24696 },
    "drones": [
        {
            "presetname": "Small ECM drones",
            "inbay": [
                {
                    "typeid": 23707,
                    "quantity": 5
                }
            ],
            "inspace": [
                {
                    "typeid": 23707,
                    "quantity": 5
                }
            ]
        },
        {
            "presetname": "Medium ECM drones",
            "inspace": [
                {
                    "typeid": 23705,
                    "quantity": 5
                }
            ]
        },
        {
            "presetname": "Combat drones",
            "inbay": [
                {
                    "typeid": 2488,
                    "typename": "Warrior II",
                    "quantity": 5
                }
            ],
            "inspace": [
                {
                    "typeid": 2456,
                    "typename": "Hobgoblin II",
                    "quantity": 5
                }
            ]
        }
    ],
    ...
}
~~~~

# 3. Parsing rules and recommendations          {#parsing-rules}

## 3.1. Duplicates and conflicts                {#duplicates-and-conflicts}

- If two or more presets (resp. charge presets, drone presets) have
  specified the same name and/or `id`, the entity SHOULD show a
  warning message, and MUST discard all but the last preset
  (resp. charge preset, drone preset) appearing in the array.

  ~~~~
  "chargepresets": [
      { "id": 1, "name": "Preset number one" },   <-- Discard this one
      { "id": 2, "name": "Preset number two" },
      { "id": 3, "name": "Preset number one" }    <-- (sic!) Duplicate name
  ]

  "chargepresets": [
      { "id": 1, "name": "Preset number one" },
      { "id": 2, "name": "Preset number two" },   <-- Discard this one
      { "id": 3, "name": "Preset number three" },
      { "id": 2, "name": "Preset number four" }   <-- (sic!) Duplicate id
  ]

  "boosters": [
      { "typeid": 9950 },                         <-- Discarded
      { "typeid": 25349 },                        <-- (sic!) Duplicate slot, discarded
      { "typeid": 15463 }                         <-- (sic!) Duplicate slot
  ]
  ~~~~

- If several modules (resp. implants, boosters) in the same preset
  have the same type and the same index number (resp. slot number),
  then the entity SHOULD display a warning message and MUST only take
  the one appearing last in the array in account.

  ~~~~
  "modules": [
      { "typeid": 2048, "index": 0 },           <-- Discard this one
      { "typeid": 11269, "index": 1 },
      { "typeid": 10858, "index": 0 },          <-- OK, slot type is different (medium)
      { "typeid": 11269, "index": 0 }           <-- (sic!) Duplicate location 
  ]
  ~~~~


- If the same charge preset `id` appears twice or more in a `module`,
  the entity SHOULD show a warning message, and MUST only take the
  last charge to appear in the array into account.

  ~~~~
  "chargepresets": [
      { "id": 1, "name": "Short range" },
      { "id": 2, "name": "Long range" }
  ],
  "modules": [
      { "typeid": 462, "charges": [
          { "typeid": 262, "cpid": 1 },         <-- Discard this one
          { "typeid": 255, "cpid": 2 },
          { "typeid": 21236, "cpid": 1 }        <-- (sic!) Duplicate charge
      ]}
  ]
  ~~~~

- If a drone preset contains two or more `drone`s with the same
  `TypeID`, the entity MUST add the quantities to get the final number
  of drones (valable for both `inbay` and `inspace` drones).

  ~~~~
  "drones": [
      { "inbay": [
          { "typeid": 2488, "quantity": 5 },
          { "typeid": 23705, "quantity": 5 },
          { "typeid": 2488, "quantity": 5 }     <-- OK, there are now 10 Warrior IIs in bay
        ],
        "inspace": [
          { "typeid": 2185, "quantity": 5 }
        ]
      }
  ]
  ~~~~

## 3.2. Overflows                               {#overflows}

- If a drone preset contains more drones than it is possible to fit in
  the ship's drone bay (when counting both `inbay` and `inspace`), the
  entity SHOULD display a warning message. The actual action (discard
  the extraneous drones or not) is left to the entity.

  ~~~~
  "ship": { "typeid": 24696, typename: "Harbinger" },
  "drones": [
      { "inbay": [
          { "typeid": 23705, "quantity": 4 },
        ],
        "inspace": [
          { "typeid": 23705, "quantity": 1 },
          { "typeid": 23707, "quantity": 3 }    <-- (sic!) Extraneous drones (50 m³ bay)
        ]
      }
  ]
  ~~~~

- If a drone preset contains more drones in `inspace` than it is
  possible to have (because the ship doesn't have enough bandwidth,
  for example), the entity SHOULD display a warning message. The
  actual action (whether to discard them or not) is left to the
  entity.

  ~~~~
  "ship": { "typeid": 645 },
  "drones": [
      { "inbay": [
          { "typeid": 28211, "quantity": 5 },
          { "typeid": 28209, "quantity": 5 },
        ],
        "inspace": [
          { "typeid": 2185, "quantity": 5 },
          { "typeid": 2447, "quantity": 5 }     <-- (sic!) Extraneous drones, can only have 5 drones maximum in space
        ]
      }
  ]
  ~~~~

- If the loadout contains more modules than the ship's available
  slots, the entity SHOULD show a warning message. The actual action
  (whether to discard the extraneous modules or not) is left to the
  entity.

  ~~~~
  "ship": { "typeid": 597, "typename": "Punisher" },
  "presets": {
      "modules": [
          { "typeid": 6003 },
          { "typeid": 5439 },
          { "typeid": 4031 }                    <-- (sic!) No more medium slots to fit this into
      ]
  }
  ~~~~

## 3.3. Interpolating data                      {#interpolating-data}

- If the `client-version` key is not present in the root JSON object,
  entities SHOULD assume that the loadout is intended for the current
  version of the EVE Online client.

- If the `presets` section is not present in a loadout, the entity
  MUST assume that no modules and charges are fitted on the ship, and
  no implants and boosters are used.

- If a preset (resp. charge preset, drone preset) does not have a
  `presetname` (resp. `name` for charge presets) specified, the entity
  SHOULD generate acceptable default names for these presets, and
  check that they do not conflict with other specified preset names.

- If a module does not have an `index` specified, the entity MUST
  place the module in any suitable (read: of the same type) unoccupied
  slot. The actual choice (ie, first, last, etc.) decision is left to
  the entity.

- If a module does not have a `state` specified, the entity SHOULD
  assume that by default, modules that can be activated are `active`,
  and other modules are `online`.

- If a charge does not have a speficied charge preset `id` (`cpid`),
  entities MUST assume it is 0. This also counts when handling
  conflicts with two or more `charge`s with the same `cpid`, whether
  it is explicitely set to 0 or not.

## 3.4. Nonsensical data                        {#nonsensical-data}

- If a module whose slot type is `rig` or `subsystem` has a `state`
  specified other than `online`, the entity SHOULD display a warning
  message and MUST use the `online` state instead.

  ~~~~
  "modules": [
      { "typeid": 31790, "state": "online" },     <-- Redundant, but OK
      { "typeid": 31790 },
      { "typeid": 31718, "state": "active" }      <-- (sic!) Invalid state (rigs have no state), assume "online"
  ]
  ~~~~

- If a module (resp. implant, booster) has an incorrect `slottype`
  specified (resp. `slot`), the entity SHOULD show a warning message
  and MUST override the `slottype` (resp. `slot`) with the correct
  value.

  ~~~~
  "modules": [
      { "typeid": 31790, "slottype": "high" }   <-- (sic!) Incorrect slot type, assume "rig"
  ]

  "boosters": [
      { "typeid": 15465, "slot": 3 }            <-- (sic!) Incorrect slot, assume slot 1
  ]
  ~~~~

- If a module has an incorrect `state` specified, the entity SHOULD show a warning message and MUST assume:

  - `online` if the module cannot be activated;
  - `active` if the module can be activated.

  ~~~~
  "modules": [
      { "typeid": 578, "state": "overloaded" },  <-- OK (Adaptive Invulnerability Field II)
      { "typeid": 2048, "state": "overloaded" }, <-- (sic!) Invalid state (Damage Control II), assume "active"
      { "typeid": 1306, "state": "active" }      <-- (sic!) Invalid state (Adaptive Nano Plating), assume "online"
  ]
  ~~~~

- If a module has charges with an incorrect `cpid` (that is, there is
  no `charge preset` whose `id` is the same in the current preset),
  the entity SHOULD show a warning message and SHOULD discard the
  bogus charge.

  ~~~~
  "chargepresets": [
      { "id": 1 }
  ],
  "modules": [
      { "typeid": 462, "charges": [
          { "typeid": 262, "cpid": 1 },
          { "typeid": 255, "cpid": 42 }         <-- (sic!) Discard this charge
      ]}
  ]
  ~~~~



