<?
	namespace Cache;
	
	class NoCache {
		public function __construct($key) {
		
		}
		
		public function set($value) {
			return null;
		}
		
		public function delete() {
			return null;
		}
		
		public function get() {
			return null;
		}
	}
?>