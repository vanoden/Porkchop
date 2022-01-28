<script src="https://cdn.tiny.cloud/1/owxjg74mr7ujxhw9soo7iquo7iul2mclregqovcp7ophazmn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
	tinymce.init({
		selector: '#content',
        plugins: 'code',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | code'
	});
</script>
<form method="post" action="/_content/edit">
<input type="hidden" name="id" value="<?=$message->id?>"/>
<div>
    <span>Name</span>
    <input type="text" name="name" id="name" value="<?=$message->name?>"/>
    <span>Target</span>
    <input type="text" name="target" id="target" value="<?=$message->target?>"/>
    <span>Content</span>
	<textarea id="content" name="content"><?=$message->content?></textarea>
	<input type="submit" name="Submit" value="Submit"/>
</div>
</form>
