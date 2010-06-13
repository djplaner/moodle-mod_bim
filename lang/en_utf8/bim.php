<?php

$string['bim'] = 'BIM';

$string['modulename'] = 'BIM';
$string['modulenameplural'] = 'BIMs';

$string['group_allocation'] = 'Marker Allocations';
$string['marking'] = 'Marking data';
$string['student_feeds'] = 'Student feeds';

/*
 * Configuration FORM
 */

// General settings
$string['bimfieldset'] = 'BIM settings';
$string['bimintro'] = 'About BIM activity';
$string['bimname'] = 'Name of BIM activity';

$string['bim_submit'] = 'Submit';
$string['bim_cancel'] = 'Cancel';

$string['bim_error_updating'] = 'Error updating database';

// BIM specific settings

$string['bim_register_feed'] = 'Allow registration?';
$string['bim_mirror_feed'] = 'Enable mirroring?';
$string['bim_grade_feed'] = 'Enable grading?';
// change not implemented at the moment
//$string['bim_change_feed'] = 'Enable change of feed?';

// capabilities

$string['bim:marker'] = 'Teacher view of student details';
$string['bim:student'] = 'Student change/view details';
$string['bim:coordinator'] = 'Admin do just about anything';

// student/register_form.php
$string['bim_please_register_heading'] = 'Please register your blog';

$string['bim_please_register_description'] = '<p>Copy the URL of your blog/feed into the box below and hit the \"Register your blog\" button. At this stage BIM will:</p><ol><li>Check your URL for any problems.<p>If there are any problems it will tell you what they are and ask you to register the correct URL.</p></li><li>Make a copy (mirror) any existing posts on your blog into this system.<p><strong>Warning:</strong> This may take a little while, please be patient.</p></li><li>Display details of what it found.<p>This is how you can check what BIM knows about your blog. Once you register your blog, BIM will only ever show  you the details.</p></li></ol>';

$string['bim_register'] = 'Register your blog';
//*****************
// used in coordinator/allocate_markers.php
// bim_allocate_markers
$string['bim_allocate_marker_heading'] = "Allocate markers to groups";
$string['bim_allocate_marker_description'] = 
       '<p>Allocate each staff member responsibility for 0 or more groups. '.
          'The staff member will only be able to view progress and mark ' .
          'those students who belong to these groups.</p>' .
  '<p>To add groups or staff, user the normal Moodle processes for assigning roles and managing groups for this course.</p>';
$string['bim_allocate_marker_nogroups_heading'] = 'Can not allocate markers';
$string['bim_allocate_marker_nogroups_description'] = '<p>Unable to find any groups for this course.  Marker allocation is only possible with existing course groups.</p><p>Course groups need to be created using Moodle\'s groups facility.</p>';
// process_markers_form
$string['bim_group_allocations_heading'] = "Updating group allocations";
$string['bim_group_allocations_added'] = 
       '<li>Added group $a->group for $a->marker </li>';
$string['bim_group_allocations_removed'] = 
       '<li>Removed group $a->group for $a->marker </li>';
$string['bim_group_allocations_none'] = 
       '<p>Unable to find or make any change to marker allocations.</p>';

//*****************
// used in coordinator/find_student.php
// bim_find_students
$string['bim_find_again_heading'] = "Search again?";
$string['bim_find_again_description'] = '<p>The details of the student you selected appears below.</p>';
$string['bim_find_heading'] = 'Find student';
$string['bim_find_description'] = '<p>View details of a specific student by entering all or part of the student\'s name, username or email address. Some examples:</p> <ul> <li> <strong>joe bloggs</strong> - will find all students with \"joe bloggs\" as their name. </li><li> <strong>j.bloggs</strong> - find all students with \"j.bloggs\" in their name, username or email adress. </li> <li> <strong>j</strong> find all students with \"j\" in their name, username or email address.</li><li> <strong>%%</strong> will match all students.</li></ul><p> <strong>NOTE:</strong> Only search strings that return less than 200 matches will work. This search is likely to be much more. </p>';
// find_student_form.php
$string['bim_find_text'] = 'Search for student:';
// bim_process_find_student
$string['bim_find_none_heading'] = 'No matches found';
$string['bim_find_none_description'] = '<p>Unable to find any students with a name, username or email address that contained <blockquote><strong>$a</strong></blockquote>Please try another search. </p>';
$string['bim_find_one_heading'] = 'One student found';
$string['bim_find_one_description'] = '<p>Your search for<blockquote><strong>$a</strong></blockquote>matched one student.  That student\'s details follow after the search form.</p>';
$string['bim_find_x_heading'] = '$a students found';
$string['bim_find_x_description'] = '<p>Your search for<blockquote><strong>$a->search</strong></blockquote>matched $a->count students.  A list of matching students is shown below. Click on the \"details\" column to view more detail about that student.</p>';
$string['bim_find_student_details_heading'] = 'Student details';
$string['bim_find_too_many'] = '<p>Your search for <blockquote><strong>$a->search</strong></blockquote>matched $a->count students.  This is too many (from a system resources perspective) to display.  Please refine your search and try again.</p>';

//*****************
// coordinator/manage_marking.php
// bim_manaage_marking
$string['bim_marking_no_markers_heading'] = 'No markers allocated';
$string['bim_marking_no_markers_description'] = '<p>There are no markers with students allocated to mark. This means it is impossible to view marking progress.</p><p>To view marking progress you will need to use <strong>Allocate Markers</strong> to allocate markers.</p>';

$string['bim_marking_to_do'] = '<p>The following table gives an overview of marking progress for the different markers for this BIM.  There are two main tasks you can perform from here:</p><ul> <li> release marked posts so that students can see marker comments and marks; and<br /> (Only possible when there are <em>Marked</em> posts) </li><li> drill down to see more detail on groups of students.</li></ul>';
$string['bim_marking_heading'] = 'Manage marking';
$string['bim_marking_no_questions'] = '<p>There are currently no questions defined for this activity. This means that no marking can be done.  Use the \"Manage Questions\" tab above to add questions.</p>';
$string['bim_marking_unregistered'] = '<p><strong><a href=\"#unreg\">Unregistered students</a>:</strong> There are $a student(s) who have not registered their feeds.</p>';
$string['bim_marking_release'] = 'Release all $a marked post(s)';
// binm_manage_release
$string['bim_release_heading'] = 'Release marking';
$string['bim_release_success'] = '<p>Successfully released marked posts.</p>';
$string['bim_release_errors'] = '<p>Errors encountered while releasing results.</p>';
$string['bim_release_return'] = '<p>Return to <a href=\"$a\">manage marking</a></p>';
// bim_manage_view
$string['bim_release_manage_header'] = 'View details';
$string['bim_release_manage_view'] = '<p>Viewing details of $a->match students (<a href=\"#registered\">$a->registered registered</a> and <a href=\"#unregistered\">$a->unregistered unregistered</a>)';
$string['bim_release_manage_any'] = ' with any posts.</p>';
$string['bim_release_manage_criteria'] = ' with posts matching these criteria:</p><ul>';
$string['bim_release_manage_status'] = '<li> status equal to <strong>$a</strong></li>';
$string['bim_release_manage_marker'] = '<li> <strong>$a</strong> as marker.</li>';
$string['bim_release_manage_response'] = '<li> in response to the question <strong>$a</strong>.</li>';
$string['bim_release_manage_registered_heading'] = 'Registered student details';
$string['bim_release_manage_registered_description'] = '<p>$a student(s) who have registered with BIM, match the criteria.</p>';
$string['bim_release_manage_unregistered_heading'] = 'Unregistered student details';
$string['bim_release_manage_unregistered_description'] = '<p>$a student(s) who have <strong>not</strong> registered with BIM, match the criteria.</p>';
$string['bim_release_no_students_heading'] = 'No students allocated to you';
$string['bim_release_no_students_description'] = '<p>You have not yet been allocated any groups of students to mark for this activity.</p><p>Only the coordinator can allocate groups for you to mark.</p>';
//************
// coordinator/view.php
// bim_configuration_screen
$string['bim_configuration_screen'] = '<p>The general configuration of BIM is done using the <a href=\"$a->wwwroot/course/modedit.php?update=$a->cmid&return=1\">standard activity configure interface</a></p><p>Some basic advice on the steps to configure a BIM activity are <a href=\"#steps\">provided below</a>.</p>';
$string['bim_configuration_details'] = 'Current configuration settings';
$string['bim_configuration_name'] = 'BIM name:';
$string['bim_configuration_intro'] = 'About:';
$string['bim_configuration_registration'] = 'Can students register?:';
$string['bim_configuration_mirror'] = 'Are posts being mirrored?:';
$string['bim_configuration_grade'] = 'Are results added to gradebook?:';
$string['bim_configuration_settings'] = 'Settings';
$string['bim_configuration_values'] = 'Current Values';
$string['bim_configuration_no_register'] = "<p><strong>Important:</strong> students cannot currently register their feed.</p>";
$string['bim_configuration_no_mirror'] = "<p><strong>Important:</strong> BIM is not currently copying/mirroring student posts to the local system.  No new student posts will enter the system.</p>";
$string['bim_configuration_steps_heading'] = 'General steps for configuring a BIM activity';
$string['bim_configuration_steps_description'] = '<p>Configuring a BIM activity would normally include these steps:</p><ol><li>Configure BIM;<br />Provide the title and descripton of the activity and set whether the activity is being graded, mirrored or students allowed to registere.</li><li>Manage questions;<br />Create a list of questions students are expected to answer. With mirroring on BIM will try to automatically allocate student posts to questions.</li><li>Allocate markers;<br />Specify which groups of students each marker is responsible for marking.</li></ol>';
// bim_manage_questions
$string['bim_questions_current'] = '<p>There are currently $a questions for this BIM activity. The following allows you to add a new question or modify existing questions.</p><p>When you add a new question, if students have already made posts, then BIM will attempt to allocate those existing posts to the new question when it runs its automated mirror process. Modifying questions to which students have already provided answers should work, but the advice is not to.</p>';
$string['bim_questions_none_heading'] = 'No current questions';
$string['bim_questions_none_description'] = '<p>There are currently no questions for this activity. BIM will still operate, students can register blogs and staff can track student blogs.  However, there will be no question allocation or tracking of progress by question.</p><p>Use the \"Add new question\" form below to start adding questions. Once questions are added you will be able to modify and delete them.</p><p>BIM will automatically check any existing students posts within BIM against new questions. This usually takes an hour or so after you have added the question.</p>';
$string['bim_questions_changes_heading'] = 'Changes being made include..';
$string['bim_questions_adding'] = '<p>Adding a new question with the title <strong>$a</strong></p>';
$string['bim_questions_error_insert'] = 'Error inserting new question';
$string['bim_questions_error_delete'] = '<p><strong>ERROR</strong> unable to delete question with title <strong>$a</strong>.</p>';
$string['bim_questions_deleting'] = '<p>Deleting question with title: <strong>$a</strong>.</p>';
$string['bim_questions_changing'] = '<p>Changing $a question(s):</p><ul>';
$string['bim_questions_error_changing_title'] = 'ERROR updating database';
$string['bim_questions_nochanges'] = '<p>No changes were made.</p>';
$string['bim_questions_error_processing'] = 'Problem process the question form.';

// coordinator/question_form.php
$string['bim_qform_addnew'] = 'Add new question';
$string['bim_qform_title'] = 'Title:';
$string['bim_qform_min'] = 'Min mark:';
$string['bim_qform_max'] = 'Max mark:';
$string['bim_qform_question'] = 'Question: $a';
$string['bim_qform_stats'] = '<p>Student answers: $a</p>';
$string['bim_qform_delete'] = 'Delete question?';

//**********
// lib/bim_rss.php
// bim_process_feed
$string['bim_process_feed_error'] = 'bim_process_feed: inserting bim_marking $a';
// bim_is_item_allocated
$string['bim_item_allocated_not'] = 'Not allocated to a question';
$string['bim_item_allocated_released'] = 'Allocated to the question <strong>$a->title</strong><br />Marked: $a->mark out of $a->max';
$string['bim_item_allocated_allocated'] = 'Allocated to the question <strong>$a</strong><br />(not marked yet)';
$string['bim_item_allocated_marked'] = 'Allocated to the question <strong>$a</strong><br />(marked but not released)';
// bim_display_error
// bim_display_error
$string['bim_register_invalid_url_heading'] = 'Not a valid url';
$string['bim_register_invalid_url_description'] = '<p>The URL you provided <strong>$a</strong> is not a valid URL.</p><p>A valid URL will typically look something like this - <strong>http://davidtjones.wordpress.com/</strong>. For more information about what a URL is, please read <a href=\"http://www.utoronto.ca/web/HTMLdocs/NewHTML/url.html\">this page</a>.</p>';
$string['bim_register_no_retrieve_heading'] = 'Could not access the URL';
$string['bim_register_no_retrieve_description'] = '<p>Unable to access the URL you provided <blockquote><strong>$a->url</strong></blockquote>The error created was <blockquote><strong>$a->error</strong></blockquote> ';
$string['bim_register_nolinks_heading'] = 'Could not find any feeds';
$string['bim_register_nolinks_description'] = '<p>Unable to find any feeds from the URL you provided via auto-discovery.  Would appear that there are no feeds. </p><p>If you know the URL for the feed, please try registering it.</p><p>For more information about feeds (aka Web feed) see the <a href=\"http://en.wikipedia.org/wiki/Web_feed\">wikipedia page</a>. Normally a blog home page will include a link to a feed.  This error suggests BIM cannot find the feed.</p>';

//***********
// lib/locallib.php
// bim_print_header
$string['bim_header_details'] = 'Your details for ';
$string['bim_header_student_details'] = 'View student details';
$string['bim_header_post_details'] = 'Mark posts';
$string['bim_header_allocate'] = 'Allocate posts for a student';
$string['bim_header_changeblog'] = 'Change student\'s blog';
$string['bim_header_mark'] = 'Mark post';
// bim_build_coordinator_tabs
$string['bim_tabs_config'] = 'Configure BIM';
$string['bim_tabs_questions'] = 'Manage Questions';
$string['bim_tabs_markers'] = 'Allocate Markers';
$string['bim_tabs_manage'] = 'Manage Marking';
$string['bim_tabs_find'] = 'Find Student';
$string['bim_tabs_details'] = 'Your Students';

// bim_show_questions
$string['show_qs_heading'] = 'Current questions';
$string['show_qs_description'] = '<p>This activity currently has $a questions as shown in the following table.</p><p><strong>IMPORTANT:</strong> when you answer a question with a post, please make sure that the title of your post contains the title of the question.<br />For example, if the question title is <strong>Week 12</strong> make sure you post\'s title contains <strong>Week 12</strong></p>';
$string['show_qs_title'] = 'Title';
$string['show_qs_body'] = 'Description';

//**************
// marker/view.php
// bim_marker_allocate_posts
$string['bim_marker_student_details'] = 'View student details';
$string['bim_marker_post_details'] = 'Mark posts';
$string['bim_marker_show_qs'] = '<p>This activity has set questions that the students must answer through their posts. You can $a.</p>';
$string['bim_marker_show_qs_link'] = 'view the questions here';

$string['marker_student_config_heading'] = 'Activity configuration information';
$string['marker_student_config_description'] = '<p>The configuration of this activity is such that:</p><ul>';
$string['marker_student_no_register'] = '<li> Students cannot register their blog/feed.</li>';
$string['marker_student_no_mirror'] = '<li> Student posts to their blog are not being copied into this activity.</li>';
$string['marker_student_config_end'] = '</ul><p>The teacher in charge of the course can change this configuration.</p>';

$string['bim_marker_allocate_heading'] = 'Changing post allocations';

$string['bim_marker_notstudent_heading'] = 'Error, not your student';
$string['bim_marker_notstudent_description'] = '<p>The student <strong>$a</strong> is not currently allocated to you. This means you cannot view or make changes to their details.</p>';
$string['bim_marker_student'] = 'Student';
$string['bim_marker_blog'] = 'Your blog';
//$string['bim_marker_posts'] = 'Num. of Posts';
$string['bim_marker_answers'] = '# actual answers / # required answers';
$string['bim_marker_m_r'] = '# Released / # Marked';
$string['bim_marker_progress'] = 'Progress result';
$string['bim_marker_posts'] = '# posts mirrored';
$string['bim_marker_error_heading'] = 'Error: validating form data for allocation';
$string['bim_marker_error_description'] = '<p>No changes made.</p>';
// bim_process_allocate_form
$string['marker_unallocating_heading'] = 'Unallocating the post';
$string['marker_unallocating_description'] = '<p>This <a href=\"$a\">student post</a> has been unallocated as an answer to a question.</p>';
$string['marker_change_alloc_heading'] = 'Changing question allocation';
$string['marker_change_alloc_description'] = 
         '<p>This <a href=\"$a->link\">student post</a> has been reallocated from question <strong>$a->old</strong> to question <strong>$a->new</strong>.</p>';
$string['marker_allocate'] = '<p>This <a href=\"$a->link\">student post</a> has been allocated to question </strong>$a->title</strong>.</p>';
// show_marker_post_details
$string['bim_post_heading'] = 'Mark posts';
$string['bim_post_no_questions'] = '<p>There are no questions currently defined for this activity. Use the \"Manage Questions\" tab above to add questions.</p>';
// show_marker_student_details
$string['bim_details_unregistered_description'] = '<p>These students have not yet registered their feeds</p>';
$string['bim_details_unreg_email_list'] = 'Unregistered students\' email addresses: ';
$string['bim_details_registered_heading'] = 'Registered student details';
$string['bim_student_details_heading'] = 'Your students';
$string['bim_details_count'] = '<p><strong>You have:</strong> $a->unregistered <a href=\"#unreg\">unregistered students</a>, and $a->registered <a href=\"#reg\">registered students.</a></p><p><strong>You can:</strong> View the students\' details below, or <a href=\"$a->mark\">mark their posts</a>. (see the green navigation box up and to the right.)</p>';
$string['bim_details_opml'] = '<p>$a->help Download OPML file: <a href=\"$a->url\">your students</a></p>';
$string['bim_details_unavailable'] = 'unavailable';
// bim_change_blog_registration
$string['bim_change_heading'] = 'Change student feed';
$string['bim_change_description'] = '<p>The following allows you to change the registered blog/feed for a specific student. The form contains:</p><ol> <li> Current details about the student, their feed and any marking. <br /><strong>Important:</strong> If you change a student\'s blog/feed any record of the old blog/feed (including any marks and comments) will be <strong>deleted</strong>.</li><li> A text box for you to enter the URL for the new feed.</li></ol>';
$string['bim_change_register_heading'] = 'Register student feed';
$string['bim_change_register_description'] = '<p>Use the form below to register the student\'s blog URL';
$string['bim_change_again'] = '<p>Please try to <a href=\"$a\">change the student blog</a> again.</p>';
$string['bim_change_success_heading'] = 'Student feed changed';
$string['bim_change_success_description'] = '<p>The student blog has been successfully changed. The details of the new feed are shown below.</p>';

///*********
// marker/change_blog.form.php
$string['bim_change_form_heading'] = 'Change student feed';
$string['bim_change_form_description'] = '<p>You should copy and paste the complete URL (e.g. <strong>http://davidtjones.wordpress.com</strong>) of the home page for a blog. BIM will attempt to describe any errors.</p>';
$string['bim_change_form_url'] = 'New student blog URL:';

// bim_setup_posts_table
$string['bim_table_username'] = 'Username';
$string['bim_table_name_blog'] = 'Name and blog';
$string['bim_table_name'] = 'Name';
$string['bim_table_email'] = 'Email';
$string['bim_table_register'] = 'Register blog';
$string['bim_table_questions'] = 'Questions';
$string['bim_table_marked'] = 'Marked';
$string['bim_table_entries'] = '#Posts';
$string['bim_table_last_post'] = 'Last post';
$string['bim_table_live_blog'] = 'Student blog';
$string['bim_table_current_blog'] = 'View current';
$string['bim_table_change_blog'] = 'Change it';
$string['bim_table_register_blog'] = 'Register blog';
$string['bim_table_details'] = 'Details';

// bim_create_posts_display
$string['bim_posts'] = ' posts';
$string['bim_answer'] = 'Live blog';
$string['bim_suspended'] = 'SUSPENDED';
$string['bim_not_marked'] = 'Mark it';
// bim_marker_mark_post
$string['bim_mark_prev_next_q'] = 'For this student: ';
$string['bim_mark_prev_next_s'] = 'For this question: ';
$string['bim_mark_prev_q'] = '<a href=\"$a\">mark previous question</a>';
$string['bim_mark_prev_q_none'] = 'mark previous question';
$string['bim_mark_next_q'] = '<a href=\"$a\">mark next question</a>';
$string['bim_mark_next_q_none'] = 'mark next question';
$string['bim_mark_prev_s'] = '<a href=\"$a\">mark previous student</a>';
$string['bim_mark_prev_s_none'] = 'mark previous student';
$string['bim_mark_next_s'] = '<a href=\"$a\">mark next student</a>';
$string['bim_mark_next_s_none'] = 'mark next student';
$string['bim_mark_prev_next_sep'] = ' | ';
$string['bim_mark_prev_next_none_q'] = ' no other questions';
$string['bim_mark_prev_next_none_s'] = ' no other students';

$string['bim_mark_post_heading'] = 'Mark post';
$string['bim_mark_details_error'] = 'Could not get student details';
$string['bim_mark_post'] = 'Post';
$string['bim_mark_cancel_heading'] = 'Cancelled marking of post';
$string['bim_mark_cancel_description'] = '<p>Redirecting back to show post details.</p>';
$string['bim_mark_changes_heading'] = 'Changes to post';
$string['bim_mark_comments_updated'] = '<li> Markers comments updated.</li>';
$string['bim_mark_mark_updated'] = '<li>Mark updated.</li>';
$string['bim_mark_marked'] = '<li>Post status updated to Marked.</li>';
$string['bim_mark_unallocated'] = '<li>Post unallocated as an answer.</li>';
$string['bim_mark_allocated'] = '<li>Post allocation changed to question <strong>$a</strong>.</li>';
$string['bim_mark_suspended'] = '<li> Post has been suspended. </li>';
$string['bim_mark_unsuspended'] = '<li> Post has unsuspended (set to Marked). </li>';
$string['bim_mark_marker'] = '<li>Marker updated to you.</li>';
$string['bim_mark_nochanges'] = '<p>No changes made.</p>';
$string['bim_mark_max_exceeded_heading'] = 'Mark exceeds maximum mark';
$string['bim_mark_max_exceeded_description'] = '<p>The mark you have awarded this student - <strong>$a->mark</strong> - exceeds the maximum allowed mark for this question - <strong>$a->max</strong>.</p><p>This is allowed, however, it may not be what is intended.</p>';
$string['bim_mark_min_exceeded_heading'] = 'Mark falls below minimum mark';
$string['bim_mark_min_exceeded_description'] = '<p>The mark you have awarded this student - <strong>$a->mark</strong> - falls below the minimum allowed mark for this question - <strong>$a->max</strong>.</p><p>This is allowed, however, it may not be what is intended.</p>';
$string['bim_mark_continue'] = 'Make more changes?';


//********************
// marker/allocation_form.php
$string['allocation_form_description'] = '<p>The following is a list of all the posts this student has made. Some may have been allocated to questions. Some may not.</p><p>Use the \"Change allocation to:\" menu to change the allocation of any student posts.</p>';
$string['allocation_form_status'] = '<span class=\"$a->class\">Status: $a->status</span>';
$string['marker_allocation_heading'] = 'All student posts';
$string['allocation_form_original'] = 'Original post';
$string['allocation_form_posted'] = 'Posted:';
$string['allocation_form_change'] = 'Change allocation to';
$string['allocation_form_post_title'] = 'Title: ';
$string['allocation_form_mark'] = 'Mark';
$string['allocation_form_suspend'] = 'Suspended?';
// marker/marking_form.php
$string['marking_form_mark'] = 'Mark:';
$string['marking_form_status'] = 'Status: $a';
$string['marking_form_student_post'] = '<strong>Student post <small(<a href=\"$a\">original post</a>)</small></strong>';
$string['marking_form_min'] = 'min: ';
$string['marking_form_max'] = 'max: ';

//********************
// student/view.php
// show_student_details
$string['student_details_user_error'] = 'Error: cannot get user details for user with id <strong>$a</strong>';
$string['student_details_nofeed_heading'] = 'No registered feed';
$string['student_details_nofeed_description'] = '<p>There is currently no registered feed for student <strong>$a</strong>.</p>';
$string['student_details_header'] = 'Details about this activity';
$string['student_details_questions_description'] = '<p>You are expected to use your blog to post answers to $a.</p>';
$string['student_details_show_qs'] = 'a range of questions';
$string['student_details_description'] = '<p>Below you will find details about what the system knows about you and your posts.</p>';
$string['student_details_noposts_heading'] = '<p>No posts yet.</p>';         
$string['student_details_noposts_description'] = '<p>There appear to be no posts from you feed stored here.</p>';         
$string['student_details_not_mirrored'] = '<p>This is because student feeds are not yet being mirrored for this activity.</p>';
$string['student_details_reasons'] = '<p>Possible reasons for this include:</p><ul><li> you have posted anything yet; </li><li> your feed could not be mirrored due to error; </li><li> your feed has not yet been mirrored (it should happen within a few hours) </li></ul>';
$string['student_details_details'] = '<p>Number of recorded posts: <strong>$a->total_posts</strong> (A summary of all posts appears in the <a href=\"#allposts\">All posts</a> table below.) </p><p>Number of answers required: <strong>$a->total_questions</strong></p><p>Number answers identified: <strong>$a->num_answered</strong> (if this is incorrect please contact your teacher).</p>' ;
$string['student_details_none_marked'] = '<p>None of your posts have yet been marked.</p>';
$string['student_details_num_marked'] = '<p>Of these posts $a have been marked, but the marks/comments are not yet available to you.</p>';
$string['student_details_released_heading'] = 'Marks and comments for released posts';
//$string['student_details_released_description'] = '<p>You can see marks and comments for $a posts in the following table.</p>';

$string['student_details_question_heading'] = 'Question';
$string['student_details_mark_heading'] = 'Mark';
$string['student_details_markers_comment_heading'] = 'Marker\'s comment';
$string['student_details_your_answer'] = 'your answer';

$string['student_details_allposts_heading'] = 'All posts';
$string['student_details_allposts_description'] = 
        '<p>The following table gives an overview of the $a of your posts the system knows about. If there is something missing, please contact your teacher.</p><p>The name for each posts is a link to your original post. The status describes how (or if) your post has been allocated as an answer, marked or released.</p>';

$string['student_details_status_heading'] = 'Status';
$string['student_details_about_heading'] = 'About this activity';
$string['student_details_about_description'] = '<p>The following information about this activity was provided by the teaching staff.</p><p>&nbsp;</p>$a';

// show_registere_feed
$string['register_cannot_heading'] = 'Cannot register your feed yet';
$string['register_cannot_description'] = '<p>This activity is currently configured so that you can not register your feed. This will be possible once the teaching staff change the activity configuration.</p>';
$string['register_again'] = '<p>Please try to <a href=\"$a\">register your URL</a> again.</p>';
$string['register_success_heading'] = 'Successful registration';
$string['register_success_description'] = '<p>Your URL has been successfully registered.</p><p>What is now known about your blog is shown in the following information.</p>';

// bim_rss.php/bim_check_wrong_urls
$string['bim_register_wrong_url_heading'] = 'Provided URL appears not to be for your blog';
$string['bim_wrong_url_wordpress'] = '<p>The URL you tried to register (<strong>$a</strong>) is actually the home page of the Wordpress blogging service and <strong>NOT</strong> for your individual blog.</p>';
$string['bim_wrong_url_notfinished'] = '<p>The blog URL you provided (<strong>$a</strong>) is for a page on the Wordpress.com website.  Not your blog.</p><p>This normally suggests you have copied a URL part-way through the blog creation process on Wordpress.com.  The simple fix is usually to carefully complete the blog creation process.</p>';
$string['bim_wrong_feed_notfinished'] = '<p>The feed URL generated for your blog url turns out to be <strong>$a</strong>. This cannot the feed URL for your blog. It is the feed URL for the Wordpress.com site.</p><p>This normally suggests you have copied a URL part-way through the blog creation process on Wordpress.com.  The simple fix is usually to carefully complete the blog creation process.</p>';
$string['bim_register_timeout_heading'] = 'Timed out trying to retrieve URL';
$string['bim_register_timeout_description'] = '<p>The system ran out of time while trying to retrieve the URL you entered (<strong>$a->url</strong>). The error reported was<blockquote><strong>$a->error</strong></blockquote>This normally suggests that there was a problem with either the system hosting your URL or with the network connection between here and your URL.</p><p>Try copying the URL into a web browser window and see if you can see the URL.  If this works, try to register again.</p><p>If the registration process continues to fail, ask for help.</p>';

?>
