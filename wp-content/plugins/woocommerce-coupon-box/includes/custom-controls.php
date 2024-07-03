<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
if ( ! class_exists( 'WP_Customize_Control' ) ) {
	require_once ABSPATH . 'wp-includes/class-wp-customize-control.php';
}
if ( class_exists( 'WP_Customize_Control' ) ):
	class WP_Customize_WCB_Radio_Icons_Control extends WP_Customize_Control {
		public $type = 'wcb_radio_icons';

		public function render_content() {
			?>
            <div class="customize-control-content">
				<?php
				if ( ! empty( $this->label ) ) {
					?>
                    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php
				}

				if ( ! empty( $this->description ) ) {
					?>
                    <span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
					<?php
				}
				$class = $this->id;
				$class = str_replace( '[', '-', $class );
				$class = str_replace( ']', '', $class );
				?>
                <div class="wcb-radio-icons-wrap <?php echo esc_attr( $class ); ?>">
					<?php
					foreach ( $this->choices as $key => $value ) {
						?>
                        <label class="wcb-radio-icons-label <?php if ( $key == $this->value() )
	                        echo esc_attr( 'wcb-radio-icons-active' ) ?>">
                            <input type="radio" style="display: none;" name="<?php echo esc_attr( $this->id ); ?>"
                                   value="<?php echo esc_attr( $key ); ?>" <?php $this->link(); ?> <?php checked( esc_attr( $key ), $this->value() ); ?>/>
                            <span class="<?php echo esc_attr( $key ); ?>"></span>
                        </label>
						<?php
					}
					?>
                </div>
            </div>
			<?php
		}

	}

	/**
	 * Googe Font Select Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */
	class WCB_Google_Font_Select_Custom_Control extends WP_Customize_Control {
		/**
		 * The type of control being rendered
		 */
		public $type = 'wcb_google_fonts';
		/**
		 * The list of Google Fonts
		 */
		private $fontList = false;
		/**
		 * The saved font values decoded from json
		 */
		private $fontValues = [];
		/**
		 * The index of the saved font within the list of Google fonts
		 */
		private $fontListIndex = 0;
		/**
		 * The number of fonts to display from the json file. Either positive integer or 'all'. Default = 'all'
		 */
		private $fontCount = 'all';
		/**
		 * The font list sort order. Either 'alpha' or 'popular'. Default = 'alpha'
		 */
		private $fontOrderBy = 'alpha';

		/**
		 * Get our list of fonts from the json file
		 */
		public function __construct( $manager, $id, $args = array(), $options = array() ) {
			parent::__construct( $manager, $id, $args );
			// Get the font sort order
			if ( isset( $this->input_attrs['orderby'] ) && strtolower( $this->input_attrs['orderby'] ) === 'popular' ) {
				$this->fontOrderBy = 'popular';
			}
			// Get the list of Google fonts
			if ( isset( $this->input_attrs['font_count'] ) ) {
				if ( 'all' != strtolower( $this->input_attrs['font_count'] ) ) {
					$this->fontCount = ( abs( (int) $this->input_attrs['font_count'] ) > 0 ? abs( (int) $this->input_attrs['font_count'] ) : 'all' );
				}
			}
			$this->fontList = $this->getGoogleFonts( 'all' );
			// Decode the default json font value
			$this->fontValues = json_decode( $this->value() );
			// Find the index of our default font within our list of Google fonts
//			$this->fontListIndex = $this->getFontIndex( $this->fontList, $this->fontValues->font );
		}

		/**
		 * Enqueue our scripts and styles
		 */
		public function enqueue() {
//			wp_enqueue_script( 'woocommerce-coupon-box-select2-google-font-js', VI_WOOCOMMERCE_COUPON_BOX_JS . 'select2.js', array( 'jquery' ) );
			wp_enqueue_script( 'woocommerce-coupon-box-custom-controls-google-font-js', VI_WOOCOMMERCE_COUPON_BOX_JS . 'google-select-customizer.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_style( 'woocommerce-coupon-box-custom-controls-google-font-css', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'google-select-customizer.css', array(), '', 'all' );
//			wp_enqueue_style( 'woocommerce-coupon-box-select2-css', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'select2.min.css' );
		}

		/**
		 * Export our List of Google Fonts to JavaScript
		 */
		public function to_json() {
			parent::to_json();
			$this->json['skyrocketfontslist'] = $this->fontList;
		}

		/**
		 * Render the control in the customizer
		 */
		public function render_content() {
			$fontCounter  = 0;
			$isFontInList = false;
			$fontListStr  = '';

			if ( ! empty( $this->fontList ) ) {
				?>
                <div class="google_fonts_select_control">
					<?php if ( ! empty( $this->label ) ) { ?>
                        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php } ?>
					<?php if ( ! empty( $this->description ) ) { ?>
                        <span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
					<?php } ?>
                    <input type="hidden" id="<?php echo esc_attr( $this->id ); ?>"
                           name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $this->value() ); ?>"
                           class="customize-control-google-font-selection" <?php $this->link(); ?> />
                    <div class="wcb-google-fonts">
                        <select class="wcb-google-fonts-list" control-name="<?php echo esc_attr( $this->id ); ?>">
                            <option></option>
							<?php
							foreach ( $this->fontList as $key => $value ) {
								$fontCounter ++;
								$fontListStr .= '<option value="' . $value->family . '" ' . selected( $this->fontValues->font, $value->family, false ) . '>' . $value->family . '</option>';
								if ( $this->fontValues->font === $value->family ) {
									$isFontInList = true;
								}
								if ( is_int( $this->fontCount ) && $fontCounter === $this->fontCount ) {
									break;
								}
							}
							//							if ( !$isFontInList && $this->fontListIndex ) {
							//								// If the default or saved font value isn't in the list of displayed fonts, add it to the top of the list as the default font
							//								$fontListStr = '<option value="' . $this->fontList[$this->fontListIndex]->family . '" ' . selected( $this->fontValues->font, $this->fontList[$this->fontListIndex]->family, false ) . '>' . $this->fontList[$this->fontListIndex]->family . ' (default)</option>' . $fontListStr;
							//							}
							// Display our list of font options
							echo $fontListStr;
							?>
                        </select>
                    </div>
                </div>
				<?php
			}
		}

		/**
		 * Find the index of the saved font in our multidimensional array of Google Fonts
		 */
		public function getFontIndex( $haystack, $needle ) {
			foreach ( $haystack as $key => $value ) {
				if ( $value->family == $needle ) {
					return $key;
				}
			}

			return false;
		}

		/**
		 * Return the list of Google Fonts from our json file. Unless otherwise specfied, list will be limited to 30 fonts.
		 */
		public function getGoogleFonts( $count = 30 ) {
			// Google Fonts json generated from https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=AIzaSyAn72E0zx7-8Ec7omB1c3fdDQj4HKeElDo
//			$fontFile = trailingslashit( get_template_directory_uri() ) . 'inc/wcb-google-fonts-alphabetical.json';
			$fontFile = 'https://www.googleapis.com/webfonts/v1/webfonts?sort=' . $this->fontOrderBy . '&key=AIzaSyAn72E0zx7-8Ec7omB1c3fdDQj4HKeElDo';
//			if ( $this->fontOrderBy === 'popular' ) {
//				$fontFile = trailingslashit( get_template_directory_uri() ) . 'inc/wcb-google-fonts-popularity.json';
//			}

			$request = wp_remote_get( $fontFile );
			if ( is_wp_error( $request ) ) {
				return "";
			}

			$body    = wp_remote_retrieve_body( $request );
			$content = json_decode( $body );

			if ( $count == 'all' ) {
				return $content->items;
			} else {
				return array_slice( $content->items, 0, $count );
			}
		}
	}

	/**
	 * Alpha Color Picker Custom Control
	 *
	 * @author Braad Martin <http://braadmartin.com>
	 * @license http://www.gnu.org/licenses/gpl-3.0.html
	 * @link https://github.com/BraadMartin/components/tree/master/customizer/alpha-color-picker
	 */
	class WCB_Customize_Alpha_Color_Control extends WP_Customize_Control {
		/**
		 * The type of control being rendered
		 */
		public $type = 'wcb-alpha-color';
		/**
		 * Add support for palettes to be passed in.
		 *
		 * Supported palette values are true, false, or an array of RGBa and Hex colors.
		 */
		public $palette;
		/**
		 * Add support for showing the opacity value on the slider handle.
		 */
		public $show_opacity;

		/**
		 * Enqueue our scripts and styles
		 */
		public function enqueue() {
			wp_enqueue_script( 'woocommerce-coupon-box-custom-controls-alpha-color-picker-js', VI_WOOCOMMERCE_COUPON_BOX_JS . 'alpha-color-picker.js', array(
				'jquery',
				'wp-color-picker'
			), '1.0', true );
			wp_enqueue_style( 'woocommerce-coupon-box-custom-controls-alpha-color-picker-css', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'alpha-color-picker.css', array( 'wp-color-picker' ), '1.0', 'all' );
		}

		/**
		 * Render the control in the customizer
		 */
		public function render_content() {

			// Process the palette
			if ( is_array( $this->palette ) ) {
				$palette = implode( '|', $this->palette );
			} else {
				// Default to true.
				$palette = ( false === $this->palette || 'false' === $this->palette ) ? 'false' : 'true';
			}

			// Support passing show_opacity as string or boolean. Default to true.
			$show_opacity = ( false === $this->show_opacity || 'false' === $this->show_opacity ) ? 'false' : 'true';

			?>
            <label>
				<?php // Output the label and description if they were passed in.
				if ( isset( $this->label ) && '' !== $this->label ) {
					echo '<span class="customize-control-title">' . sanitize_text_field( $this->label ) . '</span>';
				}
				if ( isset( $this->description ) && '' !== $this->description ) {
					echo '<span class="description customize-control-description">' . sanitize_text_field( $this->description ) . '</span>';
				} ?>
            </label>
            <input class="wcb-alpha-color-control" type="text" data-show-opacity="<?php echo $show_opacity; ?>"
                   data-palette="<?php echo esc_attr( $palette ); ?>"
                   data-default-color="<?php echo esc_attr( $this->settings['default']->default ); ?>" <?php $this->link(); ?> />
			<?php
		}
	}

	/**
	 * Image Radio Button Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */
	class WCB_Image_Radio_Button_Custom_Control extends WP_Customize_Control {
		/**
		 * The type of control being rendered
		 */
		public $type = 'wcb_image_radio_button';

		/**
		 * Enqueue our scripts and styles
		 */
		public function enqueue() {
			wp_enqueue_style( 'woocommerce-coupon-box-custom-controls-radio-image-css', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'customizer-radio-image.css', array(), '1.0', 'all' );
		}

		/**
		 * Render the control in the customizer
		 */
		public function render_content() {
			?>
            <div class="wcb_image_radio_button_control">
				<?php if ( ! empty( $this->label ) ) { ?>
                    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php } ?>
				<?php if ( ! empty( $this->description ) ) { ?>
                    <span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php } ?>

				<?php foreach ( $this->choices as $key => $value ) { ?>
                    <label class="wcb-radio-button-label">
                        <input type="radio" name="<?php echo esc_attr( $this->id ); ?>"
                               value="<?php echo esc_attr( $key ); ?>" <?php $this->link(); ?> <?php checked( esc_attr( $key ), $this->value() ); ?>/>
                        <img src="<?php echo esc_attr( $value['image'] ); ?>"
                             alt="<?php echo esc_attr( $value['name'] ); ?>"
                             title="<?php echo esc_attr( $value['name'] ); ?>"/>
                    </label>
				<?php } ?>
            </div>
			<?php
		}
	}

endif;
