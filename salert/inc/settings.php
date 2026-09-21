<?php
/**
 * Admin Settings Page - Modern UI (2026 redesign)
 * Field names & AJAX contract kept identical to v1.3.1 for backward compatibility.
 */

if( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

class Salert_Admin_Settings {
	/**
	 * Contains Default Component keys
	 * @var array
	 * @since 1.0.0
	 */
	public $salert_default_keys = [
								'popup-enable' => 0,
								'popup-start-time' => 5,
								'popup-stay-time' => 10,
								'popup-time-interval-from' => 10,
								'popup-time-interval-to' => 20,
								'popup-position' => 'bottomRight',
								'popup-animation' => 'fadeInUp',
								'image-position' => 'imageOnLeft',
								'image-style' => 'square',
								'bg-color' => '#fff',
								'container-width' => 350,
								'inner-padding' => 10,
								'border-enable' => 1,
								'border-color' => '#e0e0e0',
								'border-width' => 2,
								'border-radius' => 0,
								'text-color' => '#000',
								'font-size' => 14,
								'text-transform' => 'none',
								'popup-names' => 'John,Eliye',
								'popup-countries' => 'Australia,USA',
								'popup-timeperiod' => 'hours,mins,sec',
								'popup-timeago' => 'ago',
								'popup-products' => array(),
								'product-count' => 0,
								'popup-contents' => '[name] from [country] has just purchased [product]<br>
[time]',
								'close-btn' => 1,
								'enable-resp' => 1,
								'box-shadow' => 1,
								'text-separator' => ','
							];

	private $salert_default_settings;
	private $salert_settings;
	private $salert_get_settings;

	function __construct(){
		add_action( 'admin_menu', array( $this, 'create_salert_admin_menu' ) );
		add_action( 'wp_ajax_salert_save_settings_with_ajax', array( $this, 'salert_save_settings_with_ajax' ) );
	}

	public function create_salert_admin_menu() {
		add_menu_page(
			esc_html__('Salert','salert'),
			esc_html__('Salert','salert'),
			'manage_options',
			'salert-settings',
			array( $this, 'salert_admin_settings_page' ),
			'dashicons-megaphone',
			30
		);
	}

	/**
	 * Render a toggle switch row.
	 */
	private function render_toggle( $name, $label, $desc = '', $disabled = false, $premium = false ) {
		$value   = isset( $this->salert_get_settings[ $name ] ) ? $this->salert_get_settings[ $name ] : 0;
		$classes = 'salert-switch';
		if ( $premium ) { $classes .= ' is-premium'; }
		?>
		<div class="salert-field <?php echo $premium ? 'salert-field-premium' : ''; ?>">
			<div class="salert-field-label">
				<span class="salert-field-title"><?php echo esc_html( $label ); ?></span>
				<?php if ( $desc ) : ?><span class="salert-field-desc"><?php echo esc_html( $desc ); ?></span><?php endif; ?>
			</div>
			<label class="<?php echo esc_attr( $classes ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $value, '1', true ); ?> <?php disabled( $disabled ); ?>>
				<span class="salert-switch-track"><span class="salert-switch-thumb"></span></span>
			</label>
			<?php if ( $premium ) : ?><span class="salert-pro-badge"><?php esc_html_e( 'PRO', 'salert' ); ?></span><?php endif; ?>
		</div>
		<?php
	}

	public function salert_admin_settings_page() {
		$this->salert_default_settings = $this->salert_default_keys;
		$this->salert_get_settings = get_option( 'salert_save_settings', $this->salert_default_settings );
		$salert_new_settings = array_diff_key( $this->salert_default_settings, $this->salert_get_settings );

		if(empty($this->salert_get_settings)){
			update_option( 'salert_save_settings', $this->salert_default_settings );
		}
		if( ! empty( $salert_new_settings ) ) {
			$salert_updated_settings = array_merge( $this->salert_get_settings, $salert_new_settings );
			update_option( 'salert_save_settings', $salert_updated_settings );
		}
		$this->salert_get_settings = get_option( 'salert_save_settings', $this->salert_default_settings );
		$s = $this->salert_get_settings;

		$animations = array(
			'fadeInLeft' => 'Fade In Left', 'fadeInUp' => 'Fade In Up', 'fadeInRight' => 'Fade In Right',
			'bounceInRight' => 'Bounce In Right', 'bounceInLeft' => 'Bounce In Left', 'bounceInUp' => 'Bounce In Up',
			'zoomIn' => 'Zoom In', 'zoomInDown' => 'Zoom In Down', 'zoomInLeft' => 'Zoom In Left',
			'zoomInRight' => 'Zoom In Right', 'zoomInUp' => 'Zoom In Up',
			'jackInTheBox' => 'Jack In The Box', 'rollIn' => 'Roll In', 'lightSpeedIn' => 'Light Speed In',
		);
		?>
		<div class="wrap salert-wrap">

			<!-- ===== App Header ===== -->
			<div class="salert-header">
				<div class="salert-header-brand">
					<div class="salert-header-logo">S</div>
					<div>
						<h1>Salert</h1>
						<p><?php esc_html_e( 'Sales notification popups for WooCommerce & beyond', 'salert' ); ?></p>
					</div>
				</div>
				<div class="salert-header-actions">
					<span class="salert-version-pill">v<?php echo esc_html( SALERT_VERSION ); ?></span>
					<a href="https://wordpress.org/support/plugin/salert/reviews/#new-post" target="_blank" rel="noopener" class="salert-btn-secondary">
						<span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Rate Us', 'salert' ); ?>
					</a>
					<a href="https://wpoperation.com/plugins/sale-alert/" target="_blank" rel="noopener" class="salert-btn-primary">
						<span class="dashicons dashicons-awards"></span> <?php esc_html_e( 'Upgrade to Pro', 'salert' ); ?>
					</a>
				</div>
			</div>

			<form action="" method="post" id="salert-settings-form" name="salert-settings">

			<!-- ===== Layout: Sidebar Nav + Content + Preview ===== -->
			<div class="salert-layout">

				<!-- Sidebar -->
				<nav class="salert-nav" aria-label="<?php esc_attr_e( 'Salert sections', 'salert' ); ?>">
					<button type="button" class="salert-nav-item active" data-pane="pane-general">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'General', 'salert' ); ?>
					</button>
					<button type="button" class="salert-nav-item" data-pane="pane-content">
						<span class="dashicons dashicons-editor-ul"></span>
						<?php esc_html_e( 'Popup Content', 'salert' ); ?>
					</button>
					<button type="button" class="salert-nav-item" data-pane="pane-help">
						<span class="dashicons dashicons-editor-help"></span>
						<?php esc_html_e( 'How To Use', 'salert' ); ?>
					</button>
					<button type="button" class="salert-nav-item" data-pane="pane-compare">
						<span class="dashicons dashicons-star-filled"></span>
						<?php esc_html_e( 'Free vs Pro', 'salert' ); ?>
					</button>
					<button type="button" class="salert-nav-item" data-pane="pane-more">
						<span class="dashicons dashicons-grid-view"></span>
						<?php esc_html_e( 'More From Us', 'salert' ); ?>
					</button>

					<div class="salert-nav-card">
						<h4><?php esc_html_e( 'Love Salert?', 'salert' ); ?></h4>
						<p><?php esc_html_e( 'A 5-star rating helps other store owners find us.', 'salert' ); ?></p>
						<a href="https://wordpress.org/support/plugin/salert/reviews/#new-post" target="_blank" rel="noopener" class="salert-btn-secondary salert-btn-block">
							<?php esc_html_e( 'Leave a Review', 'salert' ); ?>
						</a>
					</div>
				</nav>

				<!-- Main panes -->
				<div class="salert-main">

					<!-- ============ GENERAL PANE ============ -->
					<section class="salert-pane active" id="pane-general">

						<div class="salert-card">
							<header class="salert-card-head">
								<h2><span class="dashicons dashicons-controls-play"></span> <?php esc_html_e( 'Popup Behavior', 'salert' ); ?></h2>
							</header>
							<div class="salert-card-body">
								<?php $this->render_toggle( 'popup-enable', __( 'Enable Popup', 'salert' ), __( 'Master switch — show sales notifications on your site.', 'salert' ) ); ?>

								<div class="salert-field-grid">
									<div class="salert-field">
										<label class="salert-field-title" for="popup-start-time"><?php esc_html_e( 'First popup after (seconds)', 'salert' ); ?></label>
										<input type="number" min="0" id="popup-start-time" name="popup-start-time" value="<?php echo esc_attr( $s['popup-start-time'] ); ?>">
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="popup-stay-time"><?php esc_html_e( 'Stay visible (seconds)', 'salert' ); ?></label>
										<input type="number" min="0" id="popup-stay-time" name="popup-stay-time" value="<?php echo esc_attr( $s['popup-stay-time'] ); ?>">
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="popup-time-interval-from"><?php esc_html_e( 'Interval between popups', 'salert' ); ?></label>
										<span class="salert-inline-range">
											<input type="number" min="0" id="popup-time-interval-from" name="popup-time-interval-from" value="<?php echo esc_attr( $s['popup-time-interval-from'] ); ?>">
											<em><?php esc_html_e( 'to', 'salert' ); ?></em>
											<input type="number" min="0" id="popup-time-interval-to" name="popup-time-interval-to" value="<?php echo esc_attr( $s['popup-time-interval-to'] ); ?>">
											<em><?php esc_html_e( 'sec', 'salert' ); ?></em>
										</span>
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="popup-position"><?php esc_html_e( 'Screen position', 'salert' ); ?></label>
										<select id="popup-position" name="popup-position">
											<option value="bottomLeft" <?php selected( $s['popup-position'], 'bottomLeft' ); ?>><?php esc_html_e( 'Bottom Left', 'salert' ); ?></option>
											<option value="bottomRight" <?php selected( $s['popup-position'], 'bottomRight' ); ?>><?php esc_html_e( 'Bottom Right', 'salert' ); ?></option>
											<option value="topLeft" <?php selected( $s['popup-position'], 'topLeft' ); ?>><?php esc_html_e( 'Top Left', 'salert' ); ?></option>
											<option value="topRight" <?php selected( $s['popup-position'], 'topRight' ); ?>><?php esc_html_e( 'Top Right', 'salert' ); ?></option>
										</select>
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="popup-animation"><?php esc_html_e( 'Entry animation', 'salert' ); ?></label>
										<select id="popup-animation" name="popup-animation">
											<?php foreach ( $animations as $anim_key => $anim_label ) : ?>
												<option value="<?php echo esc_attr( $anim_key ); ?>" <?php selected( $s['popup-animation'], $anim_key ); ?>><?php echo esc_html( $anim_label ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="salert-card">
							<header class="salert-card-head">
								<h2><span class="dashicons dashicons-art"></span> <?php esc_html_e( 'Design', 'salert' ); ?></h2>
							</header>
							<div class="salert-card-body">
								<div class="salert-field-grid salert-grid-3">
									<div class="salert-field">
										<label class="salert-field-title" for="bg-color"><?php esc_html_e( 'Background color', 'salert' ); ?></label>
										<input type="text" class="color-picker" id="bg-color" name="bg-color" value="<?php echo esc_attr( $s['bg-color'] ); ?>">
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="text-color"><?php esc_html_e( 'Text color', 'salert' ); ?></label>
										<input type="text" class="color-picker" id="text-color" name="text-color" value="<?php echo esc_attr( $s['text-color'] ); ?>">
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="font-size"><?php esc_html_e( 'Font size (px)', 'salert' ); ?></label>
										<input type="number" min="0" id="font-size" name="font-size" value="<?php echo esc_attr( $s['font-size'] ); ?>">
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="container-width"><?php esc_html_e( 'Popup width (px)', 'salert' ); ?></label>
										<input type="number" min="0" id="container-width" name="container-width" value="<?php echo esc_attr( $s['container-width'] ); ?>">
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="inner-padding"><?php esc_html_e( 'Inner padding (px)', 'salert' ); ?></label>
										<input type="number" min="0" id="inner-padding" name="inner-padding" value="<?php echo esc_attr( $s['inner-padding'] ); ?>">
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="text-transform"><?php esc_html_e( 'Text transform', 'salert' ); ?></label>
										<select id="text-transform" name="text-transform">
											<option value="none" <?php selected( $s['text-transform'], 'none' ); ?>><?php esc_html_e( 'Default', 'salert' ); ?></option>
											<option value="uppercase" <?php selected( $s['text-transform'], 'uppercase' ); ?>><?php esc_html_e( 'Uppercase', 'salert' ); ?></option>
											<option value="lowercase" <?php selected( $s['text-transform'], 'lowercase' ); ?>><?php esc_html_e( 'Lowercase', 'salert' ); ?></option>
											<option value="capitalize" <?php selected( $s['text-transform'], 'capitalize' ); ?>><?php esc_html_e( 'Capitalize', 'salert' ); ?></option>
										</select>
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="image-position"><?php esc_html_e( 'Image position', 'salert' ); ?></label>
										<select id="image-position" name="image-position">
											<option value="imageOnLeft" <?php selected( $s['image-position'], 'imageOnLeft' ); ?>><?php esc_html_e( 'Image on left', 'salert' ); ?></option>
											<option value="imageOnRight" <?php selected( $s['image-position'], 'imageOnRight' ); ?>><?php esc_html_e( 'Image on right', 'salert' ); ?></option>
											<option value="textOnly" <?php selected( $s['image-position'], 'textOnly' ); ?>><?php esc_html_e( 'Text only', 'salert' ); ?></option>
										</select>
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="image-style"><?php esc_html_e( 'Image style', 'salert' ); ?></label>
										<select id="image-style" name="image-style">
											<option value="square" <?php selected( $s['image-style'], 'square' ); ?>><?php esc_html_e( 'Square', 'salert' ); ?></option>
											<option value="circle" <?php selected( $s['image-style'], 'circle' ); ?>><?php esc_html_e( 'Circle', 'salert' ); ?></option>
										</select>
									</div>
								</div>

								<div class="salert-divider"></div>

								<div class="salert-field-grid">
									<?php $this->render_toggle( 'close-btn', __( 'Close button', 'salert' ), __( 'Let visitors dismiss the popup.', 'salert' ) ); ?>
									<?php $this->render_toggle( 'box-shadow', __( 'Drop shadow', 'salert' ) ); ?>
									<?php $this->render_toggle( 'enable-resp', __( 'Show on mobile', 'salert' ) ); ?>
									<?php $this->render_toggle( 'border-enable', __( 'Border', 'salert' ) ); ?>
								</div>

								<div class="salert-border-options" id="salert-border-options">
									<div class="salert-field-grid salert-grid-3">
										<div class="salert-field">
											<label class="salert-field-title" for="border-color"><?php esc_html_e( 'Border color', 'salert' ); ?></label>
											<input type="text" class="color-picker" id="border-color" name="border-color" value="<?php echo esc_attr( $s['border-color'] ); ?>">
										</div>
										<div class="salert-field">
											<label class="salert-field-title" for="border-width"><?php esc_html_e( 'Border width (px)', 'salert' ); ?></label>
											<input type="number" min="0" id="border-width" name="border-width" value="<?php echo esc_attr( $s['border-width'] ); ?>">
										</div>
										<div class="salert-field">
											<label class="salert-field-title" for="border-radius"><?php esc_html_e( 'Corner radius (px)', 'salert' ); ?></label>
											<input type="number" min="0" id="border-radius" name="border-radius" value="<?php echo esc_attr( $s['border-radius'] ); ?>">
										</div>
									</div>
								</div>

								<div class="salert-premium-row">
									<span class="dashicons dashicons-lock"></span>
									<strong><?php esc_html_e( 'Background image', 'salert' ); ?></strong> — <?php esc_html_e( 'available in Pro', 'salert' ); ?>
									<a href="https://wpoperation.com/plugins/sale-alert/" target="_blank" rel="noopener"><?php esc_html_e( 'Unlock', 'salert' ); ?> →</a>
								</div>
							</div>
						</div>

					</section>

					<!-- ============ CONTENT PANE ============ -->
					<section class="salert-pane" id="pane-content">

						<div class="salert-card">
							<header class="salert-card-head">
								<h2><span class="dashicons dashicons-format-quote"></span> <?php esc_html_e( 'Notification Text', 'salert' ); ?></h2>
							</header>
							<div class="salert-card-body">
								<div class="salert-field">
									<label class="salert-field-title" for="popup-contents"><?php esc_html_e( 'Message template', 'salert' ); ?></label>
									<span class="salert-field-desc"><?php esc_html_e( 'Use the placeholders below. Basic HTML allowed.', 'salert' ); ?></span>
									<textarea rows="4" id="popup-contents" name="popup-contents"><?php echo esc_textarea( $s['popup-contents'] ); ?></textarea>
									<div class="salert-tags">
										<code>[name]</code><code>[country]</code><code>[product]</code><code>[time]</code>
									</div>
								</div>
								<div class="salert-premium-row">
									<span class="dashicons dashicons-lock"></span>
									<strong><?php esc_html_e( 'Multiple message templates', 'salert' ); ?></strong> — <?php esc_html_e( 'rotate unlimited variations, available in Pro', 'salert' ); ?>
									<a href="https://wpoperation.com/plugins/sale-alert/" target="_blank" rel="noopener"><?php esc_html_e( 'Unlock', 'salert' ); ?> →</a>
								</div>
							</div>
						</div>

						<div class="salert-card">
							<header class="salert-card-head">
								<h2><span class="dashicons dashicons-groups"></span> <?php esc_html_e( 'Names & Countries', 'salert' ); ?></h2>
							</header>
							<div class="salert-card-body">
								<div class="salert-field">
									<label class="salert-field-title" for="text-separator"><?php esc_html_e( 'List separator', 'salert' ); ?></label>
									<input type="text" class="salert-input-sm" id="text-separator" name="text-separator" value="<?php echo esc_attr( $s['text-separator'] ); ?>">
								</div>
								<div class="salert-field">
									<label class="salert-field-title" for="popup-names"><?php esc_html_e( 'Person names', 'salert' ); ?></label>
									<span class="salert-field-desc"><?php esc_html_e( 'Separated by your list separator, e.g. John,Martin,Ram', 'salert' ); ?></span>
									<textarea rows="3" id="popup-names" name="popup-names"><?php echo esc_textarea( $s['popup-names'] ); ?></textarea>
								</div>
								<div class="salert-field">
									<label class="salert-field-title" for="popup-countries"><?php esc_html_e( 'Countries', 'salert' ); ?></label>
									<span class="salert-field-desc"><?php esc_html_e( 'Separated by your list separator, e.g. Nepal,USA,Japan', 'salert' ); ?></span>
									<textarea rows="3" id="popup-countries" name="popup-countries"><?php echo esc_textarea( $s['popup-countries'] ); ?></textarea>
								</div>
								<div class="salert-field-grid">
									<div class="salert-field">
										<label class="salert-field-title" for="popup-timeperiod"><?php esc_html_e( 'Time period labels', 'salert' ); ?></label>
										<input type="text" id="popup-timeperiod" name="popup-timeperiod" value="<?php echo esc_attr( $s['popup-timeperiod'] ); ?>">
									</div>
									<div class="salert-field">
										<label class="salert-field-title" for="popup-timeago"><?php esc_html_e( '"Ago" text', 'salert' ); ?></label>
										<input type="text" id="popup-timeago" name="popup-timeago" value="<?php echo esc_attr( $s['popup-timeago'] ); ?>">
									</div>
								</div>
							</div>
						</div>

						<div class="salert-card">
							<header class="salert-card-head">
								<h2><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Products', 'salert' ); ?></h2>
								<?php if ( class_exists( 'woocommerce' ) ) : ?>
									<span class="salert-pro-badge salert-badge-inline"><?php esc_html_e( 'Real orders & Woo products in Pro', 'salert' ); ?></span>
								<?php endif; ?>
							</header>
							<div class="salert-card-body">
								<fieldset class="salert-input mannual-products">
									<div class="products-meta-section-wrapper">
										<div class="table-products-wrapper" id="salert-products-list">
											<?php
												$table_product = isset( $s['popup-products'] ) ? $s['popup-products'] : '';
												$t_count = 0;
												if ( ! empty( $table_product ) && ! empty( $table_product['title'] ) ) {
													foreach ( $table_product['title'] as $product => $val ) {
														$t_count++;
														$product_image = $table_product['url'][ $product ];
														?>
														<div class="single-product">
															<div class="salert-product-thumb">
																<?php if ( ! empty( $product_image ) ) : ?>
																	<img src="<?php echo esc_url( $product_image ); ?>" alt="">
																<?php endif; ?>
															</div>
															<div class="salert-product-fields">
																<input type="text" name="popup-products[title][<?php echo esc_attr( $t_count ); ?>]" placeholder="<?php esc_attr_e( 'Product name', 'salert' ); ?>" value="<?php echo esc_attr( $table_product['title'][ $product ] ); ?>" required/>
																<div class="salert-product-row">
																	<input type="text" class="salert-image-url" name="popup-products[url][<?php echo esc_attr( $t_count ); ?>]" placeholder="<?php esc_attr_e( 'Image URL', 'salert' ); ?>" value="<?php echo esc_url( $product_image ); ?>">
																	<button type="button" class="button salert-upload-btn"><span class="dashicons dashicons-upload"></span></button>
																</div>
																<input type="text" name="popup-products[link][<?php echo esc_attr( $t_count ); ?>]" placeholder="<?php esc_attr_e( 'Link (https://)', 'salert' ); ?>" value="<?php echo esc_attr( $table_product['link'][ $product ] ); ?>">
															</div>
															<button type="button" class="button-link delete-product" aria-label="<?php esc_attr_e( 'Remove product', 'salert' ); ?>"><span class="dashicons dashicons-trash"></span></button>
														</div>
													<?php }
												}
											?>
										</div>
										<input id="table_products_count" type="hidden" name="product-count" value="<?php echo esc_attr( $t_count ); ?>" />
										<button type="button" class="button docopy-table-product"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add Product', 'salert' ); ?></button>
									</div>
								</fieldset>

								<?php if ( class_exists( 'woocommerce' ) ) : ?>
								<div class="salert-premium-row">
									<span class="dashicons dashicons-lock"></span>
									<strong><?php esc_html_e( 'Pull real WooCommerce orders & products automatically', 'salert' ); ?></strong> — <a href="https://wpoperation.com/plugins/sale-alert/" target="_blank" rel="noopener"><?php esc_html_e( 'Go Pro', 'salert' ); ?> →</a>
								</div>
								<?php endif; ?>
							</div>
						</div>

					</section>

					<!-- ============ HELP PANE ============ -->
					<section class="salert-pane" id="pane-help">
						<div class="salert-card">
							<div class="salert-card-body">
								<?php require_once SALERT_PATH.'inc/how-to-use.php'; ?>
							</div>
						</div>
					</section>

					<!-- ============ MORE PANE ============ -->
					<!-- ============ FREE VS PRO PANE ============ -->
					<section class="salert-pane" id="pane-compare">
						<?php
						$upgrade_url = 'https://wpoperation.com/plugins/sale-alert/';
						$rows        = array(
							array( __( 'Display Sales Notification To Customers', 'salert' ), true, true ),
							array( __( 'Display Real-time Sales Notification', 'salert' ), false, true ),
							array( __( 'Add real WooCommerce Products', 'salert' ), false, true ),
							array( __( 'Popup With Sound', 'salert' ), false, true ),
							array( __( 'Analytics Dashboard (Click Status)', 'salert' ), false, true ),
							array( __( 'Product Rating As Popup', 'salert' ), false, true ),
							array( __( 'Live Preview Configuration', 'salert' ), true, true ),
							array( __( 'Multiple Modern Templates', 'salert' ), false, true ),
							array( __( 'Trust Badges', 'salert' ), false, true ),
							array( __( 'Notification Timer', 'salert' ), false, true ),
							array( __( 'Multilingual Ready', 'salert' ), __( 'Partially', 'salert' ), __( 'Fully', 'salert' ) ),
							array( __( 'Translation Ready', 'salert' ), true, true ),
							array( __( 'Major Browser Compatible', 'salert' ), true, true ),
							array( __( 'Responsive - Mobile Friendly', 'salert' ), true, true ),
							array( __( 'Fast and Friendly Support', 'salert' ), false, true ),
						);
						$cell        = function ( $v ) {
							if ( true === $v ) {
								return '<span class="dashicons dashicons-yes-alt salert-yes"></span><span class="screen-reader-text">' . esc_html__( 'Yes', 'salert' ) . '</span>';
							}
							if ( false === $v ) {
								return '<span class="dashicons dashicons-dismiss salert-no"></span><span class="screen-reader-text">' . esc_html__( 'No', 'salert' ) . '</span>';
							}
							return esc_html( $v );
						};
						?>
						<div class="salert-card">
							<header class="salert-card-head">
								<h2><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Free vs Pro', 'salert' ); ?></h2>
								<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener" class="salert-btn-primary"><?php esc_html_e( 'Upgrade to Pro', 'salert' ); ?></a>
							</header>
							<div class="salert-card-body">
								<table class="salert-compare">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Feature', 'salert' ); ?></th>
											<th><?php esc_html_e( 'Free', 'salert' ); ?></th>
											<th class="salert-compare-pro"><?php esc_html_e( 'Pro', 'salert' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $rows as $row ) : ?>
											<tr>
												<td><?php echo esc_html( $row[0] ); ?></td>
												<td><?php echo $cell( $row[1] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $cell. ?></td>
												<td class="salert-compare-pro"><?php echo $cell( $row[2] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $cell. ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
								<p class="salert-compare-cta">
									<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener" class="salert-btn-primary salert-btn-lg"><span class="dashicons dashicons-awards"></span> <?php esc_html_e( 'Upgrade to Salert Pro', 'salert' ); ?></a>
								</p>
							</div>
						</div>
					</section>

					<section class="salert-pane" id="pane-more">
						<div class="salert-more-grid">
							<div class="salert-card salert-promo">
								<span class="dashicons dashicons-admin-appearance"></span>
								<h3><?php esc_html_e( 'Try Our Themes', 'salert' ); ?></h3>
								<p><?php esc_html_e( 'Looking for stunning WordPress themes? Why not try ours?', 'salert' ); ?></p>
								<a href="https://wpoperation.com/themes/" target="_blank" rel="noopener" class="salert-btn-primary"><?php esc_html_e( 'View Themes', 'salert' ); ?></a>
							</div>
							<div class="salert-card salert-promo">
								<span class="dashicons dashicons-sos"></span>
								<h3><?php esc_html_e( 'Need Help?', 'salert' ); ?></h3>
								<p><?php esc_html_e( 'Our support team is always ready for your questions.', 'salert' ); ?></p>
								<a href="https://wpoperation.com/contact" target="_blank" rel="noopener" class="salert-btn-secondary"><?php esc_html_e( 'Create Ticket', 'salert' ); ?></a>
							</div>
							<div class="salert-card salert-promo salert-promo-pro">
								<span class="dashicons dashicons-awards"></span>
								<h3><?php esc_html_e( 'Salert Pro', 'salert' ); ?></h3>
								<p><?php esc_html_e( 'Real WooCommerce orders, sounds, page targeting, background images & more.', 'salert' ); ?></p>
								<a href="https://wpoperation.com/plugins/sale-alert/" target="_blank" rel="noopener" class="salert-btn-primary"><?php esc_html_e( 'Upgrade Now', 'salert' ); ?></a>
							</div>
						</div>
					</section>

				</div><!-- /.salert-main -->

				<!-- Live Preview -->
				<aside class="salert-preview-col">
					<div class="salert-preview-sticky">
						<div class="salert-card salert-preview-card">
							<header class="salert-card-head">
								<h2><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Live Preview', 'salert' ); ?></h2>
								<button type="button" class="button-link salert-replay" id="salert-replay" title="<?php esc_attr_e( 'Replay animation', 'salert' ); ?>"><span class="dashicons dashicons-update"></span></button>
							</header>
							<div class="salert-preview-stage">
								<div class="salert-device">
									<div class="salert-device-bar"><span></span><span></span><span></span></div>
									<div class="salert-device-body">
										<div class="popup_position bottomRight" id="salert-preview-position">
											<div class="popup_template clearfix animated border radius boxs" id="popup_template">
												<div class="popup-item clearfix">
													<span class="close btn-close"><button type="button" class="close-btn-demo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></button></span>
													<img class="pimg" src="<?php echo esc_url( plugin_dir_url( __FILE__ ).'images/100.png' ); ?>" alt="">
													<p>
														<?php esc_html_e( 'John from Australia has just purchased', 'salert' ); ?><br>
														<small class="time"><?php esc_html_e( '16 mins ago', 'salert' ); ?></small>
													</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</aside>

			</div><!-- /.salert-layout -->

			<!-- Sticky Save Bar -->
			<div class="salert-savebar">
				<span class="save-notice"><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'You have unsaved changes', 'salert' ); ?></span>
				<span class="salert-savebar-spacer"></span>
				<button type="submit" class="button button-primary salert-btn"><span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Settings', 'salert' ); ?></button>
			</div>

			</form>
		</div>
		<?php
	}

		/**
		 * @since 1.2.3
		 * Sanitize array or string
		 * */
		public function sanitize_form_setting_array($input){

			if( is_string($input) ){
				$input = sanitize_text_field($input);
			}elseif( is_array($input) ){
				foreach ( $input as $key => $value ) {
					if ( is_array($value) ) {
						$value = self::sanitize_form_setting_array($value);
					}
					else {
						$value = sanitize_text_field($value);
					}
				}
			}
			return $input;
		}

	public function salert_save_settings_with_ajax(){
		if( isset( $_POST['fields'] ) ) {
			parse_str( $_POST['fields'], $settings );
		}else {
			return;
		}

		$this->salert_settings = array(
			'popup-enable' 				=> (int) isset($settings['popup-enable']),
		    'popup-start-time' 			=> sanitize_text_field($settings['popup-start-time']),
		    'popup-stay-time' 			=> sanitize_text_field($settings['popup-stay-time']),
		    'popup-time-interval-from' 	=> sanitize_text_field($settings['popup-time-interval-from']),
		    'popup-time-interval-to' 	=> sanitize_text_field($settings['popup-time-interval-to']),
		    'popup-position' 			=> sanitize_text_field($settings['popup-position']),
		    'popup-animation' 			=> sanitize_text_field($settings['popup-animation']),
		    'image-position' 			=> sanitize_text_field($settings['image-position']),
		    'image-style' 				=> sanitize_text_field($settings['image-style']),
		    'bg-color' 					=> sanitize_hex_color($settings['bg-color']),
		    'container-width' 			=> sanitize_text_field($settings['container-width']),
		    'inner-padding' 			=> sanitize_text_field($settings['inner-padding']),
			'border-enable' 			=> (int) isset($settings['border-enable']),
			'border-color' 				=> sanitize_hex_color($settings['border-color']),
			'border-width' 				=> sanitize_text_field($settings['border-width']),
			'border-radius' 			=> sanitize_text_field($settings['border-radius']),
			'text-color' 				=> sanitize_hex_color($settings['text-color']),
			'font-size' 				=> sanitize_text_field($settings['font-size']),
			'text-transform' 			=> sanitize_text_field($settings['text-transform']),
		    'popup-names' 				=> sanitize_text_field($settings['popup-names']),
			'popup-countries' 			=> sanitize_text_field($settings['popup-countries']),
			'popup-timeperiod' 			=> sanitize_text_field($settings['popup-timeperiod']),
			'popup-timeago' 			=> sanitize_text_field($settings['popup-timeago']),
			'popup-products' 			=> self::sanitize_form_setting_array($settings['popup-products']),
			'product-count' 			=> sanitize_text_field($settings['product-count']),
			'popup-contents' 			=> wp_kses_post($settings['popup-contents']),
			'close-btn' 				=> sanitize_text_field($settings['close-btn']),
			'enable-resp' 				=> (int) isset($settings['enable-resp']),
			'box-shadow'				=> (int) isset($settings['box-shadow']),
			'text-separator' 			=> sanitize_text_field($settings['text-separator'])
		);
		check_ajax_referer('salert_ajax_nonce_wpop','security');
		update_option( 'salert_save_settings', $this->salert_settings );
		wp_send_json_success();
	}

}

new Salert_Admin_Settings();