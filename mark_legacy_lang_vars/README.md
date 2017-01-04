##Mark as deprecated all the variables that has a corresponding entry in the deprecated CSV file

Before execute the script, is highly recommended to duplicate the language file that is wanted to modify
 
This script takes a language file and adds a comment "###deprecated" at the end of each line that has a corresponding entry in
the "lang_deprecated_leg.csv" file. This "lang_deprecated_leg.csv" file must be placed in the same directory as this script.
(Customizing/global/tools/mark_legacy_lang_vars/)

- Language file name can be passed as an argument. The script will check if this file exists inside the "/lang" directory

          python mark_deprecated_lang_vars.py ilias.en.lang


- This script can be executed also without passing the language file as an argument. In this case the current "/lang/ilias.en.lang" file will be modified.

          python mark_deprecated_lang_vars.py

Update language files can take more than one minute, don't interrupt the process.

