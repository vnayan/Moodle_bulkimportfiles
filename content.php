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
 * Name: CSV file upload
 * Auther: Sushil Kumar Yadav
 * Date: 4/10/16
 * Time: 12:48 PM
 */
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir.'/formslib.php');
require_once('content_form.php');
require_once($CFG->libdir.'/gdlib.php');
require_once('lib.php');
global $DB, $CFG;
$context = context_system::instance();
admin_externalpage_setup('tooluploadcontent');
require_capability('tool/uploadcontent:uploadcontent', $context);
$override = true;
$flag = 1;
$selected[] = "arc";
$inactive[] = "";
$activated[] = "arc";
$filecolumns = array('category', 'course', 'topic',
  'filename', 'file url'
);
$previewrows = optional_param('previewrows', 10, PARAM_INT);
$returnurl = new moodle_url('admin/tool/uploadcontent/content.php');

$PAGE->set_url(new moodle_url('/tool/uploadcontent/content.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title('CSV upload');
require_login();
$mform1 = new tool_uploadcontent_content_form();
if ($fromform = $mform1->get_data()) {
    // Print the header.
    $iid = csv_import_reader::get_new_iid('uploadcontent');
    $cir = new csv_import_reader($iid, 'uploadcontent');
    $content = $mform1->get_file_content('contentfile');
    $readcount = $cir->load_csv_content($content, $fromform->encoding, $fromform->delimiter_name);
    $csvloaderror = $cir->get_error();
    unset($content);
    if (!is_null($csvloaderror)) {
        print_error('csvloaderror', '', $returnurl, $csvloaderror);
    }
    $flag = 0;
    $data = array();
    $cir->init();
    $linenum = 1;
    while ($linenum <= $previewrows and $fields = $cir->next()) {
        $linenum++;
        $rowcols = array();
        $rowcols['line'] = $linenum;
        foreach ($fields as $key => $field) {
            $rowcols[] = s(trim($field));
        }
        $data[] = $rowcols;

    }

    if ($fields = $cir->next()) {
        $data[] = array_fill(0, count($fields) + 2, '...');
    }
    $cir->close();
    $PAGE->set_url(new moodle_url('/tool/uploadcontent/content.php'));
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('admin');
    $PAGE->set_title('CSV upload');
    echo $OUTPUT->header();
    echo $OUTPUT->heading("Activity upload preview");
    $table = new html_table();
    $table->id = "uupreview";
    $table->attributes['class'] = 'generaltable';
    $table->tablealign = 'center';
    $table->summary = get_string('uploaduserspreview', 'tool_uploaduser');
    $table->head = array();
    $table->data = $data;
    $table->head[] = "Line number";
    foreach ($filecolumns as $column) {
        $table->head[] = $column;
    }
    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
    $bulkurl = 'contentbulkupload.php';
    echo $OUTPUT->continue_button($bulkurl.'?iid='.$iid);
}
if ($flag) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('Contentheading' , 'tool_uploadcontent'));
    tool_uploadcontent_print_tabs($selected, $inactive, $activated);
    $mform1->display();
}
echo $OUTPUT->footer();