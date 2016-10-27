<?php
/**
 * Created by PhpStorm.
 * User: Nayan VeldeS
 * Date: 6/10/16
 * Time: 3:12 PM
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
$filecolumns = array('Category','Course','Topic','Filename','Fileurl');
$iid = optional_param('iid', '', PARAM_TEXT);
$PAGE->set_url(new moodle_url('/tool/uploadcontent/contentbulkupload.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title('CSV upload');
require_login();
echo $OUTPUT->header();
echo $OUTPUT->heading("Activity upload status");
if($iid!='') {
  $cir = new csv_import_reader($iid, 'uploadcontent');
  $cir->init();
  $linenum=1;
  while ($fields = $cir->next()) {
    $linenum++;
    $rowcols = array();
    $rowcols['line'] = $linenum;
    foreach ($fields as $key => $field) {
      $rowcols[] = s(trim($field));
    }
    if(validateCategory($rowcols[0]))
    {
      if(validateCourse($rowcols[1])) {
          if(validateUrl($rowcols[4],$rowcols[3]))
          {
            addResourseToCourse($rowcols);
          }else
          {
            $rowcols[]="Invalid  url";
            $data[] = $rowcols;
          }

        }else
        {
          $rowcols[]="Invalid course";
          $data[] = $rowcols;
        }
    }
    else {
      $rowcols[]="Invalid category";
      $data[] = $rowcols;
    }
  }

if(!empty($data)) {
  $table = new html_table();
  $table->id = "uupreview";
  $table->attributes['class'] = 'generaltable';
  $table->tablealign = 'center';
  $table->summary = get_string('uploadcontentspreview', 'tool_uploadcontent');
  $table->head = array();
  $table->data = $data;
  $table->head[] = "Line number";
  foreach ($filecolumns as $column) {
    $table->head[] = $column;
  }
  $table->head[] = get_string('status');
  echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
}
  echo html_writer::start_span('status') . 'Your resource is successfully updated in respective details. ' . html_writer::link(new moodle_url('/admin/tool/uploadcontent/content.php'),'Click').' Here to go content bulk upload page.'.html_writer::end_span() ;
  $temppath=explode('/' ,$rowcols[4]);
  $path=$CFG->dataroot."/filedir".$temppath[0]."/".$temppath[1]."/".$temppath[2]."/";
  exec("rm -rf {$path}");
}
echo $OUTPUT->footer();
