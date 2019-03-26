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

$plugin->version   = 2019032300; // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2016052300; // Requires this Moodle version (3.1)
$plugin->component = 'mod_invoicegenerator';

$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = "3.1 (Build: 2016052300)"; // User-friendly version number
