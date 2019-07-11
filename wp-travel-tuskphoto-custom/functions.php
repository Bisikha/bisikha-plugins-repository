<?php
/**
 * ======================================================================
 * This file will have the all the required code for important functions.
 * ======================================================================
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Currency position and formatting with price but without other currency symbol.
 */
function wp_travel_tuskphoto_custom_get_formated_price_currency( $price, $regular_price = false ) {
	$settings = wp_travel_get_settings();
	$currency_position = isset( $settings['currency_position'] ) ? $settings['currency_position'] : 'left';

	$filter_name = 'wp_travel_itinerary_sale_price'; // Filter for customization work support.
	$price_class = 'wp-travel-trip-price-figure';
	if ( $regular_price ) {
		$filter_name = 'wp_travel_itinerary_price';
		$price_class = 'wp-travel-regular-price-figure';
	}

	// Price Format Start.
	$thousand_separator = $settings['thousand_separator'];
	$decimal_separator = $settings['decimal_separator'];
	$number_of_decimals = isset( $settings['number_of_decimals'] ) && ! empty( $settings['number_of_decimals'] ) ? $settings['number_of_decimals'] : 0;
	$price = number_format( $price, $number_of_decimals, $decimal_separator, $thousand_separator );
	// End of Price Format.

	// $currency_element = '<span class="wp-travel-trip-currency">' . wp_travel_get_currency_symbol() . '</span>';
	// $price_element = '<span class="' . $price_class . '">' . esc_html( wp_travel_get_formated_price( $price ) ) . '</span>';
	ob_start();
	switch ( $currency_position ) {
		case 'left':
			?>
			<span class="<?php echo esc_attr( $price_class ); ?>"><?php echo esc_html( $price ); ?></span>
			<?php
			break;
		case 'left_with_space':
			?>
			<span class="<?php echo esc_attr( $price_class ); ?>"><?php echo esc_html( $price ); ?></span>
			<?php
			break;
		case 'right':
			?>
			<span class="<?php echo esc_attr( $price_class ); ?>"><?php echo esc_html( $price ); ?></span>
			<?php
			break;
		case 'right_with_space':
			?>
			<span class="<?php echo esc_attr( $price_class ); ?>"><?php echo esc_html( $price ); ?></span>
			<?php
			break;
	}
	$content = ob_get_contents();
	ob_end_clean();

	return apply_filters( $filter_name, $content, $price );
}

 //function to set the currency symbol
function wp_travel_tuskphoto_currency_symbol( $currency_code, $price_key ){

	global $post;

	$trip_id = $post->ID;

	$pricing_option_type = get_post_meta( $trip_id, 'wp_travel_pricing_option_type', true );

	if ( 'single-price' === $pricing_option_type  ) { // single
		
		$single_enable_additional_currency = get_post_meta( $trip_id, 'single_enable_additional_currency', true );
		if ( 'yes' === $single_enable_additional_currency ) {
			// return 'trip id ' . $trip_id . '--';
			$additional_currency = get_post_meta( $trip_id, 'single_select_additional_currency', true );
			// return $single_select_additional_currency;
			
		}
		elseif ( 'no' === $single_enable_additional_currency ) {
			wp_travel_get_currency_symbol();
		}

	} elseif( 'multiple-price' === $pricing_option_type ) { // multiple.

		$pricing_variation = wp_travel_get_pricing_variation( $trip_id, $price_key );
		// error_log(print_r($pricing_variation, true));

		is_array( $pricing_variation ) && reset( $pricing_variation );
		is_array( $pricing_variation ) && $first_key = key($pricing_variation);		
		$multiple_data = get_post_meta ( $trip_id , 'wp_travel_pricing_options', true );
		error_log(print_r($multiple_data, true));

			foreach (  $multiple_data as $data){

				if ( isset( $data['enable_additional_currency'] ) && 'yes' === $data['enable_additional_currency'] ){ 

				$additional_currency = $pricing_variation[ $first_key ]['select_additional_currency'];
				}			
				else {
					wp_travel_get_currency_symbol();
				}
			}
	}

	if ( isset( $additional_currency ) && ! empty( $additional_currency ) ) {

		if ( 'USD' === $additional_currency ){
			$currency_code = 'US' . '&#36;';
		} elseif ( 'GBP' === $additional_currency ) {
			$currency_code = '&#163;';
		} elseif ( 'CAD' === $additional_currency ) {
			$currency_code = 'C' . '&#36;';
		}elseif ( 'EUR' === $additional_currency ) {
			$currency_code = '&#128;';
		} elseif ( 'BWP' === $additional_currency ) {
			$currency_code = 'BWP';
		} elseif ( 'ZAR' === $additional_currency ) {
			$currency_code = 'ZAR';
		} elseif ( 'BRL' === $additional_currency ) {
			$currency_code = 'R&#36;';
		} else {
			$currency_code = '&#165;';
		}
		
	}
		return $currency_code;

}

add_filter( 'wp_travel_display_currency_symbol', 'wp_travel_tuskphoto_currency_symbol', 10, 2 );

function tusk_photo_sale_enable( $enable_sale, $trip_id , $price_key = null ) {
	$pricing_option_type = get_post_meta( $trip_id, 'wp_travel_pricing_option_type', true );

	if ( 'single-price' === $pricing_option_type ) {
		
		$single_enable_additional_currency = get_post_meta( $trip_id, 'single_enable_additional_currency', true );
		$single_enable_additional_currency_sale = get_post_meta ( $trip_id, 'single_enable_additional_currency_sale' , true );
		
		// echo $single_enable_additional_currency . '-' . $single_enable_additional_currency_sale;die;
		if ( 'yes' === $single_enable_additional_currency ) {
			if ( 'yes' === $single_enable_additional_currency_sale  ) {
				$enable_sale = true;
			} else {
				$enable_sale = false;
			}
		} 
	}
	else {
		// Multiple pricing.
		// $pricing_variation = wp_travel_get_pricing_variation( $trip_id, $price_key );
		// $first_key = key($pricing_variation);
		// $enable_additional_currency = $pricing_variation[ $first_key ]['enable_additional_currency'];
		// $enable_additional_currency_sale = $pricing_variation[ $first_key ]['enable_additional_currency_sale'];
		
		$pricing_options = get_post_meta( $trip_id, 'wp_travel_pricing_options', true );
		if ( is_array( $pricing_options ) && count( $pricing_options ) > 0 ) {
			foreach ( $pricing_options as $pricing_key => $option ) {
				if ( isset( $option['enable_additional_currency'] ) && 'yes' === $option['enable_additional_currency'] ) {
					
					$enable_sale = false;
					if ( isset( $option['enable_additional_currency_sale'] ) && 'yes' === $option['enable_additional_currency_sale'] ) {
						$enable_sale = true;
						break;
					}
				}
			}
		}
	}

	return $enable_sale;
}

add_filter( 'wp_travel_enable_sale', 'tusk_photo_sale_enable', 10, 3 );


function wp_travel_trip_price_customize( $trip_price, $trip_id, $price_key, $is_regular_price ) {
	if ( ! $trip_id ) {
		return;
	}

	// $multiple_data = get_post_meta ( $trip_id , 'wp_travel_pricing_options', true );

	$pricing_option_type = get_post_meta( $trip_id, 'wp_travel_pricing_option_type', true );
	$single_enable_additional_currency = get_post_meta( $trip_id, 'single_enable_additional_currency', true );
	$single_enable_additional_currency_sale = get_post_meta ( $trip_id, 'single_enable_additional_currency_sale' , true );
	// $enable_sale = get_post_meta ( $trip_id, 'wp_travel_enable_sale' , true );
	
	if ( 'single-price' === $pricing_option_type ) { // single

		// $single_additional_currency_amount = ! empty( $trip_pricing_options_data['single_additional_currency_amount'] ) ? $trip_pricing_options_data['single_additional_currency_amount'] : ''; 
		
		
		$additional_currency_sale_amount = get_post_meta ( $trip_id , 'additional_currency_sale_price' , true);
		$amount = get_post_meta ( $trip_id , 'single_additional_currency_amount', true );

		if ( 'yes' === $single_enable_additional_currency ) {
			
			if ('yes' === $single_enable_additional_currency_sale) {
				if ( $is_regular_price ) {
					return $amount;
				} else {
					return $additional_currency_sale_amount;
				}
				
			} else {
				return $amount;
			}
		} else {
			return $trip_price;
		}
		
	 	
	} elseif( 'multiple-price' === $pricing_option_type ) { // multiple.
		
		// $pricing_variation = wp_travel_get_pricing_variation( $trip_id, $price_key );
		// print_r($pricing_variation);
		// $pricing_variation = get_post_meta( $trip_id, 'wp_travel_pricing_options', true );

		$enable_additional_currency = '';
		$select_additional_currency = '';
		$additional_currency_amount = '';	
		$sale_additional_currency_amount = '';

		$pricing_variation = wp_travel_get_pricing_variation( $trip_id, $price_key );
		if ( is_array ( $pricing_variation ) && count( $pricing_variation ) > 0 ){
			reset( $pricing_variation );
			$first_key = key($pricing_variation);
			
			$enable_additional_currency = $pricing_variation[ $first_key ]['enable_additional_currency'];
			$select_additional_currency = $pricing_variation[ $first_key ]['select_additional_currency'];
			$additional_currency_amount = $pricing_variation[ $first_key ]['additional_currency_amount'];
			$enable_additional_currency_sale = $pricing_variation[ $first_key ]['enable_additional_currency_sale'];	
			$sale_additional_currency_amount = $pricing_variation[ $first_key ]['sale_additional_currency_amount'];
			// $wp_travel_prices = $pricing_variation[ $first_key ]['price'];
			$wp_travel_sale_amount = $pricing_variation [ $first_key ][ 'sale_price' ];

			// print_r( $wp_travel_sale_amount );die;			

			if ( 'yes' === $enable_additional_currency ) {

				if ( 'yes' === $enable_additional_currency_sale ) {

					if ( $is_regular_price ) {
						return $additional_currency_amount;
					} else {
						return $sale_additional_currency_amount;
					} 
				}
				else {
					return $additional_currency_amount;
				} 
			}	
			else {
				return $trip_price;
			}
		}
		// return false;
		// return $wp_travel_sale_amount;
	}
	return $trip_price;
}

add_filter( 'wp_travel_trip_price', 'wp_travel_trip_price_customize', 10 , 4 );

function wp_travel_get_min_price_key_modified ($price_key, $pricing_options ) {
	$min_price = 0;
	foreach ( $pricing_options as $pricing_option ) {
		// error_log(print_r($pricing_option, true));
		if ( isset( $pricing_option['enable_additional_currency'] ) && 'yes' === $pricing_option['enable_additional_currency'] ) {
			$current_price = $pricing_option['additional_currency_amount'];
			if ( 'yes' === $pricing_option['enable_additional_currency_sale'] ) {
				$current_price = $pricing_option['sale_additional_currency_amount'];
			}

			if ( ( 0 === $min_price && $current_price > 0 ) || $min_price > $current_price ) { // Initialize min price if 0.
				$min_price = $current_price;
				$price_key = $pricing_option['price_key'];
			}
		}

	}
	
	return $price_key;
}
add_filter ( 'wp_travel_trip_min_price_key' , 'wp_travel_get_min_price_key_modified', 10, 3 );


function wp_travel_save_additional_price_customize( $trip_id ) {

	$single_enable_additional_currency = '';
	if ( isset( $_POST['single_enable_additional_currency'] ) ) {
		$single_enable_additional_currency = $_POST['single_enable_additional_currency'];
	}
	update_post_meta( $trip_id, 'single_enable_additional_currency', $single_enable_additional_currency );

	$single_select_additional_currency = '';
	if ( isset( $_POST['single_select_additional_currency'] ) ) {
		$single_select_additional_currency = $_POST['single_select_additional_currency'];
	}
	update_post_meta( $trip_id, 'single_select_additional_currency', $single_select_additional_currency );

	$single_additional_currency_amount = '';
	if ( isset( $_POST['single_additional_currency_amount'] ) ) {
		$single_additional_currency_amount = $_POST['single_additional_currency_amount'];
	}
	update_post_meta( $trip_id, 'single_additional_currency_amount', $single_additional_currency_amount );

	$enable_additional_currency_sale = '';
	if ( isset ( $_POST['single_enable_additional_currency_sale'] ) ) {
		$enable_additional_currency_sale = $_POST ['single_enable_additional_currency_sale'];
	}
	update_post_meta ( $trip_id, 'single_enable_additional_currency_sale' , $enable_additional_currency_sale  );
	
	$additional_currency_sale_amount = '';
	if ( isset ( $_POST['additional_currency_sale_price'] ) ) {
		$additional_currency_sale_amount = $_POST['additional_currency_sale_price'];
	}
	update_post_meta ( $trip_id, 'additional_currency_sale_price', $additional_currency_sale_amount ); 	
	
	$additional_currency_enable_sale = '';
	if ( isset ( $_POST['enable_additional_currency_sale'] ) ) {
		$additional_currency_enable_sale = $_POST['enable_additional_currency_sale'];
	}
	update_post_meta ( $trip_id , 'enable_additional_currency_sale', $additional_currency_enable_sale  );
}
// save additional custom post meta for custom price and currency...
add_action( 'wp_travel_itinerary_extra_meta_save', 'wp_travel_save_additional_price_customize' );

