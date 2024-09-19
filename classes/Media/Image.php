<?php
namespace Media;

class Image extends Storage\File {

	public function find($parameters = array()) {
		$parameters['type'] = 'image';
		return parent::find($parameters);
	}

	public function resize($height, $width) {
		if (! $this->id) {
			$this->error = "Image not found";
			return null;
		}
		$filelist = new FileList();
		$files = $filelist->find(array("item_id" => $this->id));
		list($file) = $files;

		$data = $file->load($file->id);
		list($owidth, $oheight) = getimagesize($data);
		$gd_image = imagecreatefromstring($width, $height);
		$new_image = imagecreatetruecolor();
		$image_copy_resampled($new_image, $gd_image, 0, 0, 0, 0, $width, $height, $owidth, $oheight);

		print_r($file);
	}
}
