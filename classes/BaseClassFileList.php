<?php
	/** @class BaseClassFileList
	 * Base class for managing lists of classes defined in files. Provides functionality for scanning directories, instantiating classes, and retrieving class instances based on file structure. This class can be extended to
	 */
	class BaseClassFileList Extends \BaseListClass {
		protected $directoryPath;
		protected $namespacePrefix;

		public function findAdvanced($parameters = [], $advanced = [], $controls = []): array {
			$dir = new DirectoryIterator(CLASS_PATH . $this->directoryPath);

			$classes = [];
			foreach ($dir as $fileinfo) {
				if ($fileinfo->isFile() && $fileinfo->getExtension() === 'php') {
					$className = $this->namespacePrefix . '\\' . $fileinfo->getBasename('.php');
					if (class_exists($className)) {
						$typeInstance = new $className();
						$classes[] = $typeInstance;
					}
				}
			}
			return $classes;
		}
	}