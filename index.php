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


require_once('../../config.php');
require_once('locallib.php');

$id = required_param('id', PARAM_INT);           // Course Module ID

// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

// Requires a login
require_login($course);

// Declare variables
$currentsection = "";
$printsection = "";
$timenow = time();

// Strings used multiple times
$strinvoicegenerators = get_string('modulenameplural', 'invoicegenerator');
$strissued  = get_string('issued', 'invoicegenerator');
$strname  = get_string("name");
$strsectionname = get_string('sectionname', 'format_'.$course->format);

// Print the header
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/invoicegenerator/index.php', array('id'=>$course->id));
$PAGE->navbar->add($strinvoicegenerators);
$PAGE->set_title($strinvoicegenerators);
$PAGE->set_heading($course->fullname);

// Add the page view to the Moodle log
$event = \mod_invoicegeneratorgenerator\event\course_module_instance_list_viewed::create(array(
    'context' => context_course::instance($course->id)
));
$event->add_record_snapshot('course', $course);
$event->trigger();

// Get the invoicegenerators, if there are none display a notice
if (!$invoicegenerators = get_all_instances_in_course('invoicegenerator', $course)) {
    echo $OUTPUT->header();
    notice(get_string('noinvoicegenerators', 'invoicegenerator'), "$CFG->wwwroot/course/view.php?id=$course->id");
    echo $OUTPUT->footer();
    exit();
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strissued);
} else {
    $table->head  = array ($strname, $strissued);
}

foreach ($invoicegenerators as $invoicegenerator) {
    if (!$invoicegenerator->visible) {
        // Show dimmed if the mod is hidden
        $link = html_writer::tag('a', $invoicegenerator->name, array('class' => 'dimmed',
            'href' => $CFG->wwwroot . '/mod/invoicegenerator/view.php?id=' . $invoicegenerator->coursemodule));
    } else {
        // Show normal if the mod is visible
        $link = html_writer::tag('a', $invoicegenerator->name, array('class' => 'dimmed',
            'href' => $CFG->wwwroot . '/mod/invoicegenerator/view.php?id=' . $invoicegenerator->coursemodule));
    }

    $strsection = '';
    if ($invoicegenerator->section != $currentsection) {
        if ($invoicegenerator->section) {
            $strsection = get_section_name($course, $invoicegenerator->section);
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $invoicegenerator->section;
    }

    // Get the latest invoicegenerator issue
    if ($certrecord = $DB->get_record('invoicegenerator_issues', array('userid' => $USER->id, 'invoicegeneratorid' => $invoicegenerator->id))) {
        $issued = userdate($certrecord->timecreated);
    } else {
        $issued = get_string('notreceived', 'invoicegenerator');
    }

    if ($usesections) {
        $table->data[] = array ($strsection, $link, $issued);
    } else {
        $table->data[] = array ($link, $issued);
    }
}

echo $OUTPUT->header();
echo '<br />';
echo html_writer::table($table);
echo $OUTPUT->footer();
