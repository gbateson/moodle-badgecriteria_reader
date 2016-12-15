<?php

/**
 * Strings for Reader badge critera plugin
 */

$string['criteria_10'] = 'Reading achievment badge';
$string['criteria_10_help'] = 'Allows a badge to be awarded to users who achieve a specified reading goal. Restrictions such as start and end dates, as well as publisher and reading level can also be specified.';

$string['readinggoal'] = 'Reading goal';
$string['minreadinggoal'] = 'Minimum reading goal';
$string['minreadinggoal_help'] = 'The minimum number of words that must be read in order to earn this badge.';
$string['maxreadinggoal'] = 'Maximum reading goal';
$string['maxreadinggoal_help'] = 'The maximum number of words that can be read in order to earn this badge. Note that the badge will NOT be awarded, if the number of words exceeds this amount.';

$string['absolutetimestart'] = 'Absolute start time';
$string['absolutetimestart_help'] = 'The date and time at which the reading period starts.';
$string['absolutetimeend'] = 'Absolute end time';
$string['absolutetimeend_help'] = 'The date and time at which the reading period ends.';
$string['relativetimestart'] = 'Relative start time';
$string['relativetimestart_help'] = 'The time after enrolment at which a user\'s reading period starts.';
$string['relativetimeend'] = 'Relative end time';
$string['relativetimeend_help'] = 'The time after enrolment at which a user\'s reading period ends.';

$string['enrolmenttype'] = 'Enrolment type';
$string['enrolmenttype_help'] = 'The type of enrolment to which the relative start and end times relate.';
$string['siteenrolment'] = 'Site enrolment';
$string['courseenrolment'] = 'Course enrolment';

$string['minwordcount'] = 'Minimum word count';
$string['minwordcount_help'] = 'The minimum number of words a book must have in order to contribute to the reading total.';
$string['maxwordcount'] = 'Maximum word count';
$string['maxwordcount_help'] = 'The maximum number of words a book can have in order to contribute to the reading total. Note that books with more than this number of words will NOT contribute to the reading total.';

$string['anydifficulty'] = 'Any level';
$string['shortdifficulty'] = 'RL {$a}';
$string['difficulties'] = 'Reading level';
$string['difficulties_help'] = 'If any values are selected here, the reading goal will be limited to books of the selected reading levels. If no individual reading levels are selected, books of ALL reading levels will contribute to the reading total.';
$string['anypublisher'] = 'Any publisher';
$string['publishers'] = 'Publishers';
$string['publishers_help'] = 'If any values are selected here, the reading goal will be limited to books by the selected publishers. If no individual publishers are selected, books by ALL publishers will contribute to the reading total.';
$string['genres'] = 'Genres';
$string['genres_help'] = 'If any values are selected here, the reading goal will be limited to books of the selected genres. If no individual genres are selected, books of ALL genres will contribute to the reading total.';

$string['activityfilters'] = 'Activity name filters';
$string['bookfilters'] = 'Book filters';
$string['categoryfilters'] = 'Course category filters';
$string['coursefilters'] = 'Course name filters';
$string['usernamefilters'] = 'Username filters';

$string['include'] = 'Include';
$string['include_help'] = 'Only items with a name that matches this pattern will be __included__.';
$string['exclude'] = 'Exclude';
$string['exclude_help'] = 'Any items with a name that matches this pattern will be __excluded__.';