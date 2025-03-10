<?php
namespace Media;

class Image extends \Storage\File {

	public function find($parameters = array()) {
		$parameters['type'] = 'image';
		return parent::find($parameters);
	}

	/**
	 * Return image file resized to given dimensions
	 * @param mixed $height 
	 * @param mixed $width 
	 * @return null|void 
	 */
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
		$gd_image = imagecreatefromstring($width, $height);
		print_r($file);
	}
}
