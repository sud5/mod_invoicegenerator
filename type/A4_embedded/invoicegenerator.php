<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * A4_embedded invoicegenerator type
 *
 * @package    mod_invoicegenerator
 * @copyright  Sudhanshu Gupta<sudhanshug5@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();
$pdf = new PDF($invoicegenerator->orientation, 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($invoicegenerator->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

    $x = 10;
    $y = 5;
$context = context_course::instance($course->id);
$teacher = get_role_users(3 , $context);
foreach($teacher as $value){
  $teacher_name = $value->firstname.' '.$value->lastname;
}
// Add text
$pdf->SetTextColor(0, 0, 120);
invoicegenerator_print_text($pdf, $x, $y, 'C', 'Helvetica', '', 15, get_string('title', 'invoicegenerator'));
$pdf->SetTextColor(0, 0, 0);
invoicegenerator_print_text($pdf, ($x + 60), $y + 10, 'L', 'Helvetica', '', 10, get_string('course_name', 'invoicegenerator'));
invoicegenerator_print_text($pdf, $x, $y + 10, 'C', 'Helvetica', '', 10, format_string($course->fullname));
invoicegenerator_print_text($pdf, ($x + 60), $y + 15, 'L', 'Helvetica', '', 10, get_string('instructor', 'invoicegenerator'));
invoicegenerator_print_text($pdf, $x, $y + 15, 'C', 'Helvetica', '', 10, $teacher_name);
$attendance_id = $DB->get_field('attendance', 'id', array('course' => 2));
$sessions_in_course = $DB->get_records('attendance_sessions', array('attendanceid' => $attendance_id));
$yaxis_change = 20;
  foreach ($sessions_in_course as $session_in_course) {
    $sesstext = get_string('session', 'invoicegenerator');
    $sesstext .= ' '.userdate($session_in_course->sessdate, '%b %d');
    $sesstext .= ' ' . session_strftimehm($session_in_course->sessdate);
    invoicegenerator_print_text($pdf, ($x + 60), $y + $yaxis_change, 'L', 'Helvetica', '', 10, $sesstext);
    $attendance = get_attendance($session_in_course->id, $attendance_id);
    $present = $attendance['P'];
    invoicegenerator_print_text($pdf, $x, $y + $yaxis_change, 'C', 'Helvetica', '', 10, get_string('present', 'invoicegenerator') ."-$present ");
        $absent = $attendance['A'];
    invoicegenerator_print_text($pdf, $x + 35, $y + $yaxis_change, 'C', 'Helvetica', '', 10, get_string('absent', 'invoicegenerator') ."-$absent ");
    $late = $attendance['L'];
    invoicegenerator_print_text($pdf, $x + 70, $y + $yaxis_change, 'C', 'Helvetica', '', 10, get_string('late', 'invoicegenerator') ."-$late ");
    $excused = $attendance['E'];
    invoicegenerator_print_text($pdf, $x + 105, $y + $yaxis_change, 'C', 'Helvetica', '', 10, get_string('excused', 'invoicegenerator') ."-$excused ");
    $yaxis_change = $yaxis_change + 4;
  }

function get_attendance($session_id, $attendance_id){
  global $DB;
  $attendance = array('P'=>'NM ','A'=>'NM ','L'=>'NM ','E'=>'NM ');
  $graderesults = $DB->get_records_sql('SELECT al.statusid, count(al.id) as count From {attendance_log} al where al.sessionid = ' .$session_id . ' group by al.statusid');
  if(!empty($graderesults)){
  $attendance_statuses = $DB->get_records_sql('SELECT acronym, id  From {attendance_statuses} where attendanceid = ' .$attendance_id );
  $attendance_array = array();
  foreach($graderesults as $graderesultskey => $graderesultsvalue){
    $attendance_array[$graderesultskey] = $graderesultsvalue->count;
  }
   $attendance_status_array = array();
  foreach($attendance_statuses as $attendance_statuseskey => $attendance_statusesvalue){
    $attendance_status_array [$attendance_statuseskey] = isset($attendance_array[$attendance_statusesvalue->id]) ? $attendance_array[$attendance_statusesvalue->id] : 0;
  }
  $attendance = $attendance_status_array;
  }
  return $attendance;
}

function session_strftimehm($time) {
  $mins = userdate($time, '%M');

  if ($mins == '00') {
    $format = get_string('strftimeh', 'invoicegenerator');
  }
  else {
    $format = get_string('strftimehm', 'invoicegenerator');
  }

  $userdate = userdate($time, $format);

  // Some Lang packs use %p to suffix with AM/PM but not all strftime support this.
  // Check if %p is in use and make sure it's being respected.
  if (stripos($format, '%p')) {
    // Check if $userdate did something with %p by checking userdate against the same format without %p.
    $formatwithoutp = str_ireplace('%p', '', $format);
    if (userdate($time, $formatwithoutp) == $userdate) {
      // The date is the same with and without %p - we have a problem.
      if (userdate($time, '%H') > 11) {
        $userdate .= 'pm';
      }
      else {
        $userdate .= 'am';
      }
    }
    // Some locales and O/S don't respect correct intended case of %p vs %P
    // This can cause problems with behat which expects AM vs am.
    if (strpos($format, '%p')) { // Should be upper case according to PHP spec.
      $userdate = str_replace('am', 'AM', $userdate);
      $userdate = str_replace('pm', 'PM', $userdate);
    }
  }

  return $userdate;
}