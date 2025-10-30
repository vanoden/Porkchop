<?php
	namespace Register;

	/**
	 * Privilege Level Helper Class
	 * 
	 * Handles multi-level privilege calculations using bitwise operations.
	 * Levels are defined as:
	 * - administrator = 63 (all levels)
	 * - distributor = 15 (distributor + organization + sub-organization + customer)
	 * - organization_manager = 7 (organization + sub-organization + customer)
	 * - sub_organization_manager = 3 (sub-organization + customer)
	 * - customer = 0 (customer only)
	 */
	class PrivilegeLevel {

	// Level constants
	const CUSTOMER = 0;
	const SUB_ORGANIZATION_MANAGER = 3;
	const ORGANIZATION_MANAGER = 7;
	const DISTRIBUTOR = 15;
	const ADMINISTRATOR = 63;

	// Level names for display
	const LEVEL_NAMES = [
		self::CUSTOMER => 'customer',
		self::SUB_ORGANIZATION_MANAGER => 'sub_organization_manager',
		self::ORGANIZATION_MANAGER => 'organization_manager',
		self::DISTRIBUTOR => 'distributor',
		self::ADMINISTRATOR => 'administrator'
	];

	// Level IDs by name
	const LEVEL_IDS = [
		'customer' => self::CUSTOMER,
		'sub_organization_manager' => self::SUB_ORGANIZATION_MANAGER,
		'organization_manager' => self::ORGANIZATION_MANAGER,
		'distributor' => self::DISTRIBUTOR,
		'administrator' => self::ADMINISTRATOR
	];

		/**
		 * Check if a privilege level explicitly includes a specific level (for form display)
		 * This is different from hasLevel() - it doesn't treat administrator as "includes all"
		 * It checks if the level was explicitly composed using the required level
		 * @param int $privilege_level The combined privilege level
		 * @param int $required_level The level to check for
		 * @return bool True if the privilege level explicitly includes the required level
		 */
		public static function levelIncludesExplicitly(int $privilege_level, int $required_level): bool {
			// If required level is 0 (customer), everyone has it
			if ($required_level == self::CUSTOMER) {
				return true;
			}
			
			// Check if the privilege level includes the required level
			// Try subtracting the required level - if remainder is >= 0 and valid, level is included
			if ($privilege_level < $required_level) {
				return false; // Can't have a level higher than the combined level
			}
			
			// Subtract the required level and check if remainder is valid
			$remainder = $privilege_level - $required_level;
			
			// If remainder is 0, it's an exact match
			if ($remainder == 0) {
				return true;
			}
			
			// Check if remainder can be expressed as a sum of valid base levels
			// Valid base levels: 63, 15, 7, 3, 0
			// We need to verify the remainder is a valid combination
			return self::isValidLevelCombination($remainder);
		}

		/**
		 * Check if a privilege level includes a specific level
		 * Uses addition-based arithmetic: levels are combined by adding values (e.g., 22 = 15 + 7)
		 * To check if a level is included, we subtract it and check if the remainder is valid
		 * NOTE: This treats administrator (63) as "includes all levels" for permission checking
		 * @param int $privilege_level The combined privilege level
		 * @param int $required_level The level to check for
		 * @return bool True if the privilege level includes the required level
		 */
		public static function hasLevel(int $privilege_level, int $required_level): bool {
			// Administrator has all levels (for permission checking purposes)
			if ($privilege_level >= self::ADMINISTRATOR) {
				return true;
			}
			
			// If required level is 0 (customer), everyone has it
			if ($required_level == self::CUSTOMER) {
				return true;
			}
			
			// Check if the privilege level includes the required level
			// Try subtracting the required level - if remainder is >= 0 and valid, level is included
			if ($privilege_level < $required_level) {
				return false; // Can't have a level higher than the combined level
			}
			
			// Subtract the required level and check if remainder is valid
			$remainder = $privilege_level - $required_level;
			
			// If remainder is 0, it's an exact match
			if ($remainder == 0) {
				return true;
			}
			
			// Check if remainder can be expressed as a sum of valid base levels
			// Valid base levels: 63, 15, 7, 3, 0
			// We need to verify the remainder is a valid combination
			return self::isValidLevelCombination($remainder);
		}

		/**
		 * Check if a number can be expressed as a sum of valid base levels
		 * @param int $level The level to check
		 * @return bool True if the level is valid (0 or can be expressed as sum of 3, 7, 15, 63)
		 */
		private static function isValidLevelCombination(int $level): bool {
			if ($level == 0) {
				return true; // Zero is always valid
			}
			
			// Valid base levels in descending order (excluding 0 to avoid infinite recursion)
			$valid_levels = [
				self::ADMINISTRATOR,    // 63
				self::DISTRIBUTOR,      // 15
				self::ORGANIZATION_MANAGER, // 7
				self::SUB_ORGANIZATION_MANAGER, // 3
			];
			
			// Try subtracting each valid level recursively
			// Use a depth limit to prevent infinite recursion
			return self::isValidLevelCombinationRecursive($level, $valid_levels, 0, 10);
		}
		
		/**
		 * Recursive helper to check if a level can be expressed as a sum of valid levels
		 * @param int $level The level to check
		 * @param array $valid_levels Array of valid base levels (in descending order)
		 * @param int $depth Current recursion depth
		 * @param int $max_depth Maximum recursion depth
		 * @return bool True if the level is valid
		 */
		private static function isValidLevelCombinationRecursive(int $level, array $valid_levels, int $depth, int $max_depth): bool {
			// Prevent infinite recursion
			if ($depth >= $max_depth) {
				return false;
			}
			
			if ($level == 0) {
				return true; // Zero is always valid
			}
			
			// Try subtracting each valid level (but only use each level once per path)
			foreach ($valid_levels as $base_level) {
				if ($base_level > $level) {
					continue; // Can't subtract a larger number
				}
				
				if ($base_level == $level) {
					return true; // Exact match
				}
				
				$remainder = $level - $base_level;
				if ($remainder > 0 && $remainder < $level) { // Ensure we're making progress
					// Create a new array excluding the level we just used (to prevent using same level twice)
					// This ensures we don't use the same level multiple times
					$remaining_levels = array_filter($valid_levels, function($v) use ($base_level, $remainder) {
						return $v <= $remainder && $v != $base_level; // Only include levels <= remainder and not the one we just used
					});
					
					// Only recurse if we have remaining levels to try
					if (!empty($remaining_levels)) {
						if (self::isValidLevelCombinationRecursive($remainder, array_values($remaining_levels), $depth + 1, $max_depth)) {
							return true; // Recursive check succeeded
						}
					}
				}
			}
			
			return false;
		}

		/**
		 * Check if a privilege level has administrator access
		 * @param int $privilege_level The privilege level to check
		 * @return bool True if administrator level
		 */
		public static function isAdministrator(int $privilege_level): bool {
			return $privilege_level >= self::ADMINISTRATOR;
		}

		/**
		 * Check if a privilege level has distributor access
		 * @param int $privilege_level The privilege level to check
		 * @return bool True if distributor level or higher
		 */
		public static function isDistributor(int $privilege_level): bool {
			return $privilege_level >= self::DISTRIBUTOR;
		}

		/**
		 * Check if a privilege level has organization manager access
		 * @param int $privilege_level The privilege level to check
		 * @return bool True if organization manager level or higher
		 */
		public static function isOrganizationManager(int $privilege_level): bool {
			return $privilege_level >= self::ORGANIZATION_MANAGER;
		}

		/**
		 * Check if a privilege level has sub-organization manager access
		 * @param int $privilege_level The privilege level to check
		 * @return bool True if sub-organization manager level or higher
		 */
		public static function isSubOrganizationManager(int $privilege_level): bool {
			return $privilege_level >= self::SUB_ORGANIZATION_MANAGER;
		}

		/**
		 * Get the highest level name for a privilege level
		 * @param int $privilege_level The privilege level to check
		 * @return string The name of the highest level
		 */
		public static function getHighestLevelName(int $privilege_level): string {
			if ($privilege_level >= self::ADMINISTRATOR) {
				return self::LEVEL_NAMES[self::ADMINISTRATOR];
			}
			if ($privilege_level >= self::DISTRIBUTOR) {
				return self::LEVEL_NAMES[self::DISTRIBUTOR];
			}
			if ($privilege_level >= self::ORGANIZATION_MANAGER) {
				return self::LEVEL_NAMES[self::ORGANIZATION_MANAGER];
			}
			if ($privilege_level >= self::SUB_ORGANIZATION_MANAGER) {
				return self::LEVEL_NAMES[self::SUB_ORGANIZATION_MANAGER];
			}
			return self::LEVEL_NAMES[self::CUSTOMER];
		}

		/**
		 * Get all level names that a privilege level includes
		 * @param int $privilege_level The privilege level to check
		 * @return array Array of level names
		 */
		public static function getIncludedLevels(int $privilege_level): array {
			$levels = [];
			
			if ($privilege_level >= self::ADMINISTRATOR) {
				$levels[] = self::LEVEL_NAMES[self::ADMINISTRATOR];
			} elseif ($privilege_level >= self::DISTRIBUTOR) {
				$levels[] = self::LEVEL_NAMES[self::DISTRIBUTOR];
			} elseif ($privilege_level >= self::ORGANIZATION_MANAGER) {
				$levels[] = self::LEVEL_NAMES[self::ORGANIZATION_MANAGER];
			} elseif ($privilege_level >= self::SUB_ORGANIZATION_MANAGER) {
				$levels[] = self::LEVEL_NAMES[self::SUB_ORGANIZATION_MANAGER];
			} else {
				$levels[] = self::LEVEL_NAMES[self::CUSTOMER];
			}
			
			return $levels;
		}

		/**
		 * Combine multiple privilege levels
		 * @param array $levels Array of privilege levels to combine
		 * @return int Combined privilege level
		 */
		public static function combineLevels(array $levels): int {
			$combined = 0;
			foreach ($levels as $level) {
				$combined |= $level;
			}
			return $combined;
		}

		/**
		 * Validate a privilege level
		 * @param int $level The level to validate
		 * @return bool True if valid
		 */
		public static function isValidLevel(int $level): bool {
			return $level >= 0 && $level <= 127; // Allow for future expansion
		}

		/**
		 * Get privilege level name by ID
		 * @param int $id The privilege level ID
		 * @return string|null The privilege level name or null if not found
		 */
		public static function privilegeName(int $id): ?string {
			return self::LEVEL_NAMES[$id] ?? null;
		}

		/**
		 * Get privilege level ID by name
		 * @param string $name The privilege level name
		 * @return int|null The privilege level ID or null if not found
		 */
		public static function privilegeId(string $name): ?int {
			return self::LEVEL_IDS[$name] ?? null;
		}

		/**
		 * Validate privilege level name
		 * @param string $name The privilege level name to validate
		 * @return bool True if valid
		 */
		public static function validPrivilegeName(string $name): bool {
			return isset(self::LEVEL_IDS[$name]);
		}

		/**
		 * Convert privilege level to integer
		 * @param mixed $level The privilege level (string name or int ID)
		 * @return int|null The privilege level ID or null if invalid
		 */
		public static function convertLevelToInt($level): ?int {
			if (is_int($level)) {
				return $level;
			}
			
			if (is_string($level)) {
				return self::privilegeId($level);
			}
			
			return null;
		}
	}
