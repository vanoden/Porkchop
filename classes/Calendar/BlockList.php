<?php
	namespace Calendar;

	/** @class BlockList
	 * Get an array of time periods with the greater
	 * time period through which we can look and find
	 * events within.
	 * Not extending BaseListClass because this is math, not database related
     */
	class BlockList {
		/** @method public findFrom
		 * Get blocks of time starting with provided time
		 */
		public function findFrom($start_time, $blockType = 'day') {
		}

		/** @method public findTo
		 * Get blocks of time ending with provided time
		 */
	}
