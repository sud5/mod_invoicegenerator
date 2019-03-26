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

/**
 * Add invoicegenerator instance.
 *
 * @param stdClass $invoicegenerator
 * @return int new invoicegenerator instance id
 */
function invoicegenerator_add_instance($invoicegenerator) {
  global $DB;

  // Create the invoicegenerator.
  $invoicegenerator->invoicegeneratortype = 'A4_embedded';
  $invoicegenerator->orientation = 'L';
  $invoicegenerator->timecreated = time();
  $invoicegenerator->timemodified = $invoicegenerator->timecreated;

  return $DB->insert_record('invoicegenerator', $invoicegenerator);
}

/**
 * Update invoicegenerator instance.
 *
 * @param stdClass $invoicegenerator
 * @return bool true
 */
function invoicegenerator_update_instance($invoicegenerator) {
  global $DB;

  // Update the invoicegenerator.
  $invoicegenerator->timemodified = time();
  $invoicegenerator->id = $invoicegenerator->instance;

  return $DB->update_record('invoicegenerator', $invoicegenerator);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id
 * @return bool true if successful
 */
function invoicegenerator_delete_instance($id) {
  global $DB;

  // Ensure the invoicegenerator exists
  if (!$invoicegenerator = $DB->get_record('invoicegenerator', array('id' => $id))) {
    return false;
  }

  // Prepare file record object
  if (!$cm = get_coursemodule_from_instance('invoicegenerator', $id)) {
    return false;
  }

  $result = true;
  if (!$DB->delete_records('invoicegenerator', array('id' => $id))) {
    $result = false;
  }

  // Delete any files associated with the invoicegenerator
  $context = context_module::instance($cm->id);
  $fs = get_file_storage();
  $fs->delete_area_files($context->id);

  return $result;
}

/**
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function invoicegenerator_supports($feature) {
  switch ($feature) {
    case FEATURE_GROUPMEMBERSONLY: return true;
    case FEATURE_MOD_INTRO: return true;

    default: return null;
  }
}