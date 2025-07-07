<?php
/**
 * Script to check all classes that extend BaseModel and identify which ones
 * don't properly set a table name, which causes the "constructed w/o table name" error.
 */

// Get all PHP files in the classes directory
function getPhpFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

$classFiles = getPhpFiles(__DIR__ . '/../classes');

$problematicClasses = [];
$properClasses = [];

foreach ($classFiles as $file) {
    $content = file_get_contents($file);
    
    // Check if the file contains a class that extends BaseModel
    if (preg_match('/class\s+(\w+)\s+extends?\s+\\\?BaseModel/i', $content, $matches)) {
        $className = $matches[1];
        $namespace = '';
        
        // Extract namespace if present
        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
            $namespace = $namespaceMatches[1];
        }
        
        $fullClassName = $namespace ? $namespace . '\\' . $className : $className;
        
        // Check if the class has a constructor that sets _tableName
        if (preg_match('/public\s+function\s+__construct\s*\([^)]*\)\s*\{[^}]*\$this->_tableName\s*=/s', $content)) {
            $properClasses[] = $fullClassName;
        } else {
            $problematicClasses[] = [
                'class' => $fullClassName,
                'file' => str_replace(__DIR__ . '/../', '', $file)
            ];
        }
    }
}

echo "=== BaseModel Classes Analysis ===\n\n";

echo "Classes that properly set _tableName (" . count($properClasses) . "):\n";
foreach ($properClasses as $class) {
    echo "  ✓ $class\n";
}

echo "\nClasses that DON'T set _tableName (" . count($problematicClasses) . "):\n";
foreach ($problematicClasses as $class) {
    echo "  ✗ {$class['class']} ({$class['file']})\n";
}

echo "\n=== Summary ===\n";
echo "Total classes extending BaseModel: " . (count($properClasses) + count($problematicClasses)) . "\n";
echo "Properly configured: " . count($properClasses) . "\n";
echo "Missing table name: " . count($problematicClasses) . "\n";

if (count($problematicClasses) > 0) {
    echo "\nThese classes are likely causing the 'constructed w/o table name' error.\n";
    echo "You should either:\n";
    echo "1. Add a constructor that sets _tableName before calling parent::__construct()\n";
    echo "2. Or remove the BaseModel inheritance if they don't need database functionality\n";
} 