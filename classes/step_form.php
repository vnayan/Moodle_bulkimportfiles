<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

class tool_uploadcontent_step_form extends moodleform{

    /**
     * The standard form definiton.
     * @return void
     */
    public function definition () {
        $mform = $this->_form;
        $mform->addElement('header', 'generalhdr', get_string('general'));
        $options = array();
        $options['accepted_types'] = array('archive');
        $mform->addElement('filepicker', 'contentfile',  get_string('zipfilelebel','tool_uploadcontent'),'size="40"', $options);
        $mform->addRule('contentfile', null, 'required');
        $this->add_action_buttons(false, get_string('upload','tool_uploadcontent'));
    }
}
