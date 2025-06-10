# Meta Audit

**⚠️ DEVELOPMENT CODE** - This is development code for a project at https://www.drupal.org/project/meta_audit

## Overview

Meta Audit is a Drupal module that provides comprehensive auditing capabilities for meta tags in your content. This module helps you analyze, monitor, and optimize the meta tag implementation across your Drupal site.

## Features

- Audit meta tags for content across your site
- Monitor meta tag compliance and completeness
- Identify missing or problematic meta tags
- Provide insights for SEO optimization

## Requirements

- Drupal 10 or 11
- Metatag module

## Installation

### Development Installation

1. Clone this repository into your Drupal modules directory:
   ```bash
   git clone [repository-url] web/modules/custom/meta_audit
   ```

2. Enable the module:
   ```bash
   drush en meta_audit
   ```

### Composer Installation (when available)

```bash
composer require drupal/meta_audit
drush en meta_audit
```

## Usage

[Usage documentation will be added as features are developed]

## Development

### Project Structure

```
meta_audit/
├── src/                    # Source code
├── tests/                  # Test files
├── meta_audit.info.yml     # Module definition
├── meta_audit.module       # Module hooks
├── meta_audit.routing.yml  # Route definitions
├── meta_audit.services.yml # Service definitions
└── README.md              # This file
```

### Contributing

This is development code. Please refer to the official Drupal.org project page for contribution guidelines: https://www.drupal.org/project/meta_audit

### Testing

[Testing instructions will be added as tests are developed]

## Support

For support, bug reports, and feature requests, please use the issue queue on the official Drupal.org project page: https://www.drupal.org/project/meta_audit

## License

This project is licensed under the GPL-2.0+ license, consistent with Drupal core.

## Maintainers

- Current maintainers: [To be updated]

---

**Note**: This is development code and should not be used in production environments without thorough testing.
