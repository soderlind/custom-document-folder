=== Custom Document Folder ===
Contributors: PerS
Tags: uploads, documents, media, folder, organization, file management
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 8.3
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Organize document uploads by automatically directing specific file types to custom folders based on their extensions.

== Description ==

Custom Document Folder automatically organizes your WordPress media uploads by directing specific document types to dedicated folders. Instead of having all files mixed together in date-based folders, you can configure which file extensions should be uploaded to their own organized folders.

= Features =

* **Modern settings interface** - Beautiful grid-based UI with search functionality
* **Category organization** - Extensions grouped by type (Documents, Images, Video, Audio, Archives, etc.)
* **Live search** - Filter extensions and categories in real-time
* **Bulk actions** - Select/deselect all visible extensions at once
* **Collapsible categories** - Media categories (Audio, Images, Video, Other) hidden by default
* **Live counter** - See how many extensions are selected
* **Custom folder routing** - Each extension gets its own folder (e.g., `/pdf/`, `/docx/`, `/xlsx/`)
* **MIME type validation** - Only allows file types that WordPress already permits
* **Security focused** - Input sanitization with `sanitize_text_field()`
* **Backward compatible** - Existing files remain in their original locations
* **PDF default** - Comes pre-configured with PDF support
* **Non-unique filename handling** - Automatically removes old attachments when re-uploading

= How It Works =

1. Install and activate the plugin
2. Go to Settings > Document Folder
3. Check the extensions you want to organize (PDF is selected by default)
4. Use the search box to quickly find specific extensions
5. Click "Show More Categories" to see audio, image, and video extensions
6. Save your settings
7. New uploads of selected types will automatically go to their designated folders

For example, if you select PDF and DOCX extensions:
- PDF files will upload to `/wp-content/uploads/pdf/`
- DOCX files will upload to `/wp-content/uploads/docx/`
- Other files continue to upload to the standard date-based folders

= Use Cases =

* **Document libraries** - Organize downloadable resources
* **Media management** - Keep documents separate from images/videos
* **Content organization** - Easier to find and manage specific file types
* **Development workflows** - Predictable upload paths for programmatic access

== Installation ==

1. Upload the `custom-document-folder` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Document Folder to configure file extensions
4. Select the extensions you want to organize (PDF is pre-selected)
5. Save your settings

== Frequently Asked Questions ==

= What happens to files uploaded before activating the plugin? =

They remain in their original locations. This plugin only affects new uploads after you configure and save your settings.

= Can I select multiple file extensions? =

Yes! Select as many extensions as you need. The counter shows how many are currently selected.

= How do I find a specific extension? =

Use the search box at the top of the settings page. It filters both extension names and MIME types in real-time.

= What are the collapsible categories? =

Audio, Images, Video, and Other categories are hidden by default to reduce clutter. Click "Show More Categories" to expand them.

= What file types can I configure? =

Any file type that WordPress allows. The plugin only shows extensions from WordPress's allowed MIME types for security.

= Will this break my existing media library? =

No. Existing files stay where they are. Only new uploads of selected extensions are redirected to custom folders.

= Can I change the folder structure? =

The plugin uses extension-based folders (e.g., `/pdf/`, `/docx/`) at the root of the uploads directory. This is designed for consistency.

= Does this work with multisite? =

Yes. Each site in a multisite installation can configure its own extensions independently.

= What if I deactivate the plugin? =

Files in custom folders remain accessible at their current URLs. New uploads will return to WordPress default date-based folder behavior.

= Can I organize images and videos too? =

Yes! Click "Show More Categories" to reveal and select image, video, and audio extensions. However, some themes and plugins expect images in date-based folders.

= What happens if I upload a file with the same name? =

The plugin includes non-unique filename handling. Old attachments with the same filename are automatically removed when you re-upload.

== Screenshots ==

1. Modern settings page with grid-based layout and search functionality
2. Category organization showing Documents, Text, Archives, and Fonts
3. Collapsible categories for Audio, Images, Video, and Other extensions
4. Real-time search filtering extensions and categories
5. Bulk select/deselect actions and live counter

== Changelog ==

= 1.1.0 - 2025-11-25 =
* Add automatic updates via GitHub releases using Plugin Update Checker library
* Plugin now checks for updates from GitHub repository
* Users receive update notifications in WordPress admin

= 1.0.1 - 2025-11-24 =
* Add PHP 8.3 typed properties to class properties
* Add return type declarations to all methods
* Add parameter type declarations
* Add plugin activation hook for default settings
* Improve PHPDoc annotations with generic types
* Full PHP 8.3 strict typing compliance

= 1.0.0 - 2025-11-24 =
* Initial release
* Modern grid-based settings interface with search functionality
* Category-based organization (Documents, Text, Archives, Fonts, Audio, Images, Video, Other)
* Collapsible categories for media types (Audio, Images, Video, Other)
* Bulk select/deselect all functionality
* Live counter for selected extensions
* Real-time search filtering across extensions and mime types
* Automatic folder creation for selected file types
* Upload path redirection to `/uploads/{extension}/` folders
* File type validation and sanitization
* Non-unique filename handling with old attachment removal
* Support for all WordPress-allowed MIME types
* PDF extension pre-selected by default
* Comprehensive test suite (7 PHPUnit tests, 21 assertions)
* WordPress coding standards compliant
* Namespace: `Soderlind\DocumentFolder`

== Upgrade Notice ==

= 1.1.0 =
Added automatic updates from GitHub releases. The plugin will now notify you when new versions are available.

= 1.0.1 =
Added PHP 8.3 type declarations and activation hook for improved code quality.

= 1.0.0 =
Initial release of Custom Document Folder plugin.

== Development ==

This plugin follows WordPress best practices:

* **Settings API** - Native WordPress settings registration and sanitization
* **Security** - Capability checks, input sanitization, output escaping
* **MIME Validation** - Uses WordPress's allowed MIME types whitelist
* **Filter Hooks** - Standard upload directory modification hooks
* **Modern PHP** - PHP 8.3 with strict types and namespaces
* **Testing** - PHPUnit test suite with Brain Monkey for mocking
* **Code Standards** - WordPress Coding Standards compliant
* **Architecture** - Clean class-based structure with proper separation

= Hooks Used =

* `upload_dir` - Modifies upload directory path (priority 20)
* `wp_handle_upload_prefilter` - Captures file extension early in upload
* `wp_check_filetype_and_ext` - Validates file types against allowed mimes
* `wp_handle_upload_overrides` - Handles non-unique filename callbacks
* `sanitize_file_name` - Removes old attachments on filename collision

= Testing =

Run the test suite:
```bash
composer test
```

For filter integration tests:
```bash
wp eval-file wp-content/plugins/custom-document-folder/tests/cdf-filter-test.php
```

== Support ==

For support and bug reports, please visit:
* GitHub: https://github.com/soderlind/custom-document-folder
* WordPress.org support forum

== Author ==

Developed by Per Soderlind
* Website: https://soderlind.no
* GitHub: https://github.com/soderlind
