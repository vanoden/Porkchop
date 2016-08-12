<html>
	<script language="javascript">
		function selectImage(code)
		{
			window.opener.endImageSelectWizard(code);
			window.close();
			return false;
		}
	</script>
<body>
<div style="width: 600px; height: 600px; overflow: auto">
<?
	# Load Modules
	require_once(MODULES."/media/_classes/default.php");
	
	# Get Images to Display
	$_image = new MediaImage();
	$images = $_image->find();
	
	# Loop Through and Display Images
	foreach ($images as $image)
	{ ?>
<a href="javascript:void(0)" onclick="return selectImage('<?=$image->code?>');" class="mediaImageSelect" style="float: left; width: 110px; height: 110px; background-color: gray; display: block; overflow: hidden;"><img style="width: 100px;" src="/_media/api?method=downloadMediaFile&code=<?=$image->files[0]->code?>" class="mediaImageSelect" /></a>
<?	}
?>
</body>
</html>
<?	exit; ?>