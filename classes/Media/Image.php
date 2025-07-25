<?php
namespace Media;

class Image extends \Storage\File {
	/** @method resized($height, $width)
	 * Return image file resized to given dimensions
	 * @param mixed $height 
	 * @param mixed $width 
	 * @return null|string 
	 */
	public function resized($height, $width): ?string {
		if (! $this->id) {
			$this->error("Image not found");
			return null;
		}

		$repository = $this->repository();
		$data = $repository->content($this);
		if ($this->error()) {
			return null;
		}

		// Resize Image Content
		list($owidth, $oheight) = getimagesizefromstring($data);
		$gd_image = imagecreatefromstring($data);
		if ($gd_image === false) {
			$this->error("Failed to create image from string");
			return null;
		}
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $gd_image, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
		$stream = fopen('php://memory', 'r+');
		imagejpeg($new_image, $stream);
		rewind($stream);
		$buffer = stream_get_contents($stream);
		return $buffer;
	}
}
