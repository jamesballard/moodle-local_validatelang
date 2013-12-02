Moodle Language Validation
=========================

This is a developer tool that will prints all language strings from a given component on one screen to allow HTML validation and run extra checks for case (e.g. sentence case as used by most of Moodle).

##Use

1. Checkout the language strings via Site Administration -> Language -> Language Customisation
2. Edit local/validatelang/index.php to use the desired file
3. Go to http://your/moodle/local/validatelang/

##Limitations/To-do

1. Make component selectable
2. Extend navigation to appear under language settings
3. Provide options for different case/grammar analysis
4. Provide auto W3C validation of strings
