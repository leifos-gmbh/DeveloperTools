__author__ = 'xus'
import fileinput
import sys
import os.path

#ilias.en.lang as an argument
if len(sys.argv) is not 2:
    sys.exit("Error: Invalid number of arguments, full path for ilias_en.lang is required.")

english_file = sys.argv[1]
language_directory = os.path.dirname(english_file)+"/"
separator = "#:#"

if not os.path.isfile(english_file):
    sys.exit("Error: English language file not found => " + english_file)

'''
Step 1: parse ilias_en.lang and save the deprecated identifiers in list_deprecateds
'''
list_deprecateds = list()
with open(english_file) as r:
    for line in r:
        if "###deprecated" in line:
            parts = line.split(separator)
            list_deprecateds.append(parts[0]+separator+parts[1])

'''
Step 2: get all language files (not setup files) and save it in list_lang_files
'''
full_list = os.listdir(language_directory)

list_lang_files = list()
for element in full_list:
    temp_file = language_directory + os.sep + element
    if os.path.isfile(temp_file):
        file_parts = element.split("_")
        if file_parts[0] == 'ilias' and file_parts[1] != 'en.lang':
            list_lang_files.append(element)

'''
Step 3: process the files
    substep A:
    IF the line does not contain a "###deprecated" comment, but the entry appears in the array above,
    ADD the comment to the line. Please note that other comments may exist like "###07 02 2017 new variable".
    In this case these original comments need to be removed.

    substep B:
    IF the line does contain a "###deprecated" comment, but does not appear in the array above,
    REMOVE the comment from the line.
'''
for lang_file in list_lang_files:
    lang_file_path = language_directory + lang_file
    fi = fileinput.input(lang_file_path, inplace=True)
    for line in fi:
        line_parts = line.split(separator)
        if len(line_parts) > 2:
            module = line_parts[0]
            lang_id = line_parts[1]
            str_value = line_parts[2]
            #substep A
            if module + separator + lang_id in list_deprecateds:
                if "###" in str_value and "###deprecated" not in str_value:
                    string_parts = str_value.split("###")
                    str_value = string_parts[0] + "###deprecated"
                    sys.stdout.write(module + separator + lang_id + separator + str_value + "\n")
                elif "###deprecated" not in str_value:
                    str_value = str_value.replace('\n', '')
                    sys.stdout.write(module + separator + lang_id + separator + str_value + "###deprecated \n")
                else:
                    sys.stdout.write(line)
            #substep B
            elif module + separator + lang_id not in list_deprecateds and ("###deprecated" in str_value):
                string_parts = str_value.split("###")
                str_value = string_parts[0]
                sys.stdout.write(module + separator + lang_id + separator + str_value + "\n")
            else:
                sys.stdout.write(line)
        else:
            sys.stdout.write(line)
    fi.close()
