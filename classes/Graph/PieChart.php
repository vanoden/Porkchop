<?php
/** @class PieChart
 * @brief Class for generating pie charts.
 */
namespace Graph;

class PieChart Extends \BaseClass{
	private $_diameter = 100;
	private $_strokeWidth = 5;
	private $_strokeColor = 0x000000;
	private $_pieColor = 0x0000FF;

	public function __construct($parameters = []) {
		$this->_diameter = $parameters['diameter'] ?? 100;
		$this->_strokeWidth = $parameters['strokeWidth'] ?? 5;
		$this->_strokeColor = $parameters['strokeColor'] ?? 0xFF4500;
		$this->_pieColor = $parameters['pieColor'] ?? 0xFF4500;
	}

	public function setDiameter(int $diameter) {
		$this->_diameter = $diameter;
	}

	public function setStrokeWidth(int $strokeWidth) {
		$this->_strokeWidth = $strokeWidth;
	}

	public function setStrokeColor(int $strokeColor) {
		$this->_strokeColor = $strokeColor;
	}

	public function setPieColor(int $pieColor) {
		$this->_pieColor = $pieColor;
	}

	public function generate(int $target_value, int $current_value = 0, int $diameter = 0): string {
		if (!empty($diameter) && $diameter > 0) {
			$this->_diameter = $diameter;
		}
	
		// Create the image resource for the pie chart
		$image = imagecreatetruecolor($this->_diameter, $this->_diameter);

		// Set the Background Transparent
		imagesavealpha($image, true);
		$background = imagecolorallocatealpha($image, 0, 0, 0, 127);
		// Set the pie color
		$color = imagecolorallocate($image, $this->_pieColor >> 16 & 0xFF, $this->_pieColor >> 8 & 0xFF, $this->_pieColor & 0xFF);
		imagefill($image, 0, 0, $background);

		// Set the pie chart background white
		$circle_background = imagecolorallocate($image, 255, 255, 255);
		imagefilledarc($image, $this->_diameter/2, $this->_diameter/2, $this->_diameter, $this->_diameter, 0, 360, $circle_background, IMG_ARC_PIE);

		// Draw the pie chart filled arc
		$end_angle = ($current_value / $target_value) * 360;
		imagefilledarc($image, $this->_diameter/2, $this->_diameter/2, $this->_diameter, $this->_diameter, 0, $end_angle, $color, IMG_ARC_PIE);

		// Circle outline for the pie chart
		$stroke = imagecolorallocate($image, $this->_strokeColor >> 16 & 0xFF, $this->_strokeColor >> 8 & 0xFF, $this->_strokeColor & 0xFF);
		imagesetthickness($image, $this->_strokeWidth);
		imagearc(
			$image,
			$this->_diameter/2,
			$this->_diameter/2,
			$this->_diameter,
			$this->_diameter,
			0,
			360,
			$stroke
		);
		ob_start();
		imagewebp($image);
		$data = ob_get_clean();
		imagedestroy($image);
		return $data;
	}
}