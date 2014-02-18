<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Allow the coordinator to add, modify and delete questions
 *
 * @package mod_bim
 * @copyright 2010 onwards David Jones {@link http://davidtjones.wordpress.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * manage_questions.php
 * - initial test to see if separate files for functions
 *   for specific screens make better sense going into
 *   small files
 * - coordinator/manage_questions provides support functions
 *   for the manage questions screen/page for coordinators
 *
 * bim_get_question_form( $questions, $cm )
 * - given list of questions for a bim and the $cm
 *   return the moodleform to display and manage questions
 */

require_once($CFG->dirroot.'/mod/bim/coordinator/question_form.php');
require_once($CFG->dirroot.'/mod/bim/coordinator/manage_marking.php');

/*******
 * bim_manage_questions( $bim, $cm )
 * - set up and process the form to manage/configure questions
 */

function bim_manage_questions( $bim, $cm ) {
    global $CFG, $DB, $OUTPUT;

    $questions = bim_get_question_hash( $bim->id );
    $num_questions = count( $questions );
    if ( empty($questions)) {
        $num_questions=0;
    }

    $question_form = bim_get_question_form( $questions, $cm );

    if ( ! $question_form->is_submitted() ) {
        $questions = bim_get_question_response_stats( $questions );
        if ( $num_questions > 0 ) {
            print_string( 'bim_questions_current', 'bim', $num_questions );
        } else {
            echo $OUTPUT->heading( get_string( 'bim_questions_none_heading', 
                    'bim' ), 2, 'left' );
            print_string( 'bim_questions_none_description', 'bim' );
        }
        add_to_log( $cm->course, "bim", "Questions manage",
                "view.php?id=$cm->id&tab=questions",
                "Display", $cm->id );
        $question_form->display();
    } else if ( $question_form->is_cancelled() ) {
        $question_form->display();
    } else if ( $fromform = $question_form->get_data() ) {
        $additions = false;
        $deletions = false;

        echo $OUTPUT->box_start( 'noticebox boxwidthnormal' );
        echo $OUTPUT->heading( 
            get_string( 'bim_questions_changes_heading', 'bim' ), 2, 'left' );
        // check the new/add question

        $fromform->body_new = $fromform->body_new['text'];
        // process any new question that has been added
        if ( $fromform->title_new != "" || $fromform->max_new != 0 ||
                $fromform->min_new != 0 || $fromform->body_new != "" ) {
            // create new record
            $new_question = new StdClass();
            $new_question->title = $fromform->title_new;
            $new_question->min_mark = $fromform->min_new;
            $new_question->max_mark = $fromform->max_new;
            $new_question->body = $fromform->body_new;
            $new_question->bim = $bim->id;
            $new_question->id = '';

            print_string( 'bim_questions_adding', 'bim', $fromform->title_new );

            if ( ! $DB->insert_record( "bim_questions", $new_question ) ) {
                print_error( 'bim_questions_error_insert', 'bim' );
            }
            $additions = true;
            add_to_log( $cm->course, "bim", "Questions manage",
                    "view.php?id=$cm->id&tab=questions",
                    "Adding question", $cm->id );
        }

        // loop through each existing question
        // if any change in the form content, update the database
        $changed = array();

        if ( ! empty( $questions ) ) {
            foreach ($questions as $question) {
                $qid = $question->id;
                $title = "title_".$qid;
                $min = "min_".$qid;
                $max = "max_".$qid;
                $body = "body_".$qid;
                $delete = "delete_".$qid;

                // are we deleting this current question?
                if ( isset( $fromform->$delete ) ) {
                    if ( ! $DB->delete_records( "bim_questions",
                                array("id"=>$qid)) ) {
                        print_string( 'bim_questions_error_delete', 'bim',
                                $question->title );
                    } else {
                        print_string( 'bim_questions_deleting', 'bim',
                                $question->title );
                        $deletions = true;
                    }
                }
                // KLUDGE: for some reason body has a space at the start
                // after being passed back from the form.  Don't want that.
                $text = $fromform->$body;
                $fromform->$body = $text['text'];

                if ( $fromform->$title != $question->title  ||
                        $fromform->$min != $question->min_mark ||
                        $fromform->$max != $question->max_mark ||
                        $fromform->$body != $question->body ) {
                    $question->title = $fromform->$title;
                    $question->min_mark = $fromform->$min;
                    $question->max_mark = $fromform->$max;
                    $question->body = $fromform->$body;

                    // get a copy so the unsert won't cause problems
                    $changed[$qid] = clone $question;
                    unset( $changed[$qid]->status );
                }
            }
        }
        $changes = count( $changed );
        if ( $changes ) {
            print_string( 'bim_questions_changing', 'bim', $changes );
            // loop through each change and update the database
            foreach ($changed as $change) {
                echo "<li> $change->title </li> ";
                if ( ! $DB->update_record( 'bim_questions', $change ) ) {
                    print_error( 'bim_questions_error_changing_title', 'bim' );
                }
            }
            echo "</ul>";
        }
        if ( $changes > 0 || $deletions || $additions ) {
            // need to update the gradebook to represent the new max marks
            bim_update_gradebook( $bim ); 
            add_to_log( $cm->course, "bim", "Questions manage",
                    "view.php?id=$cm->id&tab=questions",
                    "Modified question(s)", $cm->id );
        }
        if ( ! $additions && ! $deletions && $changes == 0 ) {
            print_string( 'bim_questions_nochanges', 'bim' );
        }
        echo $OUTPUT->box_end();
 
        $url = "$CFG->wwwroot/mod/bim/view.php?id=$cm->id&tab=questions";
        print_string( 'bim_continue', 'bim', $url );

    } else {
        print_error( 'bim_questions_error_processing', 'bim' );
    }
}

/*
 * bim_get_question_form( $questions, $cm )
 * - given list of questions for a bim and the $cm
 *   return the moodleform to display and manage questions
 */

function bim_get_question_form( $questions, $cm ) {
    $question_form = new question_form( 'view.php',
            array( 'questions' => $questions,
                'id' => $cm->id ) );

    // set the form values to existing questions
    $toform = array();
    if ( empty( $questions ) ) {
        return $question_form;
    }

    foreach ($questions as $question) {
        $toform["title_$question->id"] = $question->title;
        $toform["min_$question->id"] = $question->min_mark;
        $toform["max_$question->id"] = $question->max_mark;
        $toform["body_$question->id"] =
            array( 'text' => $question->body );
    }

    $question_form->set_data( $toform );

    return $question_form;
}

