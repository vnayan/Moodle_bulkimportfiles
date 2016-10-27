<?php
/**
 * Created by PhpStorm.
 * User: Nayan Velde
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
global $DB, $CFG;
$override=true;
$selected[] = "ars";
$inactive[] = "";
$activated[] = "ars";
$PAGE->set_url(new moodle_url('/tool/uploadcontent/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
require_login();
$mform1 = new tool_uploadcontent_step_form();
if($fromform = $mform1->get_data()) {
   core_php_time_limit::raise();
   raise_memory_limit(MEMORY_EXTRA);
   $fullpath = $CFG->dataroot . '/filedir';
   $zipdir = my_mktempdir($fullpath . '/'.'upload');
   $dstfile = $zipdir . '/content.zip';
   $content = $mform1->get_file_content('contentfile');
   $name = $mform1->get_new_filename('contentfile');
   if (!$mform1->save_file('contentfile', $dstfile, TRUE)) {
	echo $OUTPUT->notification('Upload Failed');
   }
   else {
     $fp = get_file_packer('application/zip');
     $unzipresult = $fp->extract_to_pathname($dstfile, $zipdir);
     if (!$unzipresult) {
       echo $OUTPUT->notification('Upload Failed');
       @remove_dir($zipdir);
     }else{
           $csvexporter = new csv_export_writer('comma');
           $csvexporter->set_filename('bulkactivity', '.csv');
           $headers = array('Category','Course','Topic','Filename','Fileurl');
           $csvexporter->add_data($headers);
           foreach ($unzipresult as $key=>$value){
             if(strrpos($key, "/"))
               $filename=substr($key, strrpos($key, "/") + 1 );
             else
             $filename=$key;
             $fileurl=$zipdir.'/'.$key;
             $fileurl=substr($fileurl, 28);
             $fileurl=str_replace($filename,"", $fileurl);
            $record= array('','','',$filename,$fileurl);
             if($filename !='')
               $csvexporter->add_data($record);
           }
           @unlink($dstfile);
           $csvexporter->download_file();
     }
   }
 }
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('Contentheading','tool_uploadcontent'));
tool_uploadactivity_print_tabs($selected, $inactive, $activated);
$mform1->display();
echo $OUTPUT->footer();
