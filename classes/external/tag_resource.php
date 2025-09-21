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

namespace local_autotag\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/autotag/lib.php');
require_once($CFG->dirroot.'/local/autotag/vendor/autoload.php');

/**
 * Class tag_resource
 *
 * @package    local_autotag
 * @copyright  2025 Christian Grévisse <christian.grevisse@uni.lu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_resource extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'resourceid' => new external_value(PARAM_INT, 'ID of resource'),
        ]);
    }

    /**
     * Returns description of method result value
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_value(PARAM_TEXT, 'tag')
        );
    }

    /**
     * Tag resource
     * @param object $resourceid ID of resource
     * @return array of tags
     */
    public static function execute($resourceid) {
        $params = self::validate_parameters(self::execute_parameters(), ['resourceid' => $resourceid]);

        $context = \context_module::instance($resourceid);
        self::validate_context($context);
        require_capability('mod/resource:addinstance', $context);

        // Get file info for resource.
        $fileinfo = local_autotag_get_fileinfo_for_resource($params['resourceid']);

        // Currently only PDF files are supported.
        if ($fileinfo->extension != 'pdf') {
            throw new \Exception(get_string('onlypdffiles', 'local_autotag'));
        }

        // Get OpenAI API key from plugin settings.
        $openaiapikey = get_config('local_autotag', 'openaiapikey');

        if (empty($openaiapikey)) {
            throw new \Exception(get_string('noopenaiapikeyset', 'local_autotag'));
        }

        // Initialize OpenAI client.
        $client = \OpenAI::factory()
            ->withApiKey($openaiapikey)
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 600]))
            ->make();

        // Get file content.
        $file = $fileinfo->path;
        $fp = fopen($file, "rb");
        $binary = fread($fp, filesize($file));
        $b64 = base64_encode($binary);

        // Call OpenAI to get tags.
        $response = $client->responses()->create([
            'model' => 'gpt-5',
            'input' => [
                [
                    'role' => 'system',
                    'content' => 'You are a tagging assistant. Your task is to extract a list of the most important
                                    tags for the given content. All tags shall be given in English.',
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_file',
                            'filename' => $fileinfo->name,
                            'file_data' => 'data:application/pdf;base64,'.$b64,
                        ],
                    ],
                ],
            ],
            'text' => [
                "format" => [
                    'type' => 'json_schema',
                    'name' => 'tag_response',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'tags' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'required' => ['tags'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ]);

        $tags = [];

        // Parse the response to get the tags.
        try {
            $tags = json_decode($response->outputText)->tags;
        } catch (\Exception $e) {
            throw new \Exception(get_string('parsingerror', 'local_autotag'));
        }

        return $tags;
    }

}
