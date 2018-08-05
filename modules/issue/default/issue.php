<?	if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?  }
	if ($page->success) {
?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<form action="/_issue/issue" method="post">
<div class="container">
	<div class="label" id="issue_title_label">Subject</div>
	<input name="title" class="value input" id="issue_title_value" value="<?=$issue->title?>" />
</div>
<? if ($issue->id) { ?>
<div class="container issue">
	<div class="label" id="issue_status_label">Status</div>
	<div class="value" id="issue_status_value"><?= $issue->status?></div>
</div>
<div class="container issue">
	<div class="label" id="issue_assigned_label">Assigned To</div>
	<div class="value" id="issue_assigned_value"><?	if ($issue->user_assigned_id) {
				$tech = new \Register\Customer($issue->user_assigned_id);
				print $tech->code;
			}
			else {
				print "Unassigned";
			}
		?>
	</div>
</div>
<div class="container issue">
	<div class="label" id="issue_priority_label">Priority</div>
	<select class="value input" id="issue_priority_value">
		<?	foreach ($priorities as $priority) { ?>
			<option value="<?=$priority?>"<? if ($priority == $issue->priority) print " selected"; ?>><?=$priority?></option>
		<?	} ?>
	</select>
</div>
<?	} ?>
<div class="container">
	<div class="label" id="issue_description_label">Description</div>
	<textarea class="value input" id="issue_description_value"><?=$issue->description?></textarea>
</div>
<div class="form_footer">
	<input type="submit" name="btn_submit" value="Submit" class="button" />
</div>
</form>