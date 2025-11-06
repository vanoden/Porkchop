<?php
	namespace Register;

	/**
	 * Privilege Level Helper Class
	 * 
	 * Handles multi-level privilege calculations using bitwise operations.
	 * Levels are defined as:
	 * - administrator = 7
	 * - distributor = 5
	 * - organization_manager = 3
	 * - sub_organization_manager = 2
	 * - customer = 0 (customer only)
	 */
	class PrivilegeLevel {

	// Level constants
	const CUSTOMER = 0;
	const SUB_ORGANIZATION_MANAGER = 2;
	const ORGANIZATION_MANAGER = 3;
	const DISTRIBUTOR = 5;
	const ADMINISTRATOR = 7;

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
			return inMatrix($privilege_level, $required_level);
		}

		/**
		 * Check if a privilege level includes a specific level
		 * @param int $privilege_level The combined privilege level
		 * @param int $required_level The level to check for
		 * @return bool True if the privilege level includes the required level
		 */
		public static function hasLevel(int $privilege_level, int $required_level): bool {
			if (inMatrix($privilege_level,$required_level)) {
				return true;
			}
			return false;
		}

		/**
		 * Check if a number can be expressed as a sum of valid base levels
		 * @param int $level The level to check
		 * @return bool True if the level is valid (0 - 7)
		 */
		private static function isValidLevelCombination(int $level): bool {
			if (is_numeric($level) && $level >= 0 && $level <= 7) return true;
			return false;
		}

		/**
		 * Check if a privilege level has administrator access
		 * @param int $privilege_level The privilege level to check
		 * @return bool True if administrator level
		 */
		public static function isAdministrator(int $privilege_level): bool {
			return inMatrix($privilege_level, self::ADMINISTRATOR);
		}

		/**
		 * Check if a privilege level has distributor access
		 * @param int $privilege_level The privilege level to check
		 * @return bool True if distributor level or higher
		 */
		public static function isDistributor(int $privilege_level): bool {
			return inMatrix($privilege_level, self::DISTRIBUTOR);
		}

		/**
		 * Check if a privilege level has organization manager access
		 * @param int $privilege_level The privilege level to check
		 * @return bool True if organization manager level or higher
		 */
		public static function isOrganizationManager(int $privilege_level): bool {
			return inMatrix($privilege_level, self::ORGANIZATION_MANAGER);
		}

		/**
		 * Check if a privilege level has sub-organization manager access
		 * @param int $privilege_level The privilege level to check
		 * @return bool True if sub-organization manager level or higher
		 */
		public static function isSubOrganizationManager(int $privilege_level): bool {
			return inMatrix($privilege_level, self::SUB_ORGANIZATION_MANAGER);
		}

		/**
		 * Get all level names that a privilege level includes
		 * @param int $privilege_level The privilege level to check
		 * @return array Array of level names
		 */
		public static function getIncludedLevels(int $privilege_level): array {
			$levels = [];
			$arr = byte2Matrix($privilege_level);
			foreach ($arr as $elem) {
				if (isset(self::LEVEL_NAMES[$elem])) {
					$levels[] = self::LEVEL_NAMES[$elem];
				}
			}
			return $levels;
		}

		/**
		 * Validate a privilege level
		 * @param int $level The level to validate
		 * @return bool True if valid
		 */
		public static function isValidLevel(int $level): bool {
			return is_numeric($level) && $level >= 0 && $level <= 7; // Allow for future expansion
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
