# How to use this script
First of all use composer to install all dependencies:
```
$ composer install
```
Use the commandline php to run the script:
````
Usage: $ php run.php [-p path, --path path (default: /var/www/ilias)]

Optional Arguments:
	-p path, --path path (default: /var/www/ilias)
		base Path of the ILIAS-Installation
````
You need PHP7 to run this script.

### What does it?
This reads/generates a maintenance.json in every subdirectory of /Services, /Modules and /src of the ILIAS. 
Those files are structures like this:
```
{
    "maintenance_model": "Classic", // Classic or Service 
    "first_maintainer": "fschmid(21087)", // Username of ILIAS.de with usr_id in brackets
    "second_maintainer": "", // Same format as first_maintainer
    "implicit_maintainers": [
        "mstuder(8473)" // Same format as first_maintainer
    ],
    "coordinator": "", // Same format as first_maintainer
    "tester": "", // Same format as first_maintainer
    "testcase_writer": "", // Same format as first_maintainer
    "path": "Modules/Bibliographic", // Will be generated automatically
    "belong_to_component": "Bibliographic List Item"
    "used_in_components": [
        "FileDelivery" // List every Component which uses ode from this directory. If nearly the whole codebase uses it, write "All". If nobody uses it write "None"
    ]
}
```
After running the files
- maintainers.json  
- maintainers.md  
- components.json  

will be updated.  

### Configuration
- The components.json has to be filled out manually with the information which Maintainer is first and so on.
- You can update the maintainers.json with changes usr_ids. 