Moodle Language Validation
=========================

This is a developer tool that will prints all language strings from a given component on one screen to allow HTML validation and run extra checks for case (e.g. sentence case as used by most of Moodle). This is intended to be used when additional editorial rules must be checked for in proof reading developments.

##Installation

1. Clone files to /your/moodle/local/validatelang
2. Run installation via Site Adminsitration -> Notifications

##Use

1. Checkout the language strings via Site Administration -> Language -> Language Customisation
2. Edit local/validatelang/index.php to use the desired file
3. Go to http://your/moodle/local/validatelang/
4. Select the component and rule and submit (this will list all strings and an error if it doesn't match rule - this may be intentional)
5. Use W3C local validation on the source code to check any HTML tags are correct

NB: Remember to re-checkout the strings and clear cache after making changes before re-validating.

##Features

* Check all strings conform to sentence case (ignoring English first-person pronoun, words all in capitals, HTML tags)
* Treats block level HTML tags as paragraphs so that next letter should be capitalised regardless of punctuation
* Displays all strings for a component as rendered HTML for validation based on them DTD
* Does not alter original text so editor can make a decision on changes

##Possible improvements

* Extend navigation to appear under language settings
* Provide options for different case/grammar analysis (e.g. title case)
* Provide auto W3C validation of strings
* Case match should use unicode and not ascii matching
* New lines/paragraphs should be in capitals in sentence case
* Dealing with abbreviations such as e.g. or incl. which are not followed by capital
* Highlight errors (not a direct result of strcmp so needs assessment)

##Limitations

* Anglo-centric (though it is possible to filter checks to be language specific)
* Proper nouns would require dictionary to check against these (probably best left as flagged for editor's discretion)
