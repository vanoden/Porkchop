# BaseModel Table Name Error Fix

## Problem
The following error was polluting the logs:
```
core::index classes/BaseModel.php:35 13908901 3258 Class Site\AuditLog\Event constructed w/o table name!
```

This error occurred when classes that extend `BaseModel` were instantiated without properly setting the `_tableName` property.

## Root Cause
The issue was in the `BaseModel` constructor at line 35, which checked if `_tableName` was empty and logged a generic error message that didn't provide enough information to identify which child class was causing the problem.

## Solution Implemented

### 1. Enhanced Logging in BaseModel Constructor
Modified `classes/BaseModel.php` line 35 to provide detailed call stack information:

```php
if (empty($this->_tableName)) {
    $calledClass = get_called_class();
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    $callerInfo = '';
    
    // Get caller information from the backtrace
    if (isset($backtrace[1])) {
        $caller = $backtrace[1];
        $callerInfo = " called from " . 
            (isset($caller['class']) ? $caller['class'] : 'unknown class') . "::" . 
            (isset($caller['function']) ? $caller['function'] : 'unknown function') . 
            "() in " . 
            (isset($caller['file']) ? basename($caller['file']) : 'unknown file') . 
            " line " . 
            (isset($caller['line']) ? $caller['line'] : 'unknown');
    }
    
    app_log("Class " . $calledClass . " constructed w/o table name!" . $callerInfo, 'notice');
}
```

### 2. Analysis Script
Created `scripts/check_base_model_classes.php` to identify all classes that extend BaseModel and determine which ones don't properly set a table name.

## Analysis Results
The script identified:
- **140 total classes** extending BaseModel
- **98 classes** properly configured with table names
- **42 classes** missing table names

### Classes Missing Table Names
The following classes extend BaseModel but don't set `_tableName`:

1. `System` (classes/System.php)
2. `Bench\Build` (classes/Bench/Build.php)
3. `Bench\Product` (classes/Bench/Product.php)
4. `Storage\FileType` (classes/Storage/FileType.php)
5. `Storage\Directory` (classes/Storage/Directory.php)
6. `Site\Module` (classes/Site/Module.php)
7. `Site\Page\SearchBar` (classes/Site/Page/SearchBar.php)
8. `Site\Page\Pagination` (classes/Site/Page/Pagination.php)
9. `Site\Hit` (classes/Site/Hit.php)
10. `Register\Organization\Comment` (classes/Register/Organization/Comment.php)
11. `Register\Organization\Location` (classes/Register/Organization/Location.php)
12. `Register\Location` (classes/Register/Location.php)
13. `Register\Organization` (classes/Register/Organization.php)
14. `Register\Department` (classes/Register/Department.php)
15. `Register\Notification` (classes/Register/Notification.php)
16. `Network\Domain` (classes/Network/Domain.php)
17. `Network\Subnet` (classes/Network/Subnet.php)
18. `Network\NIC` (classes/Network/NIC.php)
19. `Database\RecordSet` (classes/Database/RecordSet.php)
20. `Cache\Client` (classes/Cache/Client.php)
21. `Purchase\Order\Payment` (classes/Purchase/Order/Payment.php)
22. `Purchase\Order` (classes/Purchase/Order.php)
23. `Build\Version` (classes/Build/Version.php)
24. `Build\Repository` (classes/Build/Repository.php)
25. `Build\Commit` (classes/Build/Commit.php)
26. `Build\Product` (classes/Build/Product.php)
27. `Spectros\SIMCard` (classes/Spectros/SIMCard.php)
28. `Action\Task` (classes/Action/Task.php)
29. `Action\Event` (classes/Action/Event.php)
30. `Action\Request` (classes/Action/Request.php)
31. `Action\Task\Type` (classes/Action/Task/Type.php)
32. `Action\Event\Item` (classes/Action/Event/Item.php)
33. `S4Engine\Session` (classes/S4Engine/Session.php)
34. `S4Engine\Server` (classes/S4Engine/Server.php)
35. `Email\Campaign` (classes/Email/Campaign.php)
36. `Email\Transport\Base` (classes/Email/Transport/Base.php)
37. `Support\RegistrationQueue` (classes/Support/RegistrationQueue.php)
38. `Support\Request\Note` (classes/Support/Request/Note.php)
39. `Support\ShipmentItem` (classes/Support/ShipmentItem.php)
40. `Monitor\Sensor\Status` (classes/Monitor/Sensor/Status.php)
41. `Monitor\MonitorTrend` (classes/Monitor/Trend.php)
42. `Monitor\Reading` (classes/Monitor/Reading.php)

## Next Steps
For each of the 42 problematic classes, you should either:

1. **Add a constructor that sets `_tableName`** before calling `parent::__construct()`, or
2. **Remove the BaseModel inheritance** if the class doesn't need database functionality

### Example Fix
```php
public function __construct($id = 0) {
    $this->_tableName = 'your_table_name';
    parent::__construct($id);
}
```

## Benefits
1. **Better Error Messages**: The enhanced logging now shows exactly which class is causing the issue and where it's being called from
2. **Complete Analysis**: The script provides a comprehensive list of all problematic classes
3. **Actionable Information**: Developers can now easily identify and fix the root cause of the errors

## Usage
To run the analysis script:
```bash
php scripts/check_base_model_classes.php
``` 