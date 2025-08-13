# CHANGELOG

## v1.2.0 (2025-08-13)

#### New Features
- **Console Command for Log Cleanup** - Added `three-brs:404-logs:cleanup` command for automated deletion of old 404 logs
  - Supports dry-run mode for safe testing
  - Batch processing for memory-efficient deletion
  - Progress tracking with visual progress bar
  - Configurable batch size for performance optimization
  - Automated confirmation prompts for safety

- **Setono Redirect Plugin Integration** - Enhanced integration with setono/sylius-redirect-plugin
  - Direct redirect creation buttons in both aggregated and detailed log views
  - Automatic form pre-filling with URL slug and domain information
  - Smart channel detection based on domain mapping
  - Seamless workflow from 404 log identification to redirect creation

- **Improved Pagination** - Added pagination support for aggregated log view
  - 20 items per page for better performance
  - Preserves filter state during navigation
  - Semantic UI styled pagination controls
  - Shows total item counts and current page information

## v1.0.0 (2025-08-12)

#### Details
- Initial release of the project.
