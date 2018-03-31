=======================
moodle-badgecriteria_reader
=======================

Moodle >= 2.3 badge criteria for Reading achievements in the Moodle Reader module.

=======================
Requirements
=======================

(1) Moodle >= 2.3
(2) Moodle Reader module

=======================
Installation
=======================

To fetch the plugin using GIT ...

    ## --------------------------------------------------
    ## change to Moodle scripts folder
    ## --------------------------------------------------
    cd /PATH/TO/MOODLE

    ## --------------------------------------------------
    ## GIT clone into "badges/criteria/reader"
    ## --------------------------------------------------
    git clone -q https://github.com/gbateson/moodle-badgecriteria_reader.git badges/criteria/reader

To fetch the plugin using ZIP ...

    ## --------------------------------------------------
    ## navigate to Moodle scripts dir
    ## Note: you will need to change this path
    ##       to what is required on your server
    ## --------------------------------------------------
    ##
    cd /PATH/TO/MOODLE

    ## --------------------------------------------------
    ## navigate to "badges/criteria" folder
    ## --------------------------------------------------
    ##
    cd badges/criteria

    ## --------------------------------------------------
    ## fetch zip file from github.com
    ## Note: if you do not have wget,
    ##       you may be able to use the "curl" command
    ##       or download the zip file to your PC and
    ##       then use FTP to upload it to the server
    ## --------------------------------------------------
    ##
    wget --no-check-certificate -O master.zip https://github.com/gbateson/moodle-badgecriteria_reader/archive/master.zip

    ## --------------------------------------------------
    ## unzip the zip file
    ## --------------------------------------------------
    ##
    unzip master.zip

    ## --------------------------------------------------
    ## rename the unzipped dir
    ## --------------------------------------------------
    ##
    mv 'moodle-badgecriteria_reader-master' reader

    ## --------------------------------------------------
    ## remove the zip file
    ## --------------------------------------------------
    ##
    rm -f master.zip

To complete the installation

    ## --------------------------------------------------
    ## copy the main file into the badge criteria folder
    ## --------------------------------------------------
    ##
    cp -f badges/criteria/reader/award_criteria_reader.php \
          badges/criteria/award_criteria_reader.php

    ## --------------------------------------------------
    ## define insertion strings
    ## --------------------------------------------------
    ##
    BADGE_INSERT_1="\\
\\
\$1\/\\*\\
\$1 \\* Reader badge criteria type\\
\$1 \\* Criteria type constant, primarily for storing criteria type in the database\\.\\
\$1 \\*\/\\
\$1define('BADGE_CRITERIA_TYPE_READER', 10); \/\/ NEW LINE"
    BADGE_INSERT_1="s/( *)define\\('BADGE_CRITERIA_TYPE_PROFILE', 6\\);/\$&$BADGE_INSERT_1/s"

    BADGE_INSERT_2=",\\
\$1BADGE_CRITERIA_TYPE_READER    => 'reader', \/\/ NEW LINE"
    BADGE_INSERT_2="s/( *)BADGE_CRITERIA_TYPE_PROFILE   => 'profile'/\$&$BADGE_INSERT_2/s"

    BADGE_INSERT_3="\\
\$1BADGE_CRITERIA_TYPE_READER, \/\/ NEW LINE"
    BADGE_INSERT_3="s/( *)BADGE_CRITERIA_TYPE_OVERALL,/\$&$BADGE_INSERT_3/sg"

    ## --------------------------------------------------
    ## add the CONSTANTS for the new criteria type
    ## --------------------------------------------------
    ##
    FILE='/PATH/TO/MOODLE/badges/criteria'
    cp "$FILE" "$FILE.old"
    perl -0777 -p -i -e "$BADGE_INSERT_1" "$FILE"
    perl -0777 -p -i -e "$BADGE_INSERT_2" "$FILE"

    ## --------------------------------------------------
    ## add awareness of the new criteria to "lib/badgeslib.php"
    ## --------------------------------------------------
    ##
    FILE='/PATH/TO/MOODLE/lib/badgeslib.php'
    cp "$FILE" "$FILE.old"
    perl -0777 -p -i -e "$BADGE_INSERT_3" "$FILE"

    ## --------------------------------------------------
    ## add the strings for the new criteria type
    ## --------------------------------------------------
    ##
    FILE='/PATH/TO/MOODLE/lang/en/badges.php'
    cp "$FILE" "$FILE.old"
    perl -0777 -p -i -e 's/\?>\s*$//s' "$FILE"
    perl -p -e 's/<\?php//' '/PATH/TO/MOODLE/badges/criteria/reader/lang/en/award_criteria_reader.php' >> "$FILE"

    ## --------------------------------------------------
    ## login to Moodle as admin and purge caches
    ## to complete the installation
    ## --------------------------------------------------

=======================
Usage
=======================

(1) login to your Moodle site as administrator or teacher
(2) navigate to: Administration -> Badges -> Manage badges
(3) To add a new badge, click the "Add a badge" button
(4) To edit a badge, click the edit icon for the badge you wish to edit

=======================
Acknowledgements
=======================

Many thanks to Moodle Association of Japan for sponsoring 
the design and programming of this plugin.