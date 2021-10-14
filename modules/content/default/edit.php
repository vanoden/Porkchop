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