<?php
/**
 * Created by PhpStorm.
 * User: Nayan Velde
 * Date: 4/10/16
 * Time: 12:50 PM
 */
require_once("$CFG->libdir/filestorage/file_storage.php");
require_once($CFG->dirroot.'/course/modlib.php');
function my_mktempdir($dir, $prefix='') {
  global $CFG;
  if (substr($dir, -1) != '/') {
    $dir .= '/';
  }
  do {
    $path = $dir.$prefix.mt_rand(0, 9999999);
  } while (file_exists($path));
  check_dir_exists($path);
  return $path;
}

function tool_uploadactivity_print_tabs($selected, $inactive, $activated) {
  global $CFG;
  $ars_url = $CFG->wwwroot.'/admin/tool/uploadcontent/index.php';
  $arc_url = $CFG->wwwroot.'/admin/tool/uploadcontent/content.php';
  $top_row[] = new tabobject('ars', $ars_url, get_string('zipcontenttab','tool_uploadcontent'));
  $top_row[] = new tabobject('arc', $arc_url, get_string('csvcontenttab','tool_uploadcontent'));
  $tabs[] = $top_row;
  $bottom_row[] = new tabobject('arcs', '', '');
  $tabs[] = $bottom_row;
  print_tabs($tabs, $selected, $inactive, $activated);
}

function validateCategory($category_id) {
  global $DB;
  if ($DB->record_exists('course_categories', array('id' => $category_id))) {
    return TRUE;
  }
  return FALSE;
}

function validateCourse($courseid){
  global $DB;
  if ($DB->record_exists('course', array('id' => $courseid))) {
    return TRUE;
  }
  return FALSE;
}

function validateUrl($url,$filename)
{
  global $CFG;
  $url=$CFG->dataroot."/filedir".$url.$filename;
  if(file_exists($url))
  {
    return TRUE;
  }
 else FALSE;
}
function GetSection_id($course_id,$section)
{
  global $DB;
  $conditions= array(
    'course'=>$course_id,
    'section'=>$section
  );
  if(!$DB->record_exists('course_sections',$conditions )){
      $item= new stdClass();
      $item->course=$course_id;
      $item->section=$section;
      $item->summaryformat=1;
      $item->visible=1;
      $DB->insert_record('course_sections',$item,false);
  }
  $result= $DB->get_records('course_sections', $conditions);
  foreach ($result as $key=>$val);
    $id=$key;
  return $id;
}
function addResourseToCourse($fileinfo)
{
  global $DB,$CFG;
  try {
    $modinfo = new stdClass();
    $course = new stdClass();
    $course->id=$fileinfo[1];
    $modinfo->name = $fileinfo[3];
    $modinfo->help = "Help";
    $modinfo->language = 0;
    $modinfo->externalurl = '';
    $modinfo->action = "add";
    $modinfo->files = 0;
    $modinfo->display = 0;
    $modinfo->visible = 1;
    $modinfo->scorm_package_file = '';
    $modinfo->resource_module = 17;
    $modinfo->url_module = 20;
    $modinfo->course = $fileinfo[1];;
    $modinfo->coursemodule = '';
    $modinfo->section =$fileinfo[2];
    $modinfo->module = 17;
    $modinfo->modulename = "resource";
    $modinfo->instance = '';
    $modinfo->add = "resource";
    $modinfo->update = 0;
    $modinfo->return = 0;
    $modinfo->sr = 0;
    $modinfo->submitbutton2 = "Save and return to course";
    $modinfo->revision = 1;
    $modinfo->groupingid = 0;
    $modinfo->completion = 0;
    $modinfo->completionview = 0;
    $modinfo->completionexpected = 0;
    $modinfo->completiongradeitemnumber = '';
    $modinfo->conditiongradegroup = array();
    $modinfo->conditionfieldgroup = array();
    $modinfo->groupmode = 0;
    $modinfo->intro = "";
    $modinfo->introformat = 1;
    $modinfo->timemodified = time();
    $modinfo->displayoptions = 'a:1:{s:10:"printintro";i:0;}';
    $moduleinfo = add_moduleinfo($modinfo, get_course($fileinfo[1]),NULL);
    $file_record = new stdClass();
    $file_record->filepath =$fileinfo[4] ;
    $file_record->contextid = context_module::instance($moduleinfo->coursemodule)->id;
    $file_storage = get_file_storage();
    $fileinfos = array(
      'contextid' => $file_record->contextid,
      'component' => 'mod_resource',       // mod_[your-mod-name]
      'filearea' => 'content',  // arbitrary string
      'itemid' => 0,               // use a unique id in the context of the filearea and you should be safe
      'filepath' =>'/',            // virtual path
      'filename' => $fileinfo[3]
    );
     $file_storage->create_file_from_pathname($fileinfos, $CFG->dataroot.'/filedir'.$fileinfo[4].$fileinfo[3]);
  }
  catch (Exception $e) {
    print_r($e);
    die();
  }

}
