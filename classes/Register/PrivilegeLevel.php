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
		 * Check if a privilege level includes a specific level
		 * @param int $privilege_level The combined privilege level
		 * @param int $required_level The level to check for
		 * @return bool True if the privilege level includes the required level
		 */
		public static function hasLevel(int $privilege_level, int $required_level): bool {
			// Administrator has all levels
			if ($privilege_level >= self::ADMINISTRATOR) {
				return true;
			}
			
			// Check if the privilege level includes the required level
			return ($privilege_level & $required_level) === $required_level;
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
	}
