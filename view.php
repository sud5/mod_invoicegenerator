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

require_once("../../config.php");
require_once("$CFG->dirroot/mod/invoicegenerator/locallib.php");
require_once("$CFG->libdir/pdflib.php");

$id = required_param('id', PARAM_INT);    // Course Module ID
$action = optional_param('action', '', PARAM_ALPHA);
$edit = optional_param('edit', -1, PARAM_BOOL);

if (!$cm = get_coursemodule_from_id('invoicegenerator', $id)) {
  print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
  print_error('course is misconfigured');
}
if (!$invoicegenerator = $DB->get_record('invoicegenerator', array('id' => $cm->instance))) {
  print_error('course module is incorrect');
}

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/invoicegenerator:view', $context);

$event = \mod_invoicegenerator\event\course_module_viewed::create(array(
      'objectid' => $invoicegenerator->id,
      'context' => $context,
    ));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('invoicegenerator', $invoicegenerator);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Initialize $PAGE, compute blocks
$PAGE->set_url('/mod/invoicegenerator/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($invoicegenerator->name));
$PAGE->set_heading(format_string($course->fullname));

if (($edit != -1) and $PAGE->user_allowed_editing()) {
  $USER->editing = $edit;
}

// Add block editing button
if ($PAGE->user_allowed_editing()) {
  $editvalue = $PAGE->user_is_editing() ? 'off' : 'on';
  $strsubmit = $PAGE->user_is_editing() ? get_string('blockseditoff') : get_string('blocksediton');
  $url = new moodle_url($CFG->wwwroot . '/mod/invoicegenerator/view.php', array('id' => $cm->id, 'edit' => $editvalue));
  $PAGE->set_button($OUTPUT->single_button($url, $strsubmit));
}

make_cache_directory('tcpdf');

// Load the specific invoicegenerator type.
require("$CFG->dirroot/mod/invoicegenerator/type/$invoicegenerator->invoicegeneratortype/invoicegenerator.php");

if (empty($action)) { // Not displaying PDF
  echo $OUTPUT->header();

  $viewurl = new moodle_url('/mod/invoicegenerator/view.php', array('id' => $cm->id));
  groups_print_activity_menu($cm, $viewurl);
  $currentgroup = groups_get_activity_group($cm);
  $groupmode = groups_get_activity_groupmode($cm);

  if (!empty($invoicegenerator->intro)) {
    echo $OUTPUT->box(format_module_intro('invoicegenerator', $invoicegenerator, $cm->id), 'generalbox', 'intro');
  }

  $str = get_string('openwindow', 'invoicegenerator');
  echo html_writer::tag('p', $str, array('style' => 'text-align:center'));
  $linkname = get_string('getinvoicegenerator', 'invoicegenerator');

  $link = new moodle_url('/mod/invoicegenerator/view.php?id=' . $cm->id . '&action=get');
  $button = new single_button($link, $linkname);
  $button->add_action(new popup_action('click', $link, 'view' . $cm->id, array('height' => 600, 'width' => 800)));


  echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center'));
  echo $OUTPUT->footer($course);
  exit;
}
else { // Output to pdf
  // No debugging here, sorry.
  $CFG->debugdisplay = 0;
  @ini_set('display_errors', '0');
  @ini_set('log_errors', '1');

  $filename = invoicegenerator_get_invoicegenerator_filename($invoicegenerator, $cm, $course) . '.pdf';

  // PDF contents are now in $file_contents as a string.
  $filecontents = $pdf->Output('', 'S');

  send_file($filecontents, $filename, 0, 0, true, false, 'application/pdf');
}
