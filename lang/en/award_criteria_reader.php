<?php

/**
 * Strings for Reader badge critera plugin
 */

$string['criteria_10'] = 'Reading achievement';
$string['criteria_10_help'] = 'This criteria allows a badge to be awarded to users who achieve a specified reading goal. Restrictions on start and end dates, as well as on publisher and reading level can also be specified.';

$string['criteria_descr_short10'] = 'Reached <strong>{$a}</strong> of: ';
$string['criteria_descr_single_short10'] = 'Reading achievement: ';
$string['criteria_descr_single_10'] = 'The following Reading goal has been reached:';
$string['criteria_descr_10'] = '<strong>{$a}</strong> of the following Reading goal has yet to be reached:';

$string['readinggoal_min'] = 'Minimum reading goal';
$string['readinggoal_min_help'] = 'The minimum number of words that must be read in order to earn this badge.';
$string['readinggoal_max'] = 'Maximum reading goal';
$string['readinggoal_max_help'] = 'The maximum number of words that can be read in order to earn this badge. Note that the badge will NOT be awarded, if the number of words exceeds this amount.';

$string['absolutetime_start'] = 'Absolute start time';
$string['absolutetime_start_help'] = 'The date and time at which the reading period starts.';
$string['absolutetime_end'] = 'Absolute end time';
$string['absolutetime_end_help'] = 'The date and time at which the reading period ends.';
$string['relativetime_start'] = 'Relative start time';
$string['relativetime_start_help'] = 'The time after enrolment at which a user\'s reading period starts.';
$string['relativetime_end'] = 'Relative end time';
$string['relativetime_end_help'] = 'The time after enrolment at which a user\'s reading period ends.';

$string['enrolmenttype'] = 'Enrolment type';
$string['enrolmenttype_help'] = 'The type of enrolment to which the relative start and end times relate.';
$string['enrolmenttype_site'] = 'Site enrolment';
$string['enrolmenttype_course'] = 'Course enrolment';

$string['wordcount_min'] = 'Minimum word count';
$string['wordcount_min_help'] = 'The minimum number of words a book must have in order to contribute to the reading total.';
$string['wordcount_max'] = 'Maximum word count';
$string['wordcount_max_help'] = 'The maximum number of words a book can have in order to contribute to the reading total. Note that books with more than this number of words will NOT contribute to the reading total.';

$string['difficulty_any'] = 'Any level';
$string['difficulty_short'] = 'RL {$a}';
$string['difficulties'] = 'Reading level';
$string['difficulties_help'] = 'If any values are selected here, the reading goal will be limited to books of the selected reading levels. If no individual reading levels are selected, books of ALL reading levels will contribute to the reading total.';
$string['publisher_any'] = 'Any publisher';
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