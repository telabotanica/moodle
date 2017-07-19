<?php
/*
 * Mathias - créé le 2016-07-06 pour pouvoir vider le glossaire d'un coup
 * - voir entrée de menu ajoutée dans le menu d'admin -> glossaire (lib.php)
 */

require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/course/modlib.php");

//var_dump($_REQUEST);
$id = required_param('id', PARAM_INT);    // Course Module ID

$url = new moodle_url('/mod/glossary/empty.php', array('id'=>$id));
$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('glossary', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $glossary = $DB->get_record("glossary", array("id"=>$cm->instance))) {
    print_error('invalidid', 'glossary');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/glossary:empty', $context);

$stremptyglossary = get_string('emptyglossary', 'glossary');

require_once("$CFG->libdir/formslib.php");
// formulaire à l'arrache
class emptyglossary_form extends moodleform {
	//Add elements to form
	public function definition() {
		global $CFG;

		$mform = $this->_form; // Don't forget the underscore! 

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_NOTAGS);                   //Set type of element

		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('deleteallentries', 'glossary'));
		//$buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
	}
}

$PAGE->set_title($glossary->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($stremptyglossary);

echo $OUTPUT->box_start('emptyglossary generalbox');

$form = new emptyglossary_form();

if ($fromform = $form->get_data()) {
	//In this case you process validated data. $mform->get_data() returns data posted in form.
	global $DB;

	$articles = $DB->get_records_sql("SELECT id FROM glossary_entries WHERE glossaryid = " . $glossary->id);
	foreach ($articles as $article) {
		$toto1 = $DB->delete_records("glossary_alias", array("entryid" => $article->id));
	}
	$toto2 = $DB->delete_records("glossary_entries", array("glossaryid" => $glossary->id));
	echo '<p>' . get_string('allentriesdeleted', 'glossary') . ' !</p>';
} else {
	echo '<p>' . get_string('thiswilldeleteallentries', 'glossary') . '</p>';
}

$data = new stdClass();
$data->id = $id;
$form->set_data($data);
$form->display();

echo $OUTPUT->box_end();

/// Finish the page
echo $OUTPUT->footer();

