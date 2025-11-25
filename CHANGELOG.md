# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2025-11-25

### Changed
- Housekeeping

## [1.1.0] - 2025-11-25

### Added
- Automatic updates via GitHub releases using [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) library (v5.6)
- Plugin now checks for updates from the GitHub repository
- Users receive update notifications directly in WordPress admin
- Seamless one-click updates from the Plugins page

### Dependencies
- Added `yahnis-elsts/plugin-update-checker` ^5.6 via Composer

## [1.0.1] - 2025-11-24

### Changed
- Add PHP 8.3 typed properties (`array`, `string`) to class properties
- Add return type declarations to all methods (`:void`, `:array`, `:string`)
- Add parameter type declarations (`mixed`, `string`)
- Improve PHPDoc annotations with generic types (`array<string>`)

### Added
- Plugin activation hook to ensure default settings exist on activation

### Developer
- Full PHP 8.3 strict typing compliance
- All 7 tests passing (21 assertions)

## [1.0.0] - 2025-11-24

### Added
- Initial release of Custom Document Folder plugin
- Dynamic file extension configuration via WordPress admin settings
- Modern settings page with search functionality
- Category-based organization (Documents, Text, Archives, Fonts, Audio, Images, Video, Other)
- Collapsible categories for Audio, Images, Video, and Other mime types
- Bulk select/deselect all functionality
- Live counter for selected extensions
- Real-time search filtering across extensions and mime types
- Automatic folder creation for selected file types
- Upload path redirection to `/uploads/{extension}/` folders
- File type validation and sanitization
- Non-unique filename handling with old attachment removal
- Comprehensive PHPUnit test suite (7 tests, 21 assertions)
- WP-CLI filter tests for integration verification
- Support for all WordPress-allowed mime types
- Namespace: `Soderlind\DocumentFolder`
- Text domain: `custom-document-folder`

### Features
- **Settings Page**: Configure which file extensions should be uploaded to custom folders
- **Upload Hooks**: 
  - `upload_dir` - Modifies upload directory path
  - `wp_handle_upload_prefilter` - Captures file extension early
  - `wp_check_filetype_and_ext` - Validates file types
  - `wp_handle_upload_overrides` - Handles non-unique filenames
  - `sanitize_file_name` - Removes old attachments on re-upload
- **UI Enhancements**:
  - Grid-based responsive layout
  - Search functionality with real-time filtering
  - Category grouping by mime type
  - Collapsible categories for media files
  - Selected extensions counter
  - Modern WordPress admin styling

### Technical Details
- **PHP Version**: 8.3
- **WordPress**: Compatible with current versions
- **Architecture**: Class-based with WordPress hooks pattern
- **Security**: Uses `sanitize_text_field()` for input sanitization
- **Testing**: PHPUnit with Brain Monkey for WordPress function mocking
- **Code Style**: WordPress Coding Standards compliant

### Developer
- **Author**: Per Soderlind
- **Author URI**: https://soderlind.no
- **Plugin URI**: https://github.com/soderlind/custom-document-folder
- **License**: GPL v2 or later

### Testing
- PHPUnit test suite: All 7 tests passing
- Filter integration tests: All filters verified working
- Settings sanitization: XSS protection tested
- Upload path modification: Verified for multiple file types

[1.1.1]: https://github.com/soderlind/custom-document-folder/releases/tag/1.1.1
[1.1.0]: https://github.com/soderlind/custom-document-folder/releases/tag/1.1.0
[1.0.1]: https://github.com/soderlind/custom-document-folder/releases/tag/1.0.1
[1.0.0]: https://github.com/soderlind/custom-document-folder/releases/tag/1.0.0
