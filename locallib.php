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

require_once($CFG->dirroot.'/mod/invoicegenerator/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 * @param int $width horizontal dimension of text block
 */
function invoicegenerator_print_text($pdf, $x, $y, $align, $font='freeserif', $style, $size = 10, $text, $width = 0) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell($width, 0, '', '', $text, 0, 0, 0, true, $align);
}

/**
 * Get normalised invoicegenerator file name without file extension.
 *
 * @param stdClass $invoicegenerator
 * @param stdClass $cm
 * @param stdClass $course
 * @return string file name without extension
 */
function invoicegenerator_get_invoicegenerator_filename($invoicegenerator, $cm, $course) {
    $coursecontext = context_course::instance($course->id);
    $coursename = format_string($course->shortname, true, array('context' => $coursecontext));

    $context = context_module::instance($cm->id);
    $name = format_string($invoicegenerator->name, true, array('context' => $context));

    $filename = $coursename . '_' . $name;
    $filename = core_text::entities_to_utf8($filename);
    $filename = strip_tags($filename);
    $filename = rtrim($filename, '.');

    // Ampersand is not a valid filename char, let's replace it with something else.
    $filename = str_replace('&', '_', $filename);

    $filename = clean_filename($filename);

    if (empty($filename)) {
        // This is weird, but we need some file name.
        $filename = 'invoicegenerator';
    }

    return $filename;
}
