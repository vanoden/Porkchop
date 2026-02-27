<?php
	/** @class Graph\Line
	 * Class for creating line graphs using PHP-GD
	 * @package Graph
	 */
	namespace Graph;

	class Line extends \BaseClass {
		public ?int $height = null;
		public ?int $width = null;
		public ?int $padding_left = 0;
		public ?int $padding_top = 0;
		public ?int $padding_right = 0;
		public ?int $padding_bottom = 0;
		public ?int $border_left = 0;
		public ?int $border_top = 0;
		public ?int $border_right = 0;
		public ?int $border_bottom = 0;
		public ?float $vertical_min = null;
		public ?float $vertical_max = null;
		public ?float $horizontal_min = null;
		public ?float $horizontal_max = null;
		public ?float $vertical_scale = 1;
		public ?float $horizontal_scale = 1;
		public ?array $data = null;
		public ?bool $force_vertical_min_zero = true;
		public ?bool $force_horizontal_min_zero = false;
		private $_image = null;
		private $_axis_label_x = 'X';
		private $_axis_label_y = 'Y';
		private $_color_background = null;
		private $_color_axis = null;
		private $_color_grid = null;
		private $_color_foreground = null;
		private $_color_label = null;
		public ?string $output_path = null;

		private $_color_matrix = array();
		private $_line_colors = array();

		/** @method __construct(height, width)
		 * Constructor for the Line graph class.
		 * @param height The height of the graph.
		 * @param width The width of the graph.
		 */
		public function __construct($dimensions) {
			$this->height = $dimensions['height'];
			$this->width = $dimensions['width'];

			// Defaults
			$this->padding_left = 10;
			$this->padding_top = 10;
			$this->padding_right = 10;
			$this->padding_bottom = 10;

			// Create Image
			$this->_image = imagecreatetruecolor($this->width, $this->height);
			
			// Image Background (gotta do it now!)
			$white = imagecolorallocate($this->_image, 255, 255, 255);
			imagefill($this->_image, 0, 0, $white);

			// Initialize Color Matrix
			$this->_color_matrix = array(
				'white' => imagecolorallocate($this->_image, 255, 255, 255),
				'black' => imagecolorallocate($this->_image, 0, 0, 0),
				'red' => imagecolorallocate($this->_image, 255, 0, 0),
				'green' => imagecolorallocate($this->_image, 0, 255, 0),
				'blue' => imagecolorallocate($this->_image, 0, 0, 255),
				'cyan' => imagecolorallocate($this->_image, 0, 255, 255),
				'magenta' => imagecolorallocate($this->_image, 255, 0, 255),
				'yellow' => imagecolorallocate($this->_image, 255, 255, 0),
				'gray' => imagecolorallocate($this->_image, 128, 128, 128),
				'purple' => imagecolorallocate($this->_image, 128, 0, 128),
				'orange' => imagecolorallocate($this->_image, 255, 165, 0),
				'light_red' => imagecolorallocate($this->_image, 255, 127, 127),
				'light_green' => imagecolorallocate($this->_image, 127, 255, 127),
				'light_blue' => imagecolorallocate($this->_image, 127, 127, 255),
				'light_cyan' => imagecolorallocate($this->_image, 127, 255, 255),
				'light_magenta' => imagecolorallocate($this->_image, 255, 127, 255),
				'light_yellow' => imagecolorallocate($this->_image, 255, 255, 127),
				'light_gray' => imagecolorallocate($this->_image, 211, 211, 211),
				'olive' => imagecolorallocate($this->_image, 128, 128, 0),
				'teal' => imagecolorallocate($this->_image, 0, 128, 128),
				'egg_shell' => imagecolorallocate($this->_image, 240, 234, 214),
				'bright_gray' => imagecolorallocate($this->_image,230, 230, 230),
				'bright_red' => imagecolorallocate($this->_image, 255, 100, 100),
				'bright_green' => imagecolorallocate($this->_image, 100, 255, 100),
				'bright_blue' => imagecolorallocate($this->_image, 100, 100, 255)
			);

			$this->_color_background = $this->_color_matrix['white'];
			$this->_color_axis = $this->_color_matrix['black'];
			$this->_color_grid = $this->_color_matrix['gray'];
			$this->_color_foreground = $this->_color_matrix['egg_shell'];
			$this->_color_label = $this->_color_matrix['black'];

			$this->_line_colors = array(
				$this->_color_matrix['red'],
				$this->_color_matrix['green'],
				$this->_color_matrix['blue'],
				$this->_color_matrix['magenta'],
				$this->_color_matrix['cyan'],
				$this->_color_matrix['orange'],
				$this->_color_matrix['purple'],
				$this->_color_matrix['olive'],
				$this->_color_matrix['teal'],
				$this->_color_matrix['light_red'],
				$this->_color_matrix['light_green'],
				$this->_color_matrix['light_blue'],
				$this->_color_matrix['light_cyan'],
				$this->_color_matrix['light_magenta'],
				$this->_color_matrix['light_gray'],
			);
		}

		/** @method graphHeight()
		 * Returns the height of the graph area (excluding padding and borders).
		 * @return The height of the graph area.
		 */
		public function graphHeight(): int {
			return $this->height - $this->padding_top - $this->padding_bottom - $this->border_top - $this->border_bottom;
		}

		/** @method graphWidth()
		 * Returns the width of the graph area (excluding padding and borders).
		 * @return The width of the graph area.
		 */
		public function graphWidth(): int {
			return $this->width - $this->padding_left - $this->padding_right - $this->border_left - $this->border_right;
		}

		/** @method output(output_path)
		 * Sets the output path for the rendered graph.
		 * @param output_path The path to output the rendered graph to.
		 */
		public function output($output_path): void {
			$this->output_path = $output_path;
		}

		/** @method addDataSet(data)
		 * Adds a dataset to the graph.
		 * @param data An array of x,y data points to add to the graph.
		 */
		public function addDataSet($data): void {
			// How many datasets do we have? If this is the first dataset, initialize the data array. Otherwise, add to it.
			if ($this->data === null) {
				$this->data = array();
				$dataset_count = 0;
			}
			else {
				$dataset_count = count($this->data);
			}

			// Add a dataset for the graph. Data should be an array of x,y data points to plot on the graph.	
			$this->data[$dataset_count] = $data;

			// Loop Through Datasets and Update Vertical and Horizontal Minimums, and Maximums
			foreach ($this->data as $dataset) {
				foreach ($dataset as $x => $y) {
					if ($this->vertical_min === null || $y < $this->vertical_min) {
						$this->vertical_min = $y;
					}
					if ($this->vertical_max === null || $y > $this->vertical_max) {
						$this->vertical_max = $y;
					}

					if ($this->horizontal_min === null || $x < $this->horizontal_min) {
						$this->horizontal_min = $x;
					}
					if ($this->horizontal_max === null || $x > $this->horizontal_max) {
						$this->horizontal_max = $x;
					}
				}
			}
		}

		/** @method addPoint(dataset, x, y)
		 * Adds a single point to the specified dataset.
		 * @param dataset The index of the dataset to add the point to.
		 * @param x The x value of the point to add.
		 * @param y The y value of the point to add.
		 */
		public function addPoint($dataset, $x, $y): void {
			if ($this->data === null) {
				$this->data = array();
			}

			if (!isset($this->data[$dataset])) {
				$this->data[$dataset] = array();
			}
			$this->data[$dataset][$x] = $y;

			if ($this->vertical_min === null || $y < $this->vertical_min) {
				$this->vertical_min = $y;
			}
			if ($this->vertical_max === null || $y > $this->vertical_max) {
				$this->vertical_max = $y;
			}
			if ($this->horizontal_min === null || $x < $this->horizontal_min) {
				$this->horizontal_min = $x;
			}
			if ($this->horizontal_max === null || $x > $this->horizontal_max) {
				$this->horizontal_max = $x;
			}
		}

		/** @method public horizontalRange()
		 * Returns the horizontal range of the graph.
		 * @return The horizontal range of the graph.
		 */
		public function horizontalRange(): int|float {
			if ($this->force_horizontal_min_zero) {
				$this->horizontal_min = 0;
			}
			return $this->horizontal_max - $this->horizontal_min;
		}

		/** @method public verticalRange()
		 * Returns the vertical range of the graph.
		 * @return The vertical range of the graph.
		 */
		public function verticalRange(): int|float {
			if ($this->force_vertical_min_zero) {
				$this->vertical_min = 0;
			}
			return $this->vertical_max - $this->vertical_min;
		}

		/** @method public axisLabel(direction, value)
		 * Sets the axis label for the specified direction.
		 * @param direction The direction of the axis label ('x' or 'y').
		 * @param value The value of the axis label.
		*/
		public function axisLabel($direction, $value) {
			if ($direction === 'x') {
				$this->_axis_label_x = $value;
			}
			else if ($direction === 'y') {
				$this->_axis_label_y = $value;
			}
		}

		/** @method public setPadding(side, value)
		 * Sets the padding for the specified side of the graph.
		 * @param side The side of the graph to set the padding for ('left', 'top', 'right', 'bottom').
		 * @param value The value of the padding to set
		 */
		public function setPadding($side, $value): void {
			if ($side === 'left') {
				$this->padding_left = $value;
			}
			else if ($side === 'top') {
				$this->padding_top = $value;
			}
			else if ($side === 'right') {
				$this->padding_right = $value;
			}
			else if ($side === 'bottom') {
				$this->padding_bottom = $value;
			}
		}

		/** @method public setBorder(side, value)
		 * Sets the border for the specified side of the graph.
		 * @param side The side of the graph to set the border for ('left', 'top', 'right', 'bottom').
		 * @param value The value of the border to set
		 */
		public function setBorder($side, $value): void {
			if ($side === 'left') {
				$this->border_left = $value;
			}
			else if ($side === 'top') {
				$this->border_top = $value;
			}
			else if ($side === 'right') {
				$this->border_right = $value;
			}
			else if ($side === 'bottom') {
				$this->border_bottom = $value;
			}
		}

		/** @method public setColor(type, value)
		 * Sets the color for the specified type.
		 * @param type The type of color to set ('background', 'axis', 'grid', 'foreground', 'label').
		 * @param value The value of the color to set (hex or RGB array).
		 */
		public function setColor($type, $value): void {
			// Is the value listed in the color matrix? If so, use the color from the matrix. Otherwise, assume it's an RGB array and create a new color.
			if (is_string($value) && isset($this->_color_matrix[$value])) {
				$value = $this->_color_matrix[$value];
			}
			else if (is_array($value) && count($value) === 3) {
				$value = imagecolorallocate($this->_image, $value[0], $value[1], $value[2]);
			}

			if ($type === 'background') {
				$this->_color_background = $value;
			}
			else if ($type === 'axis') {
				$this->_color_axis = $value;
			}
			else if ($type === 'grid') {
				$this->_color_grid = $value;
			}
			else if ($type === 'foreground') {
				$this->_color_foreground = $value;
			}
			else if ($type === 'label') {
				$this->_color_label = $value;
			}
		}

		/** @method public build()
		 * Alias for render() method.
		 */
		public function build() {
			return $this->render();
		}

		/** @method public render()
		 * Renders the graph.
		 */
		public function render(): ?string {
			// Vertical and Horizontal Scales
			app_log("Vertical Range: ".$this->verticalRange());
			app_log("Horizontal Range: ".$this->horizontalRange());
			$this->vertical_scale = (float) $this->verticalRange() / (float) $this->graphHeight();
			$this->horizontal_scale = (float) $this->horizontalRange() / (float) $this->graphWidth();

			// Calculate Grid Spacing aligned to "nice" numbers
			// Use the first digit of the range to determine the spacing
			$vertical_spacing = pow(10, floor(log10((float) $this->verticalRange())));
			$horizontal_spacing = pow(10, floor(log10((float) $this->horizontalRange())));
			while ((int)$this->graphWidth() / $horizontal_spacing < 5) {
				$horizontal_spacing -= 60;
			}

			// Graph area background
			imagefilledrectangle($this->_image, $this->padding_left + $this->border_left, $this->padding_top + $this->border_top, $this->width - $this->padding_right - $this->border_right, $this->height - $this->padding_bottom - $this->border_bottom, $this->_color_foreground);

			// X and Y Axis
			imageline($this->_image, $this->padding_left + $this->border_left, $this->padding_top + $this->border_top, $this->padding_left + $this->border_left, $this->height - $this->padding_bottom - $this->border_bottom, $this->_color_axis);
			imageline($this->_image, $this->padding_left + $this->border_left, $this->height - $this->padding_bottom - $this->border_bottom, $this->width - $this->padding_right - $this->border_right, $this->height - $this->padding_bottom - $this->border_bottom, $this->_color_axis);

			// X and Y Axis Labels
			imagestring($this->_image, 4, $this->width / 2 - (strlen($this->_axis_label_x) * 3), $this->height - $this->padding_bottom + 25, $this->_axis_label_x, $this->_color_axis);
			imagestringup($this->_image, 4, $this->padding_left - 45, $this->height / 2 + (strlen($this->_axis_label_y) * 3), $this->_axis_label_y, $this->_color_axis);

			// Grid Lines and Labels
			for ($y = ceil($this->vertical_min / $vertical_spacing) * $vertical_spacing; $y <= $this->vertical_max; $y += $vertical_spacing) {
				$plot_y = $this->height - $this->padding_bottom - (($y - $this->vertical_min) / $this->vertical_scale);
				imageline($this->_image, $this->padding_left + $this->border_left, $plot_y, $this->width - $this->padding_right - $this->border_right, $plot_y, $this->_color_grid);
				imagestring($this->_image, 2, $this->padding_left -25, $plot_y - 7, (string)$y, $this->_color_label);
			}
			for ($x = ceil($this->horizontal_min / $horizontal_spacing) * $horizontal_spacing; $x <= $this->horizontal_max; $x += $horizontal_spacing) {
				$plot_x = $this->padding_left + (($x - $this->horizontal_min) / $this->horizontal_scale);
				$string = (date('H:i', (int)$x));
				imageline($this->_image, $plot_x, $this->padding_top + $this->border_top, $plot_x, $this->height - $this->padding_bottom - $this->border_bottom, $this->_color_grid);
				imagestring($this->_image, 2, $plot_x - 10, $this->height - $this->padding_bottom + 5, $string, $this->_color_label);
			}

			// Plot Data
			$dataset_index = 0;
			foreach ($this->data as $dataset) {
				$prev_x = null;
				$prev_y = null;
				foreach ($dataset as $x => $y) {
					$plot_x = $this->padding_left + (($x - $this->horizontal_min) / $this->horizontal_scale);
					$plot_y = $this->height - $this->padding_bottom - (($y - $this->vertical_min) / $this->vertical_scale);
					if ($prev_x !== null && $prev_y !== null) {
						// Connecting Line
						imageline($this->_image, $prev_x, $prev_y, $plot_x, $plot_y, $this->_line_colors[$dataset_index % count($this->_line_colors)]);
						// Starting Data Point
						imagefilledellipse($this->_image, $prev_x, $prev_y, 4, 4, $this->_line_colors[$dataset_index % count($this->_line_colors)]);
						imageellipse($this->_image, $prev_x, $prev_y, 4, 4, imagecolorallocate($this->_image, 0, 0, 0));
						// Ending Data Point
						imagefilledellipse($this->_image, $plot_x, $plot_y, 4, 4, $this->_line_colors[$dataset_index % count($this->_line_colors)]);
						imageellipse($this->_image, $plot_x, $plot_y, 4, 4, imagecolorallocate($this->_image, 0, 0, 0));
					}
					$prev_x = $plot_x;
					$prev_y = $plot_y;
				}
				$dataset_index++;
			}

			// Output Image to Specified Path
			imagepng($this->_image, $this->output_path);
			return $this->output_path;
		}
	}