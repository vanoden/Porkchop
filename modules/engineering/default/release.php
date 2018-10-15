<div>
<form name="release_form" action="/_engineering/release" method="post">
<input type="hidden" name="release_id" value="<?=$release->id?>" />
<div class="breadcrumbs">
<a class="breadcrumb" href="/_engineering/home">Engineering</a>
<a class="breadcrumb" href="/_engineering/releases">Releases</a>
</div>
<h2>Engineering Release</h2>
	
<!--	Error Checking -->
<?	if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<!--	END Error Checking -->	
	
	
<!--	START First Table -->
	<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Code</div>
		<div class="tableCell" style="width: 40%;">Title</div>
		<div class="tableCell" style="width: 20%;">Status</div>
		<div class="tableCell" style="width: 20%;"></div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<input type="text" name="code" class="value wide_100per" value="<?=$form['code']?>" />
		</div>
		<div class="tableCell">
			<input type="text" name="title" class="value wide_100per" value="<?=$form['title']?>" />
		</div>
		<div class="tableCell">
			<select name="status" class="value wide_100per">
		<option value="new"<? if ($form['status'] == "NEW") print " selected"; ?>>New</option>
		<option value="testing"<? if ($form['status'] == "TESTING") print " selected"; ?>>Testing</option>
		<option value="released"<? if ($form['status'] == "RELEASED") print " selected"; ?>>Released</option>
	</select>
		</div>
		<div class="tableCell"><!-- empty cell -->
		</div>
	</div>
</div>
<!--	END First Table -->
	
	
<!--	START Second Table -->
<div class="tableBody half marginTop_20">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 50%;">Date Scheduled</div>
		<div class="tableCell" style="width: 50%;">Date Released</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<input type="text" name="date_scheduled" class="value wide_100per" value="<?=$form['date_scheduled']?>" />
		</div>
		<div class="tableCell">
			<input type="text" name="date_released" class="value wide_100per" value="<?=$form['date_released']?>" />
		</div>
		</div>
</div>
<div class="tableBody clean min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 100%;">Description</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<textarea name="description" class="wide_100per"><?=$form['description']?></textarea>
		</div>
	</div>
</div>
<!--	END Second Table -->
	

<div class="container">
	<input type="submit" name="btn_submit" class="button" value="Submit">
</div>
	
</form>
</div>


<!--	START First Table -->
<?	if ($release->id) { ?>
	<div class="tableBody min-tablet marginTop_20">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 30%;">Title</div>
		<div class="tableCell" style="width: 30%;">Project</div>
		<div class="tableCell" style="width: 20%;">Product</div>
		<div class="tableCell" style="width: 20%;">Status</div>
	</div> <!-- end row header -->
<?	foreach ($tasks as $task) { 
		$project = $task->project();
		$product = $task->product();
?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a>
		</div>
		<div class="tableCell">
			<?=$project->title?>
		</div>
		<div class="tableCell">
			<?=$product->title?>
		</div>
		<div class="tableCell">
			<?=$task->status?>
		</div>
	</div>
<?		if (! $greenbar) $greenbar = 'greenbar';
		else $greenbar = '';
	}
?>
</div>
<!--	END First Table -->
</div>



<?	} ?>
