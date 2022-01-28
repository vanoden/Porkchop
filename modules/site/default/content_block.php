<style>
	#contentArea {
		display: block;
		clear: both;
	}
	div.columns {
		display: inline-block;
		width: 350px;
		vertical-align: top;
	}
	span.lefty {
		display: inline-block;
		width: 100px;
	}
	input.lefty {
		display: inline;
		clear: right;
		width: 200px;
	}
</style>
<script src="https://cdn.tiny.cloud/1/owxjg74mr7ujxhw9soo7iquo7iul2mclregqovcp7ophazmn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
	tinymce.init({
		selector: '#content',
        plugins: 'code',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | code'
	});
</script>
<form method="post" action="/_site/content_block">
<input type="hidden" name="id" value="<?=$message->id?>"/>
<div class="columns">
    <span class="lefty">Name</span>
    <input class="value input lefty" type="text" name="name" id="name" value="<?=$message->name?>"/><br>
    <span class="lefty">Target</span>
    <input class="value input lefty" type="text" name="target" id="target" value="<?=$message->target?>"/>
</div>
<div class="columns">
<?php	if ($show_add_page) { ?>
	<span>Add As A Page? </span><input type="checkbox" name="addPage" value="1" />
<?php	} else { ?>
	<a href="/_content/<?=$message->target?>">Go To Page</a><br>
	<a href="/_site/page?module=content&view=index&index=<?=$message->target?>">Edit Page Metadata</a><br>
<?php	} ?>
</div>
<div id="contentArea">
	<textarea id="content" name="content"><?=$message->content?></textarea>
	<input type="submit" name="Submit" value="Submit"/>
</div>
</form>
