<html>
<head>
	<title>Text Editor</title>
	
	<script type="text/javascript" src="/js/content.api.js"></script>
	
	<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
	<script>
		tinymce.init({
			selector: '#contentEditTextArea'
		});
	</script>
<!--	<script type="text/javascript" src="/js/textEditor.js"></script> -->
	<script type="text/javascript">
		var messageID = <?=$_REQUEST["id"]?>;
		function saveContent()
		{
			var content = CKEDITOR.instances.contentEditTextArea.getData();
			updateMessageContent(messageID,content);
			window.close();
		}
		function previewContent()
		{
			var content = CKEDITOR.instances.contentEditTextArea.getData();
			var origin = document.forms[0].origin.value;
			var originElem = window.opener.document.getElementById('r7_widget['+origin+']');
			originElem.innerHTML = content;
		}
		function cancel()
		{
			var content = document.getElementById('contentEditTextArea').value;
			var origin = document.forms[0].origin.value;
			var originElem = window.opener.document.getElementById('r7_widget['+origin+']');
			originElem.innerHTML = content;
			window.close();
		}		
	</script>
</head>
<body onload="_contentEdit(<?=$_REQUEST["id"]?>)">
<form name="textEditorForm" method="post">
<input type="hidden" name="object" value="<?=$_REQUEST["object"]?>" />
<input type="hidden" name="id" value="<?=$_REQUEST["id"]?>" />
<input type="hidden" name="origin" value="<?=$_REQUEST["origin"]?>" />
<textarea id="contentEditTextArea" class="textarea-width-100 textarea-height-100"></textarea>
<div class="text-align-right">
	<input type="button" name="operation" value="Cancel" class="button" onclick="cancel();"/>
	<input type="button" name="operation" value="Preview" class="button" onclick="previewContent();"/>
	<input type="button" name="operation" value="Save" class="button" onclick="saveContent();"/>
</div>
</form>
</body>
</html>
<?php	exit; ?>
