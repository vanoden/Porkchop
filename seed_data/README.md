# Database Seed Data System

This directory contains JSON files for populating development and QA databases with test data, eliminating the need to copy production data to non-production environments.

## Directory Structure

```
seed_data/
├── core/               # Core system data
│   ├── companies.json  # Test companies
│   ├── users.json      # Test user accounts  
│   └── locations.json  # Test locations
├── spectros/           # Spectros-specific data
│   ├── products.json   # Instrument catalog
│   ├── organizations.json # Customer organizations
│   └── calibrations.json  # Sample calibration records
└── common/             # Shared/common data
    ├── shipping_vendors.json # Shipping companies
    └── sensor_models.json    # Sensor model definitions
```

## Usage

### Accessing the Population Tool
Navigate to: `/_spectros/populate_data`

### Safety Features
- **Environment Check**: Only works in non-production environments
- **Confirmation Required**: Shows confirmation page before proceeding
- **Duplicate Prevention**: Skips existing records (checks by primary key)
- **Real-time Logging**: Shows progress and any errors during population

### Test Accounts Created

| Username     | Password | Role          | Organization |
|-------------|----------|---------------|--------------|
| admin_test  | password | Administrator | Spectros     |
| manager_test| password | Manager       | Spectros     |
| customer1   | password | Customer      | ACME Corp    |
| customer2   | password | Customer      | Beta Industries |
| technician1 | password | Technician    | Spectros     |

*Note: All test passwords are hashed versions of "password"*

## Adding New Seed Data

### 1. Create JSON File
Create a new `.json` file in the appropriate subdirectory:
- `core/` - System-level data (companies, users, locations)
- `spectros/` - Business-specific data (products, calibrations)
- `common/` - Shared reference data (vendors, models)

### 2. JSON Format
Each file should contain an array of objects with the required fields for that data type:

```json
[
  {
    "field1": "value1",
    "field2": "value2",
    "status": "ACTIVE"
  }
]
```

### 3. Update Controller
If adding a new data type, update `populate_data_mc.php`:

1. Add case to `populateFromSeedData()` function
2. Create corresponding `populate[DataType]()` function
3. Implement logic to check for existing records and add new ones

### 4. Required Fields
Ensure your JSON includes all required fields for the target database table. Common required fields:
- `code` or unique identifier
- `name` or `description`
- `status` (usually "ACTIVE")

## Data Types Currently Supported

- **Companies**: Business entities in the system
- **Users**: Test user accounts with various roles
- **Organizations**: Customer organizations
- **Products**: Instrument catalog items
- **Shipping Vendors**: Shipping company configurations
- **Sensor Models**: Sensor type definitions

## Environment Configuration

Add this to your config file to enable the population tool:
```php
$_config->environment = 'development'; // or 'qa', 'staging'
```

**Important**: Never set environment to 'production' - the tool will refuse to run.

## Best Practices

1. **Consistent Naming**: Use descriptive codes/names that clearly indicate test data
2. **Realistic Data**: Use plausible but obviously fake data (e.g., "ACME Corporation")
3. **Version Control**: Keep seed data files in version control
4. **Documentation**: Update this README when adding new data types
5. **Cleanup**: Provide scripts to clean up test data if needed

## Troubleshooting

### "Environment not allowed" Error
- Check that `$_config->environment` is set to 'development', 'qa', or 'staging'
- Never run this in production

### "Seed data directory not found" Error  
- Ensure the `seed_data/` directory exists in your project root
- Check file permissions

### JSON Parse Errors
- Validate JSON syntax using a JSON validator
- Common issues: trailing commas, unescaped quotes, missing brackets

### Database Errors
- Check that the target tables exist (run upgrade first if needed)
- Verify required fields are included in your JSON
- Check for foreign key constraints

## Security Notes

- Test passwords are intentionally weak and hashed
- Never include real customer data
- Use fake email addresses and contact information
- Clearly mark all data as test/development data

## Related Files

- `modules/spectros/default/populate_data_mc.php` - Main controller
- `config/config.php` - Environment configuration
- `modules/spectros/default/upgrade_mc.php` - Schema upgrade tool (run first) 