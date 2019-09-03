<?php
	$template = new \Content\Template\Shell();

	$content = 'This is a test of the ${COMPANY} test ${SYSTEM.NAME}.  It will ${SYSTEM.DESCRIPTION} all day long! {But not this piece} or this};';

	$template->content($content);

	$template->addParam('COMPANY',"Porkchop");
	$template->addParam('SYSTEM.NAME',"templater");
	$template->addParam('SYSTEM.DESCRIPTION',"process templates");

	print "Input: <hr><pre>$content</pre><hr>";
	print "Output: <hr><pre>".$template->output()."</pre><hr>";
	if ($template->error()) {
		print "Error: ".$template->error();
	}
?>
