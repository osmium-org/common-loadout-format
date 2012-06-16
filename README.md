# Common Loadout Format

## Abstract

*(Extracted from the specification)*

The common loadout format (CLF) is a JSON based format for defining a
fitting in the EVE Online multiplayer game. The format strives to
achieve portability, completeness, extendability and longevity.

## Licensing

Unless otherwise specified, all documents and source files are
licensed under the WTFPL license, version 2. See the `COPYING` file
for the full license text.

## Validator guidelines

If you want to write a reference validator in your favorite language,
please read (and apply) the following:

- Your validator should read the JSON loadout from standard input,
  spit its errors in the standard error stream (`stderr`) and have a
  return value of `0` if the validation succeeded without warnings,
  `1` if the validation succeeded with some warnings, or anything else
  if the validation failed.

- The binary/executable script should be called `validator` and should
  be callable without any arguments. If it requires compiling, use a
  `Makefile` rule to invoke the compiler.

- Assume nothing. This is a reference implementation, so being
  pedantic is crucial. Use and abuse the helper JSON files. Cover all
  the possible cases.

- The source is more likely to be read than used; write the most
  beautiful code you can, and comment anything you find worthy of an
  explanation.
