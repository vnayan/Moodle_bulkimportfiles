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
 * Created by PhpStorm.
 * User: Sushil
 * Date: 4/10/16
 * Time: 12:48 PM
 */
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir.'/formslib.php');
require_once('classes/step_form.php');
require_once($CFG->libdir.'/gdlib.php');
require_once('lib.php');
global $DB, $CFG, $USER;

$context = context_system::instance();
admin_externalpage_setup('tooluploadcontent');
require_capability('tool/uploadcontent:uploadcontent', $context);
$override = true;
$selected[] = "ars";
$inactive[] = "";
$activated[] = "ars";
$PAGE->set_url(new moodle_url('/tool/uploadcontent/index.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
require_login();
$mform1 = new tool_uploadcontent_step_form();
if ($fromform = $mform1->get_data()) {
     core_php_time_limit::raise();
     raise_memory_limit(MEMORY_EXTRA);
     $fullpath = $CFG->dataroot . '/filedir';
     $zipdir = tool_uploadcontent_my_mktempdir($fullpath . '/'.'upload');
     $dstfile = $zipdir . '/content.zip';
     $content = $mform1->get_file_content('contentfile');
     $name = $mform1->get_new_filename('contentfile');
    if (!$mform1->save_file('contentfile', $dstfile, true)) {
        echo $OUTPUT->notification('Upload Failed');
    } else {
         $fp = get_file_packer('application/zip');
         $unzipresult = $fp->extract_to_pathname($dstfile, $zipdir);
        if (!$unzipresult) {
            echo $OUTPUT->notification('Upload Failed');
            @remove_dir($zipdir);
        } else {
               $csvexporter = new csv_export_writer('comma');
               $csvexporter->set_filename('bulkactivity', '.csv');
               $headers = array('Category', 'Course', 'Topic', 'Filename', 'File Url');
               $csvexporter->add_data($headers);
            foreach ($unzipresult as $key => $value) {
                if (strrpos($key, "/")) {
                    $filename = substr($key, strrpos($key, "/") + 1);
                } else {
                    $filename = $key;
                }
                 $fileurl = $zipdir.'/'.$key;
                 $baselength=strlen($CFG->dataroot."/filedir");
                 $fileurl = substr($fileurl, $baselength);
                 $fileurl = str_replace($filename, "", $fileurl);
                 $record = array('', '', '', $filename, $fileurl);
                if ($filename != '') {
                    $csvexporter->add_data($record);
                }
            }
               @unlink($dstfile);
               $csvexporter->download_file();


        }

    }

}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('Contentheading', 'tool_uploadcontent'));
tool_uploadcontent_print_tabs($selected, $inactive, $activated);
$mform1->display();
echo $OUTPUT->footer();
