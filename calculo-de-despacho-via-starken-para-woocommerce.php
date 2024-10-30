<?php

/**
 * Plugin Name: Despacho vía Starken Pro para WooCommerce
 * Plugin URI: https://andres.reyes.dev
 * Description: Cálculo de Despacho vía Starken Pro para WooCommerce. Incluye despacho a domicilio express y agencia (normal y express)
 * Version: 2022.05.28
 * Author: AndresReyesDev
 * Contributors: AndresReyesDev
 * Author URI: https://andres.reyes.dev
 * License: MIT License
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: arg_starken
 *
 * WC requires at least: 5.0
 * WC tested up to: 6.5.1
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ARG_STARKEN_VERSION', '2022.05.28' );
define( 'ARG_STARKEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ARG_STARKEN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ARG_STARKEN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'ARG_STARKEN_PLUGIN_API_URL', 'https://www.anyda.xyz/starken/' );
define( 'ARG_STARKEN_PLUGIN_LOGO', ARG_STARKEN_PLUGIN_URL . 'assets/images/logo.svg' );
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function wc_andresreyesdev_starken_init() {
		if ( ! class_exists( 'WC_AndresReyesDev_Starken' ) ) {
			class WC_AndresReyesDev_Starken extends WC_Shipping_Method {
				private $hide_agency_shipping;
				private $hide_express_shipping;
				private $api;
				private $origin;
				private $show_logo_checkout;

				private $enable_default;
				private $default_weight;
				private $default_height;
				private $default_width;
				private $default_length;

				public function __construct() {
					$this->id                 = 'wc_andresreyesdev_starken';
					$this->method_title       = __( 'Starken' );
					$this->method_description = __( 'Cálculo de Despacho vía Starken Pro' );

					$this->init();

					$this->enabled = $this->settings['enabled'] ?? 'yes';
					$this->title   = $this->settings['title'] ?? __( 'Starken', 'arg_starken' );
					$this->api     = $this->settings['api'] ?? __( '', 'arg_starken' );
					$this->origin  = $this->settings['origin'] ?? __( '1', 'arg_starken' );

					$this->hide_agency_shipping  = $this->settings['hide_agency'] ?? 'no';
					$this->hide_express_shipping = $this->settings['hide_express'] ?? 'no';
					$this->show_logo_checkout    = $this->settings['show_logo_checkout'] ?? 'no';

					$this->enable_default = $this->settings['enable_default'] ?? 'no';
					$this->default_weight = $this->settings['default_weight'] ?? '250';
					$this->default_height = $this->settings['default_height'] ?? '25';
					$this->default_width  = $this->settings['default_width'] ?? '25';
					$this->default_length = $this->settings['default_length'] ?? '25';
				}

				function init() {
					$this->init_form_fields();
					$this->init_settings();

					add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
				}

				function init_form_fields() {
					$weight_unit    = get_option( 'woocommerce_weight_unit' );
					$dimension_unit = get_option( 'woocommerce_dimension_unit' );

					$this->form_fields = [
						'enabled'            => [
							'title'       => __( 'Activo', 'arg_starken' ),
							'type'        => 'checkbox',
							'description' => __( 'Activar método de despacho.', 'arg_starken' ),
							'default'     => 'yes'
						],
						'title'              => [
							'title'       => __( 'Título', 'arg_starken' ),
							'type'        => 'text',
							'description' => __( 'El título será mostrado en el despacho', 'arg_starken' ),
							'default'     => __( 'Starken', 'arg_starken' )
						],
						'api'                => [
							'title'       => __( 'API', 'arg_starken' ),
							'type'        => 'text',
							'description' => __( 'Obtén tu API Key <strong>GRATIS 🎉!</strong>. Sólo regístrate en <a href="https://www.anyda.xyz/" target="_blank">anyda.xyz</a>', 'arg_starken' ),
							'default'     => __( '', 'arg_starken' )
						],
						'origin'             => [
							'title'       => __( 'Origen', 'arg_starken' ),
							'type'        => 'select',
							'description' => __( 'Comuna de origen para el Envío. Nota: Si despachas desde alguna comuna de la Provincia de Santiago (incluido Puente Alto y San Bernardo) debes seleccionar como origen SANTIAGO.', 'arg_starken' ),
							'options'     => $this->get_origin()
						],
						'hide_agency'        => [
							'title'       => __( 'Ocultar Despacho a Agencias', 'arg_starken' ),
							'type'        => 'checkbox',
							'description' => __( 'Oculta el despacho a Agencias de Starken', 'arg_starken' ),
							'default'     => 'no',
						],
						'hide_express'       => [
							'title'       => __( 'Ocultar Despacho Express', 'arg_starken' ),
							'type'        => 'checkbox',
							'description' => __( 'Oculta el servicio de Despacho Express de Starken', 'arg_starken' ),
							'default'     => 'no',
						],
						'enable_default'     => [
							'title'       => __( 'Activar Tamaño Mínimo', 'starken' ),
							'type'        => 'checkbox',
							'description' => __( 'En caso de que el producto no tenga tamaño tomará estos valores.', 'arg_starken' ),
							'default'     => 'yes'
						],
						'default_weight'     => [
							'title'       => __( 'Peso por Defecto', 'starken' ),
							'type'        => 'text',
							'description' => __( 'Sólo números y signo (de ser necesario) en ' . $weight_unit, 'arg_starken' ),
							'default'     => __( '250', 'starken' )
						],
						'default_height'     => [
							'title'       => __( 'Altura por Defecto', 'starken' ),
							'type'        => 'text',
							'description' => __( 'Sólo números y signo (de ser necesario) en ' . $dimension_unit, 'arg_starken' ),
							'default'     => __( '25', 'starken' )
						],
						'default_width'      => [
							'title'       => __( 'Ancho por Defecto', 'starken' ),
							'type'        => 'text',
							'description' => __( 'Sólo números y signo (de ser necesario) en ' . $dimension_unit, 'arg_starken' ),
							'default'     => __( '25', 'starken' )
						],
						'default_length'     => [
							'title'       => __( 'Largo por Defecto', 'starken' ),
							'type'        => 'text',
							'description' => __( 'Sólo números y signo (de ser necesario) en ' . $dimension_unit, 'arg_starken' ),
							'default'     => __( '25', 'starken' )
						],
						'show_logo_checkout' => [
							'title'       => __( 'Mostrar el Logo de Starken en los envíos disponibles', 'arg_starken' ),
							'type'        => 'checkbox',
							'description' => __( 'Esto reemplazará el texto Starken (o el indicado en Título) por el logo de <strong>Starken</strong> (' . $this->show_checkout_logo() . ') junto al despacho seleccionado', 'arg_starken' ),
							'default'     => 'no',
						],
					];
				}

				public function get_origin() {
					$transient_key        = 'wc_starken_origin';
					$transient_expiration = 60 * 60 * 24;
					$data                 = get_transient( $transient_key );

					if ( ! $data ) {
						$response = wp_remote_get( ARG_STARKEN_PLUGIN_API_URL . 'cities' );

						try {
							$data = json_decode( $response['body'], true );
							set_transient( $transient_key, $data, $transient_expiration );
						} catch ( Exception $ex ) {
						}
					}

					return $data;
				}

				private function show_checkout_logo() {
					return '<img src="' . ARG_STARKEN_PLUGIN_LOGO . '" style="width: 26px; margin-bottom: -7px" />';
				}

				private function fix_format( $value ) {
					$value = str_replace( ',', '.', $value );

					return $value;
				}

				public function process_calculator( $alternative ) {

					if ( empty( $alternative->precio ) || $alternative->precio == 0 ) {
						return [];
					}

					if ( $this->hide_agency_shipping == 'yes' && in_array( strtolower( $alternative->entrega ), [ 'agencia', 'sucursal' ] ) ) {
						return [];
					}

					if ( $this->hide_express_shipping == 'yes' && in_array( strtolower( $alternative->servicio ), [ 'expreso', 'express' ] ) ) {
						return [];
					}

					return [
						'entrega'  => ucwords( strtolower( $alternative->entrega ) ),
						'servicio' => ucwords( strtolower( $alternative->servicio ) ),
						'precio'   => (int) $alternative->precio,
					];
				}

				public function calculate_shipping( $package = [] ) {
					$weight         = 0.0;
					$body           = '';
					$volume         = 0.0;
					$dimensions     = [];
					$single_product = true;
					$productIds     = [];
					$packageType    = 'BULTO';

					foreach ( WC()->cart->get_cart() as $cartItem ) {
						if ( $cartItem['quantity'] > 1 ) {
							$single_product = false;
						}

						if ( $cartItem['data']->has_dimensions() ) {
							$dimensions[] = (float) wc_get_dimension( $this->fix_format( $cartItem['data']->get_width() ), 'cm' );
							$dimensions[] = (float) wc_get_dimension( $this->fix_format( $cartItem['data']->get_height() ), 'cm' );
							$dimensions[] = (float) wc_get_dimension( $this->fix_format( $cartItem['data']->get_length() ), 'cm' );
							$volume       += ( (float) wc_get_dimension( $this->fix_format( $cartItem['data']->get_width() ), 'cm' ) * (float) wc_get_dimension( $this->fix_format( $cartItem['data']->get_height() ), 'cm' ) * (float) wc_get_dimension( $this->fix_format( $cartItem['data']->get_length() ), 'cm' ) ) * $cartItem['quantity'];
							$weight       += wc_get_dimension( $this->fix_format( $cartItem['data']->get_weight() ), 'kg' ) * $cartItem['quantity'];
							$productIds[] = $cartItem['product_id'];
						} else {
							if ( $this->enable_default ) {
								$dimensions[] = (float) wc_get_dimension( $this->fix_format( $this->default_width ), 'cm' );
								$dimensions[] = (float) wc_get_dimension( $this->fix_format( $this->default_height ), 'cm' );
								$dimensions[] = (float) wc_get_dimension( $this->fix_format( $this->default_length ), 'cm' );
								$volume       += ( (float) wc_get_dimension( $this->fix_format( $this->default_width ), 'cm' ) * (float) wc_get_dimension( $this->fix_format( $this->default_height ), 'cm' ) * (float) wc_get_dimension( $this->fix_format( $this->default_length ), 'cm' ) ) * $cartItem['quantity'];
								$weight       += (float) wc_get_dimension( $this->fix_format( $this->default_weight ), 'kg' ) * $cartItem['quantity'];
								$productIds[] = $cartItem['product_id'];
							}
						}
					}

					if ( $volume <= 2250 && $weight <= 0.3 ) {
						$packageType = 'SOBRE';
					}

					if ( count( $productIds ) > 1 ) {
						$single_product = false;
					}

					if ( ! $single_product ) {
						$width  = max( $dimensions );
						$height = sqrt( ( $volume / $width ) * 2 / 3 );
						$length = $volume / $width / $height;
					} else {
						$width  = $dimensions[0];
						$height = $dimensions[1];
						$length = $dimensions[2];
					}

					if ( $width <= 0 || $height <= 0 || $length <= 0 || $weight <= 0 ) {
						return;
					}

					$payload = [
						'route'   => [
							'origin'      => $this->origin,
							'destination' => [
								'state' => $package['destination']['state'],
								'city'  => $package['destination']['city'],
							]
						],
						'package' => [
							'type'   => $packageType,
							'height' => (float) number_format( $height, 2 ),
							'width'  => (float) number_format( $width, 2 ),
							'length' => (float) number_format( $length, 2 ),
							'weight' => (float) number_format( $weight, 2 ),
						]
					];

					$response = wp_remote_post( ARG_STARKEN_PLUGIN_API_URL . 'quote', [
						'headers'     => [
							'Authorization' => 'Bearer ' . $this->api,
							'Content-Type'  => 'application/json',
							'Accept'        => 'application/json',
						],
						'body'        => json_encode( $payload ),
						'redirect'    => 0,
						'data_format' => 'body',
					] );


					if ( ! is_wp_error( $response ) ) {
						if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
							$body = wp_remote_retrieve_body( $response );
						}
					}

					$result = json_decode( $body );

					foreach ( $result->alternativas as $alternativa ) {

						$alternative = $this->process_calculator( $alternativa );

						if ( empty( $alternative ) ) {
							continue;
						} else {
							$rate = [
								'id'       => $this->id . '_' . strtolower( $alternative['entrega'] ) . '_' . strtolower( $alternative['servicio'] ),
								'label'    => ( $this->title ?? 'Starken' ) . ' ' . $alternative['entrega'] . ' ' . $alternative['servicio'],
								'cost'     => $alternative['precio'],
								'calc_tax' => 'per_item'
							];
							$this->add_rate( $rate );
						}
					}
				}
			}
		}
	}

	add_action( 'woocommerce_shipping_init', 'wc_andresreyesdev_starken_init' );

	function wc_andresreyesdev_starken( $methods ) {
		$methods['wc_andresreyesdev_starken'] = 'WC_AndresReyesDev_Starken';

		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'wc_andresreyesdev_starken' );
}

final class AndresReyesDev_Starken {
	protected static $_instance = null;
	protected $cities;

	public static function init() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
			self::$_instance->do_init();
		}

		return self::$_instance;
	}

	public function do_init() {
		add_filter( 'woocommerce_billing_fields', [ $this, 'billing_fields' ] );
		add_filter( 'woocommerce_shipping_fields', [ $this, 'shipping_fields' ] );
		add_filter( 'woocommerce_form_field_city', [ $this, 'form_field_city' ], 10, 4 );
		add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
		add_action( 'admin_notices', [ $this, 'needs_configuration' ] );
		add_filter( 'woocommerce_states', [ $this, 'load_country_states' ] );
		add_filter( 'woocommerce_rest_prepare_report_customers', [ $this, 'set_state_local' ] );
		add_filter( 'woocommerce_get_country_locale', [ $this, 'wc_change_state_label_locale' ] );
		add_filter( 'woocommerce_default_address_fields', [ $this, 'wc_reorder_region_field' ] );
		add_filter( 'woocommerce_cart_shipping_method_full_label', [ $this, 'filter_woocommerce_cart_shipping_method_full_label' ], 10, 2 );
	}

	public function filter_woocommerce_cart_shipping_method_full_label( $label, $method ) {
		$starken = get_option( 'woocommerce_wc_andresreyesdev_starken_settings' );

		if ( $starken['show_logo_checkout'] == 'yes' && $method->method_id == 'wc_andresreyesdev_starken' ) {
			return '<img class="wc_andresreyesdev_starken_logo" src="' . ARG_STARKEN_PLUGIN_LOGO . '" alt="Starken" title="Starken">' . substr( $label, strlen( $starken['title'] ) );
		}

		return $label;
	}

	public function wc_reorder_region_field( $address_fields ) {
		$address_fields['state']['priority'] = 60;
		$address_fields['city']['priority']  = 65;

		return $address_fields;
	}

	public function wc_change_state_label_locale( $locale ) {
		$locale['CL']['state']['label'] = __( 'Región', 'woocommerce' );
		$locale['CL']['city']['label']  = __( 'Comuna', 'woocommerce' );

		return $locale;
	}

	public function billing_fields( $fields ) {
		$fields['billing_city']['type'] = 'city';

		return $fields;
	}

	function needs_configuration() {
		$starken = get_option( 'woocommerce_wc_andresreyesdev_starken_settings' );
		if ( empty( $starken['api'] ) ) {
			echo '<div class="notice notice-error">
			<h3>¡Falta configurar el plugin Starken!</h3>
			<p>Para poder usar el plugin de <strong>Starken</strong> es necesario que te registres en <a href="https://www.anyda.xyz" target="_blank">https://www.anyda.xyz</a> y obtengas la API Key gratis para usar el servicio. Luego debes ingresarla en la página de <a href="admin.php?page=wc-settings&tab=shipping&section=wc_andresreyesdev_starken">ajustes del plugin de Starken</a>.</p>
			</div>';
		}
	}

	public function shipping_fields( $fields ) {
		$fields['shipping_city']['type'] = 'city';

		return $fields;
	}

	public function form_field_city( $field, $key, $args, $value ) {
		if ( ( ! empty( $args['clear'] ) ) ) {
			$after = '<div class="clear"></div>';
		} else {
			$after = '';
		}

		// Required markup
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
		} else {
			$required = '';
		}

		// Custom attribute handling
		$custom_attributes = [];
		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		// Validate classes
		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';
		if ( $args['label'] ) {
			$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
		}

		$country_key = $key == 'billing_city' ? 'billing_country' : 'shipping_country';
		$current_cc  = WC()->checkout->get_value( $country_key );
		$state_key   = $key == 'billing_city' ? 'billing_state' : 'shipping_state';
		$current_sc  = WC()->checkout->get_value( $state_key );

		$cities = $this->get_cities( $current_cc );
		$field  .= '<span class="woocommerce-input-wrapper">';
		if ( is_array( $cities ) ) {
			$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="city_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">
                <option value="">' . __( 'Select an option&hellip;', 'woocommerce' ) . '</option>';

			if ( $current_sc && isset( $cities[ $current_sc ] ) ) {
				$dropdown_cities = $cities[ $current_sc ];
			} elseif ( is_array( reset( $cities ) ) ) {
				$dropdown_cities = [];
			} else {
				$dropdown_cities = $cities;
			}
			foreach ( $dropdown_cities as $city_name ) {
				if ( is_array( $city_name ) ) {
					$city_name = $city_name[0];
				}
				$field .= '<option value="' . esc_attr( $city_name ) . '" ' . selected( $value, $city_name, false ) . '>' . $city_name . '</option>';
			}
			$field .= '</select>';
		} else {
			$field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
		}

		if ( $args['description'] ) {
			$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
		}
		$field .= '</span>';

		$field .= '</p>' . $after;

		return $field;
	}

	public function get_cities( $cc = null ) {
		if ( empty( $this->cities ) ) {
			$this->load_country_cities();
		}
		if ( ! is_null( $cc ) ) {
			return $this->cities[ $cc ] ?? false;
		} else {
			return $this->cities;
		}
	}

	public function load_country_cities() {
		$transient_key        = 'wc_starken_destination';
		$transient_expiration = 60 * 60 * 12;
		$data                 = get_transient( $transient_key );

		if ( ! $data ) {
			$response = wp_remote_get( ARG_STARKEN_PLUGIN_API_URL . 'country' );
			try {
				$data = json_decode( $response['body'], true );
				set_transient( $transient_key, $data, $transient_expiration );
			} catch ( Exception $ex ) {

			}
		}

		$this->cities = apply_filters( 'arg_starken_city_select_cities', $data );
	}

	public function load_scripts() {
		if ( defined( 'WC_VERSION' ) ) {
			if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {
				wp_enqueue_script( 'arg-starken-city-select-script', ARG_STARKEN_PLUGIN_URL . 'assets/js/script.js', [ 'jquery', 'woocommerce' ], ARG_STARKEN_VERSION, true );

				wp_enqueue_style( 'arg-starken-city-select-style', ARG_STARKEN_PLUGIN_URL . 'assets/css/style.css', [], ARG_STARKEN_VERSION, false );

				wp_localize_script( 'arg-starken-city-select-script', 'arg_starken_city_select_params', [
					'cities'                => $this->get_cities(),
					'i18n_select_city_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
				] );
			}
		}
	}

	public function load_country_states( $states ) {
		$allowed = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );

		if ( version_compare( WC_VERSION, '6.0', '<' ) ) {
			$chile = [
				'CL' => [
					'CL-AI' => __( 'Aisén del General Carlos Ibañez del Campo', 'woocommerce' ),
					'CL-AN' => __( 'Antofagasta', 'woocommerce' ),
					'CL-AP' => __( 'Arica y Parinacota', 'woocommerce' ),
					'CL-AR' => __( 'La Araucanía', 'woocommerce' ),
					'CL-AT' => __( 'Atacama', 'woocommerce' ),
					'CL-BI' => __( 'Biobío', 'woocommerce' ),
					'CL-CO' => __( 'Coquimbo', 'woocommerce' ),
					'CL-LI' => __( 'Libertador General Bernardo O\'Higgins', 'woocommerce' ),
					'CL-LL' => __( 'Los Lagos', 'woocommerce' ),
					'CL-LR' => __( 'Los Ríos', 'woocommerce' ),
					'CL-MA' => __( 'Magallanes', 'woocommerce' ),
					'CL-ML' => __( 'Maule', 'woocommerce' ),
					'CL-NB' => __( 'Ñuble', 'woocommerce' ),
					'CL-RM' => __( 'Región Metropolitana de Santiago', 'woocommerce' ),
					'CL-TA' => __( 'Tarapacá', 'woocommerce' ),
					'CL-VS' => __( 'Valparaíso', 'woocommerce' ),
				]
			];

			$states = array_merge( $states, $chile );
		}

		return $states;
	}

	public function set_state_local( $response ) {
		static $states;
		if ( ! isset( $states[ $response->data['country'] ] ) ) {
			$states[ $response->data['country'] ] = WC()->countries->get_states( $response->data['country'] );
		}
		if ( isset( $states[ $response->data['country'] ][ $response->data['state'] ] ) ) {
			$response->data['state'] = $states[ $response->data['country'] ][ $response->data['state'] ];
		}

		return $response;
	}
}

AndresReyesDev_Starken::init();
