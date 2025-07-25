<?php
namespace Media;

class Item extends \Storage\File {
	public function resize($height, $width) {
		if (! $this->id) {
			$this->error("Image not found");
			return null;
		}
		$filelist = new FileList();
		$files = $filelist->find(array("item_id" => $this->id));
		list($file) = $files;

		$data = $file->load($file->id);
		list($owidth, $oheight) = getimagesize($data);
		$gd_image = imagecreatefromstring($data);
		
		if ($gd_image === false) {
			$this->error("Failed to create image from string");
			return null;
		}

		// Resize logic here (not implemented in this snippet)
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $gd_image, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
		return $new_image;
	}
}