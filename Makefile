# Dump files in chronological order
ALLDUMPS=inc100 inc101 inc14 inc15 inca10 cru10b cru110 cru15 cru16 esc10 inf10 inf11 inf12 inf13 ret10 ret11 ret12 ody10 ody11 rub10 rub11 rub13 rub14

# Latest dump file
LATESTDUMP=$(lastword $(ALLDUMPS))

# Path where the dump files are located, with the trailing /
DUMPPATH=~/Documents/EVE/dumps/

ALLFDUMPS=$(foreach D, $(addsuffix -*.db, $(addprefix $(DUMPPATH), $(ALLDUMPS))), $(wildcard $(D)))
LATESTFDUMP=$(wildcard $(addsuffix -*.db, $(addprefix $(DUMPPATH), $(LATESTDUMP))))
GENERATED=$(addprefix helpers/, modulecharges.json moduleslottypes.json typenames.json typetypes.json)

all: $(GENERATED)

helpers/modulecharges.json:
	./tools/make_modulecharges_json $(LATESTFDUMP) > $@.temp || (rm $@.temp && exit 1)
	mv $@.temp $@

helpers/moduleslottypes.json:
	./tools/make_moduleslottypes_json $(LATESTFDUMP) > $@.temp || (rm $@.temp && exit 1)
	mv $@.temp $@

helpers/typenames.json:
	./tools/make_typenames_json $(ALLFDUMPS) > $@.temp || (rm $@.temp && exit 1)
	mv $@.temp $@

helpers/typetypes.json:
	./tools/make_typetypes_json $(LATESTFDUMP) > $@.temp || (rm $@.temp && exit 1)
	mv $@.temp $@

clean:
	rm -f $(GENERATED)

.PHONY: all clean
