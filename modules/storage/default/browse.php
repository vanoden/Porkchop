<?=$page->showAdminPageInfo()?><div class="tableBody min-tablet">
<h3>Folder: <?=$path?></h3>
<h3>Directories</h3>
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Action</div>
		<div class="tableCell">Owner</div>
		<div class="tableCell">Read Protect</div>
		<div class="tableCell">Write Protect</div>
	</div>
<?php
	if (is_array($directories)) {
		foreach ($directories as $directory) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_storage/browse?code=<?=$repository->code?>&path=<?=$directory->path?>"><?=$directory->name()?></a></div>
		<div class="tableCell"></div>
		<div class="tableCell">N/A</div>
		<div class="tableCell"><?=$directory->read_protect()?></div>
		<div class="tableCell"><?=$directory->write_protect()?></div>
	</div>
<?php	}
	}
?>
</div>

<h3>Files</h3>
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Action</div>
		<div class="tableCell">Mime-Type</div>
		<div class="tableCell">Size (Bytes)</div>
		<div class="tableCell">Date Created</div>
		<div class="tableCell">Owner</div>
		<div class="tableCell">Endpoint</div>
		<div class="tableCell">Read Protect</div>
		<div class="tableCell">Write Protect</div>
	</div>
<?php
	if (is_array($files)) {
		foreach ($files as $file) {
			if (! $file->readable($GLOBALS['_SESSION_']->customer->id)) continue;
?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_storage/file?id=<?=$file->id?>"><?=$file->name()?></a></div>
		<div class="tableCell"><a href="/_storage/downloadfile?id=<?=$file->id?>">Download</a>&nbsp;<a href="/_storage/browse?method=deleteFile&file_id=<?=$file->id?>&code=<?=$repository->code?>&path=<?=$path?>">Delete</a></div>
		<div class="tableCell"><?=$file->mime_type?></div>
		<div class="tableCell"><?=$file->size?></div>
		<div class="tableCell"><?=$file->date_created?></div>
		<div class="tableCell"><?=$file->owner()->full_name()?></div>
		<div class="tableCell"><?=$file->endpoint?></div>
		<div class="tableCell"><?=$file->read_protect?></div>
		<div class="tableCell"><?=$file->write_protect?></div>
	</div>
<?php		} 
		}
?>
</div>
<?php	if ($repository->id) { ?>
<form name="repoUpload" action="/_storage/file" method="post" enctype="multipart/form-data">
	<div class="container">
		<h3>Upload File</h3>
		<input type="hidden" name="repository_id" value="<?=$repository->id?>" />
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<input type="hidden" name="path" value="<?=$path?>" />
		<input type="file" name="uploadFile" />
		<input type="submit" name="btn_submit" class="button" value="Upload" />
	</div>
</form>
<?php	} ?>
