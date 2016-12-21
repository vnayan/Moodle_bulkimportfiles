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
 * User: Nayan
 * Date: 6/10/16
 * Time: 3:12 PM
 */
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir.'/formslib.php');
require_once('content_form.php');
require_once($CFG->libdir.'/gdlib.php');
require_once('lib.php');
global $DB, $CFG;
$filecolumns = array('category', 'course', 'topic', 'filename', 'file url');
$iid = optional_param('iid', '', PARAM_TEXT);
$PAGE->set_url(new moodle_url('/tool/uploadcontent/contentbulkupload.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout(get_string('pagelayout' , 'tool_uploadcontent'));
$PAGE->set_title(get_string('title' , 'tool_uploadcontent'));
require_login();
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadstatus' , 'tool_uploadcontent'));
if ($iid != '') {
    $cir = new csv_import_reader($iid, 'uploadcontent');
    $cir->init();
    $linenum = 1;
    while ($fields = $cir->next()) {
        $linenum++;
        $rowcols = array();
        $rowcols['line'] = $linenum;
        foreach ($fields as $key => $field) {
              $rowcols[] = s(trim($field));
        }
        if (tool_uploadcontent_validate_category($rowcols[0])) {
            if (tool_uploadcontent_validate_course($rowcols[1])) {
                if (tool_uploadcontent_validate_url($rowcols[4], $rowcols[3])) {
                    if (tool_uploadcontent_check_course_capability($rowcols[1])) {
                        tool_uploadcontent_add_resourse_to_course($rowcols);
                    } else {
                        $rowcols[] = get_string('permissiondenied' , 'tool_uploadcontent');
                        $data[] = $rowcols;
                    }
                } else {
                    $rowcols[] = get_string('invalidurl' , 'tool_uploadcontent');
                    $data[] = $rowcols;
                }

            } else {
                  $rowcols[] = get_string('invalidcourse' , 'tool_uploadcontent');
                  $data[] = $rowcols;
            }
        } else {
            $rowcols[] = get_string('invalidcategory' , 'tool_uploadcontent');
            $data[] = $rowcols;
        }
    }

    if (!empty($data)) {
        $table = new html_table();
        $table->id = get_string('tableid' , 'tool_uploadcontent');
        $table->attributes['class'] = get_string('tableclass' , 'tool_uploadcontent');
        $table->tablealign = get_string('tablealign' , 'tool_uploadcontent');
        $table->summary = get_string('uploadcontentspreview', 'tool_uploadcontent');
        $table->head = array();
        $table->data = $data;
        $table->head[] = get_string('line' , 'tool_uploadcontent');
        foreach ($filecolumns as $column) {
            $table->head[] = $column;
        }
        $table->head[] = get_string('status');
        echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
    }
    echo html_writer::start_span('status') . get_string('successstatus' , 'tool_uploadcontent') .
      html_writer::link(new moodle_url('/admin/tool/uploadcontent/content.php'), 'Click'). get_string('substring' , 'tool_uploadcontent')
      .html_writer::end_span();
    $temppath = explode('/' , $rowcols[4]);
    $path = $CFG->dataroot."/filedir".$temppath[0]."/".$temppath[1]."/".$temppath[2]."/";
    remove_dir($path);
}
echo $OUTPUT->footer();