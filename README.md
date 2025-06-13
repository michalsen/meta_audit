# Meta Audit

A Drupal module for auditing and analyzing meta tags across your content. This module provides administrators with insights into how meta tags are configured and sourced for different content types.

## Description

The Meta Audit module helps site administrators understand the meta tag configuration across their content by providing:

- A comprehensive view of meta tags for all nodes of a specific content type
- Source tracking for each meta tag (node-specific, content type default, or global default)
- Easy identification of content with missing or inconsistent meta tags
- A simple interface for auditing meta tag coverage

## Features

- **Content Type Selection**: Choose any content type to audit its meta tags
- **Comprehensive Meta Tag Analysis**: View all meta tags for each node
- **Source Identification**: See whether meta tags come from:
  - Node-specific overrides
  - Content type defaults
  - Global defaults
- **Linked Node Titles**: Direct access to edit nodes from the audit results
- **Clean Table Display**: Easy-to-read tabular format for audit results

## Requirements

- Drupal 10 or 11
- Metatag module (dependency)

## Installation

1. Download and place the module in your `modules/custom` directory
2. Enable the module using one of these methods:
   - Via Drush: `drush en meta_audit`
   - Via Admin UI: Navigate to Extend (/admin/modules) and enable "Meta Audit"
3. Clear cache: `drush cr`

## Usage

1. Navigate to **Administration » Meta Audit** (`/admin/meta_audit`)
2. Select a content type from the dropdown menu
3. Click **Submit** to generate the audit report
4. Review the results table which shows:
   - **Node Title**: Clickable links to view/edit each node
   - **Meta Tags**: List of meta tag types present for each node
   - **Tag Sources**: Where each meta tag originates from

## Permissions

The module requires the **"Administer site configuration"** permission to access the audit interface.

## Technical Details

### Services
- `meta_audit.meta_audit_service`: Core service for meta tag analysis

### Classes
- `MetaAuditService`: Main service class handling meta tag retrieval and analysis
- `MetaAuditForm`: Form class for the content type selection interface
- `MetaAuditController`: Controller for handling requests

### Meta Tag Source Priority
The module checks meta tags in the following order:
1. Entity-specific meta tags (highest priority)
2. Meta tag field values (if `field_meta_tags` exists)
3. Content type default meta tags
4. Global default meta tags (lowest priority)

## Development

### Testing
The module includes comprehensive tests:
- Unit tests for the service and controller classes
- Functional tests for the audit interface

Run tests with:
```bash
./vendor/bin/phpunit modules/custom/meta_audit/tests/
```

### Contributing
This module follows Drupal coding standards and includes:
- Strict type declarations
- Proper dependency injection
- Comprehensive documentation
- Full test coverage

## Troubleshooting

**Issue**: No meta tags showing for content
- **Solution**: Ensure the Metatag module is properly configured and that meta tags are set up for your content types

**Issue**: Access denied to audit page
- **Solution**: Ensure your user account has the "Administer site configuration" permission

**Issue**: Content type not appearing in dropdown
- **Solution**: Verify that the content type exists and is properly configured

## Author

Eric Michalsen <eric.michalsen@gmail.com>

## License

GPL-2.0-or-later

## Links

- **Official Project**: https://www.drupal.org/project/meta_audit
