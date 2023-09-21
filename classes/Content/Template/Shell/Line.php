<?php  
    namespace Content\Template\Shell;

    class Line {
        public $_fields = array();
        public $_content;

		public function __construct($id = 0) {
            $this->_content = $content;
        }

		public function content($string) {
			$this->_content = $string;
		}

        public function addParam($field,$value) {
            $this->_fields[$field] = $value;
        }

        public function render() {
            $output = $this->_content;
            foreach ($this->_fields as $name => $value) {
                $output = preg_replace('/\$\{\-'.$name.'\}/',$value,$output);
            }
            return $output;
        }
    }
?>
