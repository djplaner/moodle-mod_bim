<?php

// - collection of functions required for marker functions

/*
 * bim_get_next_prev_student( $quesiton, $student, $markers_students, $bim )
 * - given a student id and a list of all students for a marker
 * - return an array with keys NEXT PREV which indicate which
 *   of the students is next and previous
 * - the students must have answered the question
 */

function  bim_get_next_prev_student( $question, $student, 
                                     $markers_students, $bim ) {

    global $DB;

    $results = Array( 'NEXT' => "", 'PREV' => "" );

    // get just the students with answers to $question
    $students = array_keys( $markers_students )  ;
//    $student_ids = implode( ',', $students );

    list( $stud_sql, $stud_params ) = $DB->get_in_or_equal( $students);

    $sql = "select userid,id from {bim_marking} where " .
           "question=$question and " .
           "status in ('Submitted','Marked','Released','Suspended') ".
           " and userid $stud_sql order by userid";

    $details = $DB->get_records_sql( $sql, $stud_params );
    if ( ! empty( $details ) ) {
        // find where $student is
        // - just get the array pointer to the right place
        foreach ( $details as $key => $value ) {
            if ( $key == $student ) {
                break;
            }
        }

        // make sure the current student is in the array
        if ( isset( $details[$student] )) {
            // we're at next already
            $next = current( $details );
            if ( $next == false ) { // beyond end, go back to end (current)
                end( $details );
                // go back one for previous
                $previous = prev( $details );
                if ( $previous != false ) {
                    $results['PREV'] = $previous->id;
                }
            } else {
                $results['NEXT'] = $next->id;
                // go back 2 (if possible) to get previous
                if ( prev( $details ) != false ) {
                    $previous = prev( $details );
                    if ( $previous != false ) {
                        $results['PREV'] = $previous->id;
                    }

                }
            }
        }
    }
    // get the prev/next
    return $results;
}

/*
 * bim_show_next_prev_question( $cm, $nextPrev )
 * - given the cm and an array with keys NEXT PREVIOUS
 * - display navigation for next prevoius question for this student
 */

function bim_show_next_prev_question( $cm, $nextPrev, $url, $type ) {
    global $CFG;

    echo '<small>';

    print_string( 'bim_mark_prev_next_'.$type, 'bim' );

    if ( $nextPrev['PREV'] == '' && $nextPrev['NEXT'] == '' ) {
        print_string( 'bim_mark_prev_next_none_'.$type, 'bim' );
    } else {
        if ( $nextPrev['PREV'] != '' ) {
            $show_url = $url . $nextPrev['PREV'];
       
            print_string( 'bim_mark_prev_'.$type, 'bim', $show_url );
        } else {
            print_string( 'bim_mark_prev_'.$type.'_none','bim' );
        }

        print_string( 'bim_mark_prev_next_sep', 'bim' );

        if ( $nextPrev['NEXT'] != '' ) {
            $show_url = $url . $nextPrev['NEXT'];
       
            print_string( 'bim_mark_next_'.$type, 'bim', $show_url );
        } else {
            print_string( 'bim_mark_next_'.$type.'_none', 'bim' );
        } 
    }
 
    echo '</small><br />';
} 
  

/*
 * bim_get_next_prev_question( $current, $details )
 * - given an array of student responses and the id for the question
 *   in the current response, 
 * - return an array with keys NEXT and PREV and values of the
 *   ids for the next and previous response
 * - And empty value suggests there is no next/prev
 */

function bim_get_next_prev_question( $current, $details ) {

    $results = Array( 'NEXT' => "", 'PREV' => "");

    if ( empty( $details ) ) {
        return $results;
    }

    // sort the $details so it is ordered on question field
    if ( uasort( $details, "bim_sort_questions" ) ) {
        // question=0 means not allocated

        // remove the unallocated posts - i.e. not answers to questions
        foreach ( $details as $key => $value ) {
            if ( $value->status == "Unallocated" ) {
                unset( $details[$key] );
           }
        }

        // point to the current element
        foreach ( $details as $key => $value ) {
            if ( $key == $current ) {
                break;
            }
        }
        // if there's actually something then
        if ( isset( $details[$current] )) {
            // we're at next already
            $next = current( $details );
            if ( $next == false ) { // beyond end, go back to end (current)
                end( $details );
                // go back one for previous
                $previous = prev( $details );
                if ( $previous != false ) {
                    $results['PREV'] = $previous->id;
                }
            } else {
                $results['NEXT'] = $next->id;
                // go back 2 (if possible) to get previous
                if ( prev( $details ) != false ) {
                    $previous = prev( $details );
                    if ( $previous != false ) {
                        $results['PREV'] = $previous->id;
                    }
                }
            }
        }
    }

    return $results;
}





/*
 * bim_sort_questions( $a, $b )
 * - given object/row from bim_marking compare
 */

function bim_sort_questions( $a, $b ) {
    if ( $a->question == $b->question ) {
        return 0;
    }
    return ( $a->question < $b->question ) ? -1 : 1;
}

