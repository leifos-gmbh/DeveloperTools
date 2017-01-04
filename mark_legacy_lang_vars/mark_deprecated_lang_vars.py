__author__ = 'xus'
import fileinput
import sys
import csv
import os.path

if len(sys.argv) > 1:
    print ("\n** Updating language file passed as an argument."+sys.argv[1])
    LANG_FILE = '../../../../lang/'+sys.argv[1]

else:
    print "\n** Updating the current english file => ilias_en.lang"
    LANG_FILE = '../../../../lang/ilias_en.lang'

print "Please, wait..."

CSV_DEPRECATEDS = 'lang_deprecated_leg.csv'

if not os.path.isfile(LANG_FILE):
    sys.exit("Error: Lang File not found => "+LANG_FILE)

if not os.path.isfile(CSV_DEPRECATEDS):
    sys.exit("Error: File with deprecated values is not found => "+CSV_DEPRECATEDS)

with open(CSV_DEPRECATEDS, 'rb') as f:
    reader = csv.reader(f)
    for row in reader:
        string_to_search = row[0]+"#:#"+row[1]+"#:#"
        fh = fileinput.input(LANG_FILE, inplace=True)
        for line in fh:
            if string_to_search in line:
                line = line.replace('\n', '')
                sys.stdout.write(line + "###deprecated \n")
            else:
                sys.stdout.write(line)
        fh.close()
print "Done."