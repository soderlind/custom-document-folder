# Custom Document Folder


Organize document uploads by automatically directing specific file types to custom folders based on their extensions.

## Description

Custom Document Folder automatically organizes your WordPress media uploads by directing specific document types to dedicated folders. Instead of having all files mixed together in date-based folders, you can configure which file extensions should be uploaded to their own organized folders.

## Features

- ✨ **Modern Settings Interface** - Beautiful grid-based UI with search functionality
- 📁 **Category Organization** - Extensions grouped by type (Documents, Images, Video, Audio, Archives, etc.)
- 🔍 **Live Search** - Filter extensions and categories in real-time
- ⚡ **Bulk Actions** - Select/deselect all visible extensions at once
- 🎯 **Collapsible Categories** - Media categories (Audio, Images, Video, Other) hidden by default
- 🔢 **Live Counter** - See how many extensions are selected
- 📂 **Custom Folder Routing** - Each extension gets its own folder (e.g., `/pdf/`, `/docx/`, `/xlsx/`)
- 🔒 **Security Focused** - Input sanitization with `sanitize_text_field()`
- ✅ **MIME Type Validation** - Only allows file types that WordPress already permits
- 🔄 **Backward Compatible** - Existing files remain in their original locations
- 📄 **PDF Default** - Comes pre-configured with PDF support
- 🗑️ **Non-unique Filename Handling** - Automatically removes old attachments when re-uploading

## Installation

### Via WordPress Admin

1. Download the plugin zip file
2. Go to **Plugins > Add New** in WordPress admin
3. Click **Upload Plugin** and choose the zip file
4. Click **Install Now** and then **Activate**
5. Go to **Settings > Document Folder** to configure

### Manual Installation

1. Upload the `custom-document-folder` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Settings > Document Folder** to configure file extensions

### Via Composer

```bash
composer require soderlind/custom-document-folder
```

## Usage

1. Navigate to **Settings > Document Folder**
2. Check the extensions you want to organize (PDF is pre-selected)
3. Use the search box to quickly find specific extensions
4. Click **"Show More Categories"** to see audio, image, and video extensions
5. Click **"Select All"** or **"Deselect All"** for bulk actions
6. Save your settings
7. New uploads of selected types will automatically go to their designated folders

### Example

If you select `pdf` and `docx` extensions:

- PDF files upload to → `/wp-content/uploads/pdf/`
- DOCX files upload to → `/wp-content/uploads/docx/`
- Other files continue uploading to standard date-based folders



## Requirements

- **WordPress:** 6.7 or higher
- **PHP:** 8.3 or higher
- **PHP Extensions:** None required beyond standard WordPress requirements

## Frequently Asked Questions

### What happens to files uploaded before activating the plugin?

They remain in their original locations. This plugin only affects new uploads after you configure and save your settings.

### Can I select multiple file extensions?

Yes! Select as many extensions as you need. The counter shows how many are currently selected.

### Will this break my existing media library?

No. Existing files stay where they are. Only new uploads of selected extensions are redirected to custom folders.

### Does this work with multisite?

Yes. Each site in a multisite installation can configure its own extensions independently.

### Can I organize images and videos too?

Yes! Click "Show More Categories" to reveal and select image, video, and audio extensions. However, some themes and plugins expect images in date-based folders.

## Development

### Architecture

```
custom-document-folder/
├── custom-document-folder.php  # Main plugin file
├── tests/
│   ├── TestCase.php           # Base test class
│   ├── SettingsTest.php       # Settings tests
│   ├── UploadTest.php         # Upload tests
│   ├── cdf-filter-test.php    # WP-CLI filter test
│   └── TEST-RESULTS.md        # Test documentation
├── composer.json              # Composer configuration
├── phpunit.xml.dist          # PHPUnit configuration
├── CHANGELOG.md              # Version history
└── README.md                 # This file
```

### WordPress Hooks

The plugin uses these WordPress hooks:

- `upload_dir` (priority 20) - Modifies upload directory path
- `wp_handle_upload_prefilter` - Captures file extension early
- `wp_check_filetype_and_ext` - Validates file types
- `wp_handle_upload_overrides` - Handles non-unique filenames
- `sanitize_file_name` - Removes old attachments

### Code Standards

- PHP 8.3 with strict types and namespaces
- WordPress Coding Standards compliant
- PHPUnit test coverage
- Brain Monkey for WordPress function mocking
- Namespace: `Soderlind\DocumentFolder`

### Testing

#### Run PHPUnit Tests

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run with coverage
composer test:coverage
```

#### Run WP-CLI Filter Tests

```bash
wp eval-file wp-content/plugins/custom-document-folder/tests/cdf-filter-test.php
```

#### Test Results

```
PHPUnit 9.6.29
OK (7 tests, 21 assertions)
```

All core functionality verified:
- ✅ Settings sanitization
- ✅ Invalid input handling
- ✅ XSS protection
- ✅ PDF upload path modification
- ✅ Non-selected files use default path
- ✅ Multiple extension handling
- ✅ File type validation

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```bash
# Clone repository
git clone https://github.com/soderlind/custom-document-folder.git
cd custom-document-folder

# Install dependencies
composer install

# Run tests
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Support

- **GitHub Issues:** [Report bugs or request features](https://github.com/soderlind/custom-document-folder/issues)
- **WordPress Support:** [Plugin support forum](https://wordpress.org/support/plugin/custom-document-folder/)

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 Per Soderlind

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## Author

**Per Soderlind**

- Website: [soderlind.no](https://soderlind.no)
- GitHub: [@soderlind](https://github.com/soderlind)
- Twitter: [@soderlind](https://twitter.com/soderlind)

## Credits

Built with:
- [WordPress Settings API](https://developer.wordpress.org/plugins/settings/)
- [PHPUnit](https://phpunit.de/)
- [Brain Monkey](https://brain-wp.github.io/BrainMonkey/)
- [Composer](https://getcomposer.org/)

---

Made with ❤️ for the WordPress community
