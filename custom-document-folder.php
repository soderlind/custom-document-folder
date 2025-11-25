<?php
/**
 * Plugin Name: Custom Document Folder
 * Plugin URI: https://github.com/soderlind/custom-document-folder
 * Description: Redirects specific document types to custom folders based on file extensions. Configure document types in Settings > Document Folder.
 * Version: 1.1.1
 * Author: Per Soderlind
 * Author URI: https://soderlind.no
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-document-folder
 */

declare(strict_types=1);
namespace Soderlind\DocumentFolder;

if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/class-github-plugin-updater.php';
use Soderlind\DocumentFolder\GitHub_Plugin_Updater;

GitHub_Plugin_Updater::create_with_assets(
	'https://github.com/soderlind/custom-document-folder',
	__FILE__,
	'custom-document-folder',
	'/custom-document-folder\.zip/',
	'main'
);


/**
 * Class Upload_Document
 *
 * @package Soderlind\DocumentFolder
 */
class Upload_Document {

	/**
	 * Selected file extensions.
	 *
	 * @var array
	 */
	private $selected_extensions = [];

	/**
	 * File extension.
	 *
	 * @var string
	 */
	private $ext = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->selected_extensions = get_option( 'cdf_selected_extensions', [ 'pdf' ] );
		if ( ! is_array( $this->selected_extensions ) ) {
			$this->selected_extensions = [ 'pdf' ];
		}

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'settings_init' ] );

		add_filter( 'upload_dir', [ $this, 'upload_dir' ], 20 );
		add_filter( 'wp_handle_upload_prefilter', [ $this, 'wp_handle_upload_prefilter' ] );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'wp_check_filetype_and_ext' ], 10, 4 );
		add_filter( 'wp_handle_upload_overrides', [ $this, 'non_unique_filename' ] );
		add_filter( 'sanitize_file_name', [ $this, 'filename_filter' ], 10, 1 );
	}

	/**
	 * Add settings page.
	 */
	public function add_admin_menu() {
		add_options_page(
			'Custom Document Folder',
			'Document Folder',
			'manage_options',
			'custom-document-folder',
			[ $this, 'settings_page_html' ]
		);
	}

	/**
	 * Register settings.
	 */
	public function settings_init() {
		register_setting(
			'cdf_plugin',
			'cdf_selected_extensions',
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_extensions' ],
				'default'           => [ 'pdf' ],
			]
		);

		add_settings_section(
			'cdf_section',
			__( 'File Extension Settings', 'custom-document-folder' ),
			[ $this, 'section_callback' ],
			'cdf_plugin'
		);

		add_settings_field(
			'cdf_selected_extensions',
			__( 'Select Extensions', 'custom-document-folder' ),
			[ $this, 'extensions_render' ],
			'cdf_plugin',
			'cdf_section'
		);
	}

	/**
	 * Sanitize extensions.
	 *
	 * @param array $input Input array.
	 * @return array
	 */
	public function sanitize_extensions( $input ) {
		if ( ! is_array( $input ) ) {
			return [ 'pdf' ];
		}
		return array_map( 'sanitize_text_field', $input );
	}

	/**
	 * Section callback.
	 */
	public function section_callback() {
		echo '<p>' . esc_html__( 'Select the file extensions that should be uploaded to custom folders. Each selected extension will be uploaded to its own folder (e.g., /uploads/pdf/).', 'custom-document-folder' ) . '</p>';
	}

	/**
	 * Render settings field.
	 */
	public function extensions_render() {
		$allowed_mimes = get_allowed_mime_types();
		$extensions    = [];
		$grouped       = [];

		// Build extensions array and group by mime type.
		foreach ( $allowed_mimes as $ext_str => $mime ) {
			$exts = explode( '|', $ext_str );
			foreach ( $exts as $e ) {
				$extensions[ $e ]            = $mime;
				$mime_category               = $this->get_mime_category( $mime );
				$grouped[ $mime_category ][] = [ 'ext' => $e, 'mime' => $mime ];
			}
		}

		// Sort categories and extensions.
		ksort( $grouped );
		foreach ( $grouped as $category => $items ) {
			usort( $grouped[ $category ], function ( $a, $b ) {
				return strcmp( $a[ 'ext' ], $b[ 'ext' ] );
			} );
		}

		$selected_count    = count( $this->selected_extensions );
		$hidden_categories = [ 'audio', 'images', 'video', 'other' ];
		?>
		<div class="cdf-extensions-wrapper">
			<div class="cdf-controls">
				<input type="text" id="cdf-search" class="regular-text"
					placeholder="<?php esc_attr_e( 'Search extensions...', 'custom-document-folder' ); ?>">
				<button type="button"
					class="button cdf-select-all"><?php esc_html_e( 'Select All', 'custom-document-folder' ); ?></button>
				<button type="button"
					class="button cdf-deselect-all"><?php esc_html_e( 'Deselect All', 'custom-document-folder' ); ?></button>
				<button type="button" class="button cdf-toggle-hidden" id="cdf-toggle-hidden">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
					<?php esc_html_e( 'Show More Categories', 'custom-document-folder' ); ?>
				</button>
				<span
					class="cdf-count"><?php printf( esc_html__( 'Selected: %d', 'custom-document-folder' ), $selected_count ); ?></span>
			</div>

			<div class="cdf-extensions-grid">
				<?php foreach ( $grouped as $category => $items ) : ?>
					<?php
					$is_hidden      = in_array( $category, $hidden_categories, true );
					$category_class = $is_hidden ? 'cdf-category cdf-category-collapsible' : 'cdf-category';
					?>
					<div class="<?php echo esc_attr( $category_class ); ?>" data-category="<?php echo esc_attr( $category ); ?>">
						<h4 class="cdf-category-title"><?php echo esc_html( ucfirst( $category ) ); ?></h4>
						<div class="cdf-extensions-list">
							<?php foreach ( $items as $item ) : ?>
								<?php
								$ext     = $item[ 'ext' ];
								$mime    = $item[ 'mime' ];
								$checked = in_array( $ext, $this->selected_extensions, true ) ? 'checked' : '';
								?>
								<label class="cdf-extension-item" data-ext="<?php echo esc_attr( $ext ); ?>">
									<input type="checkbox" name="cdf_selected_extensions[]" value="<?php echo esc_attr( $ext ); ?>"
										<?php echo $checked; ?>>
									<span class="cdf-ext-name"><?php echo esc_html( $ext ); ?></span>
									<span class="cdf-mime-type"><?php echo esc_html( $mime ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<style>
			.cdf-extensions-wrapper {
				max-width: 1200px;
			}

			.cdf-controls {
				margin-bottom: 20px;
				padding: 15px;
				background: #f9f9f9;
				border: 1px solid #ddd;
				border-radius: 4px;
				display: flex;
				gap: 10px;
				align-items: center;
				flex-wrap: wrap;
			}

			#cdf-search {
				flex: 1;
				min-width: 200px;
			}

			.cdf-toggle-hidden {
				display: flex;
				align-items: center;
				gap: 5px;
			}

			.cdf-toggle-hidden .dashicons {
				transition: transform 0.3s;
			}

			.cdf-toggle-hidden.expanded .dashicons {
				transform: rotate(180deg);
			}

			.cdf-count {
				margin-left: auto;
				font-weight: 600;
				color: #2271b1;
			}

			.cdf-extensions-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
				gap: 20px;
			}

			.cdf-category {
				border: 1px solid #ddd;
				border-radius: 4px;
				background: #fff;
				overflow: hidden;
			}

			.cdf-category.hidden {
				display: none;
			}

			.cdf-category-collapsible {
				display: none;
			}

			.cdf-category-collapsible.show {
				display: block;
			}

			.cdf-category-title {
				margin: 0;
				padding: 12px 15px;
				background: #f0f0f1;
				border-bottom: 1px solid #ddd;
				font-size: 14px;
				font-weight: 600;
				color: #1d2327;
			}

			.cdf-extensions-list {
				padding: 10px;
				max-height: 400px;
				overflow-y: auto;
			}

			.cdf-extension-item {
				display: flex;
				align-items: center;
				padding: 8px 10px;
				margin: 0;
				border-radius: 3px;
				transition: background 0.2s;
				cursor: pointer;
				gap: 8px;
			}

			.cdf-extension-item:hover {
				background: #f6f7f7;
			}

			.cdf-extension-item.hidden {
				display: none;
			}

			.cdf-extension-item input[type="checkbox"] {
				margin: 0;
			}

			.cdf-ext-name {
				font-weight: 600;
				color: #2271b1;
				min-width: 50px;
			}

			.cdf-mime-type {
				font-size: 12px;
				color: #666;
				flex: 1;
			}
		</style>

		<script>
			jQuery(document).ready(function ($) {
				const $search = $('#cdf-search');
				const $count = $('.cdf-count');
				const $checkboxes = $('input[name="cdf_selected_extensions[]"]');
				const $toggleBtn = $('#cdf-toggle-hidden');
				let hiddenExpanded = false;

				// Update count
				function updateCount() {
					const count = $checkboxes.filter(':checked').length;
					$count.text('<?php esc_html_e( 'Selected: ', 'custom-document-folder' ); ?>' + count);
				}

				// Toggle hidden categories
				$toggleBtn.on('click', function () {
					hiddenExpanded = !hiddenExpanded;
					$('.cdf-category-collapsible').toggleClass('show', hiddenExpanded);

					if (hiddenExpanded) {
						$(this).addClass('expanded');
						$(this).find('span:not(.dashicons)').text('<?php esc_html_e( 'Show Fewer Categories', 'custom-document-folder' ); ?>');
					} else {
						$(this).removeClass('expanded');
						$(this).find('span:not(.dashicons)').text('<?php esc_html_e( 'Show More Categories', 'custom-document-folder' ); ?>');
					}
				});

				// Search functionality
				$search.on('input', function () {
					const query = $(this).val().toLowerCase();
					let visibleCategories = [];

					if (query) {
						// When searching, show all categories including hidden ones
						$('.cdf-category-collapsible').addClass('show');
					}

					$('.cdf-extension-item').each(function () {
						const ext = $(this).data('ext').toLowerCase();
						const mime = $(this).find('.cdf-mime-type').text().toLowerCase();
						const matches = ext.includes(query) || mime.includes(query);

						$(this).toggleClass('hidden', !matches);

						if (matches) {
							const category = $(this).closest('.cdf-category').data('category');
							if (!visibleCategories.includes(category)) {
								visibleCategories.push(category);
							}
						}
					});

					$('.cdf-category').each(function () {
						const category = $(this).data('category');
						const hasVisibleItems = visibleCategories.includes(category);

						if (!query) {
							// When not searching, respect the collapsed state
							if ($(this).hasClass('cdf-category-collapsible')) {
								$(this).toggleClass('show', hiddenExpanded && hasVisibleItems);
							} else {
								$(this).toggleClass('hidden', !hasVisibleItems);
							}
						} else {
							// When searching, show/hide based on matches
							$(this).toggleClass('hidden', !hasVisibleItems);
						}
					});
				});

				// Select/Deselect all
				$('.cdf-select-all').on('click', function () {
					$checkboxes.filter(':visible').prop('checked', true);
					updateCount();
				});

				$('.cdf-deselect-all').on('click', function () {
					$checkboxes.filter(':visible').prop('checked', false);
					updateCount();
				});

				// Update count on change
				$checkboxes.on('change', updateCount);
			});
		</script>
		<?php
	}

	/**
	 * Get mime category.
	 *
	 * @param string $mime Mime type.
	 * @return string
	 */
	private function get_mime_category( $mime ) {
		if ( strpos( $mime, 'image/' ) === 0 ) {
			return 'images';
		}
		if ( strpos( $mime, 'video/' ) === 0 ) {
			return 'video';
		}
		if ( strpos( $mime, 'audio/' ) === 0 ) {
			return 'audio';
		}
		if ( strpos( $mime, 'text/' ) === 0 ) {
			return 'text';
		}
		if ( in_array( $mime, [ 'application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument' ], true ) || strpos( $mime, 'officedocument' ) !== false ) {
			return 'documents';
		}
		if ( in_array( $mime, [ 'application/zip', 'application/x-rar-compressed', 'application/x-tar', 'application/x-7z-compressed' ], true ) ) {
			return 'archives';
		}
		if ( strpos( $mime, 'font/' ) === 0 || in_array( $mime, [ 'application/font-woff', 'application/font-woff2', 'application/x-font-ttf' ], true ) ) {
			return 'fonts';
		}
		return 'other';
	}

	/**
	 * Render settings page.
	 */
	public function settings_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Configure which file types should be uploaded to custom folders. Each selected extension will be uploaded to /uploads/[extension]/ (e.g., /uploads/pdf/).', 'custom-document-folder' ); ?>
			</p>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'cdf_plugin' );
				do_settings_sections( 'cdf_plugin' );
				submit_button( __( 'Save Settings', 'custom-document-folder' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Set custom upload folder.
	 *
	 * @param array $param An array of upload directory data with keys of 'path', 'url', 'subdir', 'basedir', and 'error'.
	 *
	 * @return array
	 */
	public function upload_dir( array $param ): array {
		if ( ! in_array( $this->ext, $this->selected_extensions, true ) ) {
			return $param;
		}

		$folder = '/' . $this->ext;

		wp_mkdir_p( $param[ 'basedir' ] . $folder );

		$param[ 'subdir' ] = $folder;
		$param[ 'path' ]   = $param[ 'basedir' ] . $param[ 'subdir' ];
		$param[ 'url' ]    = $param[ 'baseurl' ] . $param[ 'subdir' ];

		return $param;
	}

	/**
	 * Handle upload prefilter.
	 *
	 * @param array $file An array of data for a single file.
	 *
	 * @return array
	 */
	public function wp_handle_upload_prefilter( array $file ): array {
		$ext = pathinfo( $file[ 'name' ], \PATHINFO_EXTENSION );
		if ( ! in_array( $ext, $this->selected_extensions, true ) ) {
			return $file;
		}

		$this->ext = $ext;

		return $file;
	}

	/**
	 * Check filetype and ext.
	 *
	 * @param array      $data     An array of data returned from the wp_check_filetype() function.
	 * @param string     $file     Full path to the file.
	 * @param string     $filename The name of the file (may differ from $file due to $file being in a tmp directory).
	 * @param array|null $mimes    Key is the file extension with value as the mime type.
	 *
	 * @return array
	 */
	public function wp_check_filetype_and_ext( array $data, string $file, string $filename, $mimes ): array { // phpcs:ignore
		$ext = pathinfo( $filename, \PATHINFO_EXTENSION );
		if ( ! in_array( $ext, $this->selected_extensions, true ) ) {
			return $data;
		}

		$data[ 'ext' ] = $ext;
		if ( empty( $data[ 'type' ] ) ) {
			$allowed = get_allowed_mime_types();
			foreach ( $allowed as $key => $mime ) {
				if ( in_array( $ext, explode( '|', $key ), true ) ) {
					$data[ 'type' ] = $mime;
					break;
				}
			}
		}

		return $data;
	}


	/**
	 * Set the callback for handling non-unique file names.
	 *
	 * @param array $overrides An associative array of file upload overrides.
	 *
	 * @return array
	 */
	public function non_unique_filename( array $overrides ): array {
		$overrides[ 'test_form' ]                = false;
		$overrides[ 'unique_filename_callback' ] = [ $this, 'non_unique_filename_callback' ];
		return $overrides;
	}

	/**
	 * If the file extension is selected, remove old attachment.
	 *
	 * @param string $directory Directory path for file.
	 * @param string $filename  File name.
	 * @param string $extension File extension.
	 *
	 * @return string
	 */
	public function non_unique_filename_callback( $directory, $filename, $extension ) {
		if ( ! in_array( strtolower( $extension ), $this->selected_extensions, true ) ) {
			return $filename;
		}

		$this->remove_old_attach( $filename );

		return $filename;
	}

	/**
	 * Delete old attachment.
	 *
	 * @param string $filename File name.
	 *
	 * @return void
	 */
	public function remove_old_attach( $filename ) {
		$arguments             = [
			'numberposts' => -1,
			'meta_key'    => '_wp_attached_file', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $filename, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'post_type'   => 'attachment',
		];
		$attachments_to_remove = \get_posts( $arguments );

		foreach ( $attachments_to_remove as $a ) {
			\wp_delete_attachment( $a->ID, true );
		}
	}

	/**
	 * Remove the old attachment.
	 *
	 * @param string $name File name.
	 *
	 * @return string
	 */
	public function filename_filter( string $name ): string {
		$args                  = [
			'numberposts' => -1,
			'post_type'   => 'attachment',
			'meta_query'  => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				[
					'key'     => '_wp_attached_file',
					'value'   => $name,
					'compare' => 'LIKE',
				],
			],
		];
		$attachments_to_remove = \get_posts( $args );

		foreach ( $attachments_to_remove as $attach ) {
			\wp_delete_attachment( $attach->ID, true );
		}

		return $name;
	}
}

new Upload_Document();