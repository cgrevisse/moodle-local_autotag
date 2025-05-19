<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_autotag
 * @category    string
 * @copyright   2025 Christian Grévisse <christian.grevisse@uni.lu>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$string['autotagbutton'] = 'Launch AutoTag';
$string['autotagbutton_help'] = 'Will send your file to OpenAI and will return the tags suggested by GPT.';
$string['noopenaiapikeyset'] = 'No OpenAI API key provided.';
$string['onlypdffiles'] = 'Only PDF files are currently supported.';
$string['openaiapikey'] = 'OpenAI API key';
$string['openaiapikey_help'] = 'To be created at <a href="https://platform.openai.com/api-keys" target="_blank">https://platform.openai.com/api-keys</a>.';
$string['parsingerror'] = 'Error while parsing the generated tags.';
$string['pluginname'] = 'AutoTag';
