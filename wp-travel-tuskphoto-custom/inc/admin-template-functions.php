<?php
remove_action ( 'wp_travel_after_single_title', 'wp_travel_trip_price', 1 );
remove_action ( 'wp_travel_booking_princing_options_list', 'wp_travel_booking_tab_pricing_options_list' );

/**
 * Add html of trip price..
 *
 * @param int  $trip_id ID for current trip.
 * @param bool $hide_rating Boolean value to show/hide rating.
 */
function wp_travel_per_person_custom_trip_price( $trip_id, $hide_rating = false ) {
	$settings = wp_travel_get_settings();

	$trip_pricing_options_data = get_post_meta( $trip_id, 'wp_travel_pricing_options', true );

	$min_price_key = wp_travel_get_min_price_key( $trip_pricing_options_data );
	$regular_price = wp_travel_get_actual_trip_price( $trip_id, $min_price_key, true ); // Third param will return regular price if sale price isn't enabled.
	$trip_price    = wp_travel_get_actual_trip_price( $trip_id, $min_price_key ); // This is actual price.

	$enable_sale = wp_travel_is_enable_sale( $trip_id );

	$currency_code   = ( isset( $settings['currency'] ) ) ? $settings['currency'] : '';
	$currency_symbol = wp_travel_get_currency_symbol( $currency_code );
	$per_person_text = wp_travel_get_price_per_text( $trip_id, $min_price_key, true );
	?>

	<div class="wp-detail-review-wrap">
		<?php do_action( 'wp_travel_single_before_trip_price', $trip_id, $hide_rating ); ?>
		<div class="wp-travel-trip-detail">
			<?php if ( $trip_price ) : ?>
				<div class="trip-price" >

				<?php if ( $enable_sale ) : ?>
					<del>
						<span><?php echo wp_travel_get_formated_price_currency( $regular_price, true, $min_price_key ); ?></span>
					</del>
				<?php endif; ?>
					<span class="person-count">
						<ins>
							<span><?php echo wp_travel_get_formated_price_currency( $trip_price, false, $min_price_key ); ?></span>
						</ins>
						<?php if ( ! empty( $per_person_text ) ) : ?>
							/ <?php echo esc_html( $per_person_text ); ?>
						<?php endif; ?>
					</span>
				</div>
			<?php endif; ?>
		</div>
		<?php do_action( 'wp_travel_single_after_trip_price', $trip_id, $hide_rating ); ?>
	</div>

	<?php
}
add_action ( 'wp_travel_after_single_title', 'wp_travel_per_person_custom_trip_price', -1 );


function wp_travel_tusk_photo_booking_tab_pricing_options_list( $trip_data = null ) {

	if ( '' == $trip_data ) {
		return;
	}
	global $wp_travel_itinerary;

	if ( is_array( $trip_data ) ) {
		global $post;
		$trip_id = $post->ID;
	} elseif ( is_numeric( $trip_data ) ) {
		$trip_id = $trip_data;
	}

	$js_date_format = wp_travel_date_format_php_to_js();

	$settings   = wp_travel_get_settings();
	$form       = new WP_Travel_FW_Form();
	$form_field = new WP_Travel_FW_Field();

	$fixed_departure = get_post_meta( $trip_id, 'wp_travel_fixed_departure', true );
	$show_end_date   = wp_travel_booking_show_end_date();
	$currency_symbol = wp_travel_get_currency_symbol();

	$trip_start_date = get_post_meta( $trip_id, 'wp_travel_start_date', true );
	$trip_end_date   = get_post_meta( $trip_id, 'wp_travel_end_date', true );

	$trip_price      = wp_travel_get_trip_price( $trip_id );
	$regular_price   = $trip_price;

	$enable_sale     = wp_travel_is_enable_sale( $trip_id );
	if ( $enable_sale ) {
		$trip_price = wp_travel_get_trip_sale_price( $trip_id );
	}
	$per_person_text = wp_travel_get_price_per_text( $trip_id );

	$trip_duration       = get_post_meta( $trip_id, 'wp_travel_trip_duration', true );
	$trip_duration       = ( $trip_duration ) ? $trip_duration : 0;
	$trip_duration_night = get_post_meta( $trip_id, 'wp_travel_trip_duration_night', true );
	$trip_duration_night = ( $trip_duration_night ) ? $trip_duration_night : 0;

	$available_pax    = false;
	$booked_pax       = false;
	$pax_limit        = false;
	$general_sold_out = false;

	$status_col = apply_filters( 'wp_travel_inventory_enable_status_column', false, $trip_id );

	$status_msg           = get_post_meta( $trip_id, 'wp_travel_inventory_status_message_format', true );
	$sold_out_btn_rep_msg = apply_filters( 'wp_travel_inventory_sold_out_button', '', $trip_id );

	// Multiple Pricing. [ including single and multiple date ].
	if ( is_array( $trip_data ) ) {
		if ( empty( $trip_data ) ) {
			return;
		}
		$trip_extras_class = new Wp_Travel_Extras_Frontend();
		?>
		<div id="wp-travel-date-price" class="detail-content">
			<div class="availabily-wrapper">
				<ul class="availabily-list additional-col">
					<li class="availabily-heading clearfix">
						<div class="date-from">
							<?php echo esc_html__( 'Pricing Name', 'wp-travel' ); ?>
						</div>
						<div class="date-from">
							<?php echo esc_html__( 'Start', 'wp-travel' ); ?>
						</div>
						<div class="group-size">
							<?php echo esc_html__( 'Group (min-max)', 'wp-travel' ); ?>
						</div>
						<?php if ( $status_col ) : ?>
							<div class="seats-left">
								<?php echo esc_html__( 'Seats Left', 'wp-travel' ); ?>
							</div>
						<?php endif; ?>
						<div class="no-of-pax">
							<?php echo esc_html__( 'Pax', 'wp-travel' ); ?>
						</div>
						<div class="price">
							<?php echo esc_html__( 'Price', 'wp-travel' ); ?>
						</div>
						<div class="action">
							&nbsp;
						</div>
					</li>
					<?php
					foreach ( $trip_data as $price_key => $pricing ) :
						// Set Vars.
						$pricing_name         = isset( $pricing['pricing_name'] ) ? $pricing['pricing_name'] : '';
						$price_key            = isset( $pricing['price_key'] ) ? $pricing['price_key'] : '';
						$pricing_type         = isset( $pricing['type'] ) ? $pricing['type'] : '';
						$pricing_custom_label = isset( $pricing['custom_label'] ) ? $pricing['custom_label'] : '';
						$pricing_option_price = isset( $pricing['price'] ) ? $pricing['price'] : '';
						$pricing_sale_enabled = isset( $pricing['enable_sale'] ) ? $pricing['enable_sale'] : '';
						$pricing_sale_price   = isset( $pricing['sale_price'] ) ? $pricing['sale_price'] : '';
						$pricing_min_pax      = isset( $pricing['min_pax'] ) ? $pricing['min_pax'] : '';
						$pricing_max_pax      = isset( $pricing['max_pax'] ) ? $pricing['max_pax'] : '';

						// mention for the multiple price additional amount here
						$regular_price = $pricing_option_price;
						$trip_price    = $pricing_option_price;
						if ( 'yes' === $pricing_sale_enabled ) {
							$trip_price = $pricing_sale_price;
						}

						$available_dates = wp_travel_get_trip_available_dates( $trip_id, $price_key ); // No need to pass date

						$pricing_sold_out = false;

						$inventory_data        = array(
							'status_message' => __( 'N/A', 'wp-travel' ),
							'sold_out'       => false,
							'available_pax'  => 0,
							'booked_pax'     => 0,
							'pax_limit'      => 0,
							'min_pax'        => $pricing_min_pax,
							'max_pax'        => $pricing_max_pax,
						);
						$pricing_default_types = wp_travel_get_pricing_variation_options();

						if ( is_array( $available_dates ) && count( $available_dates ) > 0 ) { // multiple available dates
							foreach ( $available_dates as $available_date ) {
								// echo $available_date;
								$inventory_data = apply_filters( 'wp_travel_inventory_data', $inventory_data, $trip_id, $price_key, $available_date ); // Need to pass inventory date to get availability as per specific date.

								$pricing_status_msg = $inventory_data['status_message'];
								$pricing_sold_out   = $inventory_data['sold_out'];
								$available_pax      = $inventory_data['available_pax'];
								$booked_pax         = $inventory_data['booked_pax'];
								$pax_limit          = $inventory_data['pax_limit'];
								$min_pax            = $inventory_data['min_pax'];
								$max_pax            = $inventory_data['max_pax'];

								if ( class_exists( 'WP_Travel_Util_Inventory' ) ) {
									$inventory = new WP_Travel_Util_Inventory();
									if ( $inventory->is_inventory_enabled( $trip_id ) && $available_pax ) {
										$pricing_max_pax = $available_pax;
									}
								}
								$max_attr  = 'max=' . $pricing_max_pax;
								$parent_id = sprintf( 'pricing-%s-%s', esc_attr( $price_key ), $available_date );

								$unavailable_class = '';
								$availability      = wp_travel_trip_availability( $trip_id, $price_key, $available_date, $pricing_sold_out );
								if ( ! $availability ) {
									$unavailable_class = 'pricing_unavailable';
								}

								$pricing_type_label = ( 'custom' === $pricing_type ) ? $pricing_custom_label : $pricing_default_types[ $pricing_type ];

								$cart_url = add_query_arg( 'trip_id', get_the_ID(), wp_travel_get_cart_url() );
								if ( 'yes' !== $fixed_departure ) :
									$cart_url = add_query_arg( 'trip_duration', $trip_duration, $cart_url );
								endif;
								$cart_url = add_query_arg( 'price_key', $price_key, $cart_url );
								?>
								<li class="availabily-content clearfix <?php echo esc_attr( $unavailable_class ); ?>">
								<form action="<?php echo esc_url( $cart_url ); ?>" id="<?php echo esc_attr( $parent_id ); ?>" class="wp-travel-add-to-cart-form">
									<div class="date-from">
										<span class="availabily-heading-label"><?php echo esc_html__( 'Pricing Name:', 'wp-travel' ); ?></span>
										<span> <?php echo esc_html( $pricing_name ); ?> </span>
									</div>
									<div class="date-from">
										<span class="availabily-heading-label"><?php echo esc_html__( 'Start:', 'wp-travel' ); ?></span>
										<span> <?php echo esc_html( wp_travel_format_date( $available_date ) ); ?> </span>
									</div>
									<div class="group-size">
										<span class="availabily-heading-label"><?php echo esc_html__( 'Group (Min-Max):', 'wp-travel' ); ?></span>
										<span>
											<?php
											$min = ! empty( $pricing_min_pax ) ? esc_html( $pricing_min_pax . __( ' pax', 'wp-travel' ) ) : esc_html__( 'No size limit', 'wp-travel' );
											$max = ! empty( $pricing_max_pax ) ? esc_html( $pricing_max_pax . __( ' pax', 'wp-travel' ) ) : esc_html__( 'No size limit', 'wp-travel' );
											echo sprintf( '%s - %s', $min, $max );
											?>
										</span>
									</div>									
									<?php
									if ( $status_col ) :

										if ( $pricing_sold_out ) :
											?>
											<div class="status">
												<span class="availabily-heading-label"><?php echo esc_html__( 'Status:', 'wp-travel' ); ?></span>
												<span><?php echo esc_html__( 'SOLD OUT', 'wp-travel' ); ?></span>
											</div>
										<?php else : ?>
											<div class="status">
												<span class="availabily-heading-label"><?php echo esc_html__( 'Status:', 'wp-travel' ); ?></span>
												<span><?php echo esc_html( $pricing_status_msg ); ?></span>

											</div>
											<?php
										endif;
									endif;
									$max_attr = '';
									$min_attr = 'min=1';
									if ( '' !== $pricing_max_pax ) {

										$max_attr = 'max=' . $pricing_max_pax;
									}
									if ( '' !== $pricing_min_pax ) {
										$min_attr = 'min=' . $pricing_min_pax;
									}
									?>
									
									<div class="no-of-pax">
										<input name="pax" type="number" data-parent-id="<?php echo esc_attr( $parent_id ); ?>" <?php echo esc_attr( $min_attr ); ?> <?php echo esc_attr( $max_attr ); ?> placeholder="<?php echo esc_attr__( 'size', 'wp-travel' ); ?>" required data-parsley-trigger="change">
									</div>

									<!-- custom -->
									<?php 
									$pricing_variation = wp_travel_get_pricing_variation( $trip_id, $price_key );
									if ( is_array ( $pricing_variation ) && count( $pricing_variation ) > 0 ){
										reset( $pricing_variation );
										$first_key = key($pricing_variation);
									}
									$enable_additional_currency = $pricing_variation[ $first_key ]['enable_additional_currency'];
									$select_additional_currency = $pricing_variation[ $first_key ]['select_additional_currency'];
									$additional_currency_amount = $pricing_variation[ $first_key ]['additional_currency_amount'];
									$enable_additional_currency_sale = $pricing_variation[ $first_key ]['enable_additional_currency_sale'];	
									$sale_additional_currency_amount = $pricing_variation[ $first_key ]['sale_additional_currency_amount'];
									?>
									<div class="price">
										<span class="availabily-heading-label"><?php echo esc_html__( 'price:', 'wp-travel' ); ?></span>

										<?php if( 'yes' === $enable_additional_currency ) {	

											if ( 'yes' === $enable_additional_currency_sale ) { ?>
												<del>
													<span><?php echo wp_travel_get_formated_price_currency( $additional_currency_amount , true, $price_key ); ?></span>
												</del> 
												<span><?php echo wp_travel_get_formated_price_currency( $sale_additional_currency_amount, true, $price_key  ); ?></span>
			
											<?php }
											else{ ?>
											 	<?php echo wp_travel_get_formated_price_currency( $additional_currency_amount, true, $price_key ); ?>												
													<?php }

											} else if ( $pricing_option_price ) { ?>
									
											<?php if ( 'yes' === $pricing_sale_enabled ) { ?>
												<del>
													<span><?php echo wp_travel_get_formated_price_currency( $regular_price, true, $price_key ); ?></span>
												</del>
											<?php } ?>
												<span class="person-count">
													<ins>
														<span><?php echo wp_travel_get_formated_price_currency( $trip_price, true, $price_key ); ?></span>
													</ins>/<?php echo esc_html( $pricing_type_label ); ?>
												</span>
											<?php } ?>
									</div>
									<!-- custom -->
									<div class="action">
		
										<?php if ( $pricing_sold_out ) : ?>
		
											<p class="wp-travel-sold-out"><?php echo $sold_out_btn_rep_msg; ?></p>
		
										<?php else : 
											// $trip_extras_class = new Wp_Travel_Extras_Frontend();
											if ( $trip_extras_class->has_trip_extras( $trip_id, $price_key ) ) { ?>
												<a href="#0" class="btn btn-primary btn-sm btn-inverse show-booking-row"><?php echo esc_html__( 'Select', 'wp-travel' ); ?></a>
												<?php
											} else { ?>
												<input type="submit" value="<?php echo esc_html__( 'Book now', 'wp-travel' ); ?>" class="btn add-to-cart-btn btn-primary btn-sm btn-inverse" data-parent-id="<?php echo esc_attr( $parent_id ); ?>" >
												<?php
											} 
											// @since 1.9.3 To display group discount pricing lists. 
											do_action( 'wp_travel_booking_after_select_button', $trip_id, $price_key  );
											?>
										<?php endif; ?>
										<input type="hidden" name="trip_date" value="<?php echo esc_attr( $available_date ); ?>" >
										<input type="hidden" name="trip_id" value="<?php echo esc_attr( get_the_ID() ); ?>" />
										<input type="hidden" name="price_key" value="<?php echo esc_attr( $price_key ); ?>" />
									</div>
									<?php if ( $availability ) : // Remove Book now if trip is soldout. ?>
										<div class="wp-travel-booking-row">
											<?php
												/**
												 * Support For WP Travel Tour Extras Plugin.
												 *
												 * @since 1.5.8
												 */
												do_action( 'wp_travel_trip_extras', $price_key, $available_date );
											?>
											<div class="wp-travel-calender-aside">											
												<div class="add-to-cart">
													
													<?php
													if ( 'yes' !== $fixed_departure ) :
														?>
														<input type="hidden" name="trip_duration" value="<?php echo esc_attr( $trip_duration ); ?>" />
														<?php
													endif;
													?>
													<input type="submit" value="<?php echo esc_html__( 'Book now', 'wp-travel' ); ?>" class="btn add-to-cart-btn btn-primary btn-sm btn-inverse" data-parent-id="<?php echo esc_attr( $parent_id ); ?>" >

												</div>
											</div>
										</div>
									<?php endif; ?>
								</form>
								</li>
								<?php
							}
						} else { // Single Date.
							$inventory_data = apply_filters( 'wp_travel_inventory_data', $inventory_data, $trip_id, $price_key ); // Need to pass inventory date to get availability as per specific date.

							$pricing_status_msg = $inventory_data['status_message'];
							$pricing_sold_out   = $inventory_data['sold_out'];
							$available_pax      = $inventory_data['available_pax'];
							$booked_pax         = $inventory_data['booked_pax'];
							$pax_limit          = $inventory_data['pax_limit'];
							$min_pax            = $inventory_data['min_pax'];
							$max_pax            = $inventory_data['max_pax'];

							$pricing_default_types = wp_travel_get_pricing_variation_options();

							$pricing_type_label = ( 'custom' === $pricing_type ) ? $pricing_custom_label : $pricing_default_types[ $pricing_type ];

							if ( class_exists( 'WP_Travel_Util_Inventory' ) ) {
								$inventory = new WP_Travel_Util_Inventory();
								if ( $inventory->is_inventory_enabled( $trip_id ) && $available_pax ) {
									$pricing_max_pax = $available_pax;
								}
							}
							$max_attr  = 'max=' . $pricing_max_pax;
							$parent_id = sprintf( 'pricing-%s', $price_key );

							$cart_url = add_query_arg( 'trip_id', get_the_ID(), wp_travel_get_cart_url() );
							if ( 'yes' !== $fixed_departure ) :
								$cart_url = add_query_arg( 'trip_duration', $trip_duration, $cart_url );
							endif;
							$cart_url = add_query_arg( 'price_key', $price_key, $cart_url );
							?>
							<li id="<?php echo esc_attr( $parent_id ); ?>" class="availabily-content clearfix">
							<form action="<?php echo esc_url( $cart_url ); ?>" id="<?php echo esc_attr( $parent_id ); ?>" class="wp-travel-add-to-cart-form">
								
							<div class="date-from">
									<span class="availabily-heading-label"><?php echo esc_html__( 'Pricing Name:', 'wp-travel' ); ?></span> <span><?php echo esc_html( $pricing_name ); ?></span>
								</div>
								<div class="date-from">
									<span class="availabily-heading-label"><?php echo esc_html__( 'Start:', 'wp-travel' ); ?></span>
									<div class="wp-travel-calender-column1 no-padding ">
		
											<label for=""><?php echo esc_html__( 'Select a Date:', 'wp-travel' ); ?></label>
											<input data-date-format="<?php echo esc_attr( $js_date_format ); ?>" name="trip_date" type="text" data-available-dates="<?php echo ( $available_dates ) ? esc_attr( wp_json_encode( $available_dates ) ) : ''; ?>" readonly class="wp-travel-pricing-dates" required data-parsley-trigger="change" data-parsley-required-message="<?php echo esc_attr__( 'Please Select a Date', 'wp-travel' ); ?>">
		
										</div>
								</div>
								<div class="status">
									<span class="availabily-heading-label"><?php echo esc_html__( 'Group (Min/Max):', 'wp-travel' ); ?></span>
									<span>
										<?php
										$min = ! empty( $pricing_min_pax ) ? esc_html( $pricing_min_pax . __( ' pax', 'wp-travel' ) ) : esc_html__( 'No size limit', 'wp-travel' );
										$max = ! empty( $pricing_max_pax ) ? esc_html( $pricing_max_pax . __( ' pax', 'wp-travel' ) ) : esc_html__( 'No size limit', 'wp-travel' );
										echo sprintf( '%s / %s', $min, $max );
										?>
									</span>
								</div>
								
								<?php
								if ( $status_col ) :

									if ( $pricing_sold_out ) :
										?>
										<div class="status">
											<span class="availabily-heading-label"><?php echo esc_html__( 'Seats Left:', 'wp-travel' ); ?></span>
											<span><?php echo esc_html__( 'SOLD OUT', 'wp-travel' ); ?></span>
										</div>
									<?php else : ?>
										<div class="status">
											<span class="availabily-heading-label"><?php echo esc_html__( 'Seats Left:', 'wp-travel' ); ?></span>
											<span><?php echo esc_html( $pricing_status_msg ); ?></span>
										</div>
										<?php
									endif;
								endif;
								$max_attr = '';
								$min_attr = 'min=1';
								if ( '' !== $pricing_max_pax ) {

									$max_attr = 'max=' . $pricing_max_pax;
								}
								if ( '' !== $pricing_min_pax ) {
									$min_attr = 'min=' . $pricing_min_pax;
								}
								?>
								<div class="no-of-pax">
									<input name="pax" type="number" data-parent-id="<?php echo esc_attr( $parent_id ); ?>" <?php echo esc_attr( $min_attr ); ?> <?php echo esc_attr( $max_attr ); ?> placeholder="<?php echo esc_attr__( 'size', 'wp-travel' ); ?>" required data-parsley-trigger="change">
								</div>
								<div class="price">
									<span class="availabily-heading-label"><?php echo esc_html__( 'price:', 'wp-travel' ); ?></span>
									<?php if ( $pricing_option_price ) : ?>
	
										<?php if ( 'yes' === $pricing_sale_enabled ) : ?>
											<del>
												<span><?php echo wp_travel_get_formated_price_currency( $regular_price, true ); ?></span>
											</del>
										<?php endif; ?>
											<span class="person-count">
												<ins>
													<span><?php echo wp_travel_get_formated_price_currency( $trip_price ); ?></span>
												</ins>/<?php echo esc_html( $pricing_type_label ); ?>
											</span>
									<?php endif; ?>
								</div>
								<div class="action">
	
									<?php if ( $pricing_sold_out ) : ?>
	
										<p class="wp-travel-sold-out"><?php echo $sold_out_btn_rep_msg; ?></p>
	
									<?php else :
										if ( $trip_extras_class->has_trip_extras( $trip_id, $price_key ) ) { 
											?>
											<a href="#0" class="btn btn-primary btn-sm btn-inverse show-booking-row"><?php echo esc_html__( 'Select', 'wp-travel' ); ?></a>
											<?php
											// @since 1.9.3 To display group discount pricing lists. 
											do_action( 'wp_travel_booking_after_select_button', $trip_id, $price_key  );
										} else {
										?>
											<input type="submit" value="<?php echo esc_html__( 'Book now', 'wp-travel' ); ?>" class="btn add-to-cart-btn btn-primary btn-sm btn-inverse" data-parent-id="<?php echo esc_attr( $parent_id ); ?>" >
										
										<?php
										}
											?>
									<?php endif; ?>
									<input type="hidden" name="trip_id" value="<?php echo esc_attr( get_the_ID() ); ?>" />
									<input type="hidden" name="price_key" value="<?php echo esc_attr( $price_key ); ?>" />
									<?php
									if ( 'yes' !== $fixed_departure ) : ?>
											<input type="hidden" name="trip_duration" value="<?php echo esc_attr( $trip_duration ); ?>" />
									<?php endif; ?>
								</div>
								<?php if ( $trip_extras_class->has_trip_extras( $trip_id, $price_key ) ) : ?>
									<div class="wp-travel-booking-row">
										<?php
										/**
										 * Support For WP Travel Tour Extras Plugin.
										 *
										 * @since 1.5.8
										 */
										do_action( 'wp_travel_trip_extras', $price_key );
										?>
										<div class="wp-travel-calender-aside">										
											<div class="add-to-cart">
												
												<input type="submit" value="<?php echo esc_html__( 'Book now', 'wp-travel' ); ?>" class="btn add-to-cart-btn btn-primary btn-sm btn-inverse" data-parent-id="<?php echo esc_attr( $parent_id ); ?>" >

											</div>
										</div>
									</div>
								<?php endif; ?>
								</form>
							</li>
						<?php
						}
					endforeach;
					?>
				</ul>
			</div>
		</div>
		<?php

	} elseif ( is_numeric( $trip_data ) ) { // Single Pricing
		$inventory_data = array(
			'status_message' => __( 'N/A', 'wp-travel' ),
			'sold_out'       => false,
			'available_pax'  => 0,
			'booked_pax'     => 0,
			'pax_limit'      => 0,
			'min_pax'        => '',
			'max_pax'        => 0,
		);

		$inventory_data = apply_filters( 'wp_travel_inventory_data', $inventory_data, $trip_id, '' );

		$pricing_status_msg = $inventory_data['status_message'];
		$pricing_sold_out   = $inventory_data['sold_out'];
		$available_pax      = $inventory_data['available_pax'];
		$booked_pax         = $inventory_data['booked_pax'];
		$pax_limit          = $inventory_data['pax_limit'];
		$min_pax            = $inventory_data['min_pax'];
		$max_pax            = $inventory_data['max_pax'];

		$cart_url = add_query_arg( 'trip_id', get_the_ID(), wp_travel_get_cart_url() );
		if ( 'yes' !== $fixed_departure ) :
			$cart_url = add_query_arg( 'trip_duration', $trip_duration, $cart_url );
		endif;
		$parent_id = 'trip-duration-content';
		?>
		<div id="wp-travel-date-price" class="detail-content">
			<div class="availabily-wrapper">
				<ul class="availabily-list <?php echo 'yes' === $fixed_departure ? 'additional-col' : ''; ?>">
					<li class="availabily-heading clearfix">
						<div class="date-from">
							<?php echo esc_html__( 'Start', 'wp-travel' ); ?>
						</div>
						<?php if ( $show_end_date ) : ?>
							<div class="date-to">
								<?php echo esc_html__( 'End', 'wp-travel' ); ?>
							</div>
						<?php endif; ?>
						<div class="status">
							<?php echo esc_html__( 'Group Size', 'wp-travel' ); ?>
						</div>
						<?php if ( $status_col ) : ?>
							<div class="status">
								<?php echo esc_html__( 'Status', 'wp-travel' ); ?>
							</div>
						<?php endif; ?>
						<div class="price">
							<?php echo esc_html__( 'Price', 'wp-travel' ); ?>
						</div>
						<div class="action">
							&nbsp;
						</div>
					</li>
					<li class="availabily-content clearfix" >
						<form action="<?php echo esc_url( $cart_url ); ?>" id="<?php echo esc_attr( $parent_id ); ?>" class="wp-travel-add-to-cart-form">
							<?php if ( 'yes' == $fixed_departure ) : ?>
								<div class="date-from">
									<span class="availabily-heading-label"><?php echo esc_html__( 'start:', 'wp-travel' ); ?></span>
									<?php echo esc_html( date_i18n( 'l', strtotime( $trip_start_date ) ) ); ?>
									<?php $date_format = get_option( 'date_format' ); ?>
									<?php if ( ! $date_format ) : ?>
										<?php $date_format = 'jS M, Y'; ?>
									<?php endif; ?>
									<span><?php echo esc_html( date_i18n( $date_format, strtotime( $trip_start_date ) ) ); ?></span>
									<input type="hidden" name="trip_date" value="<?php echo esc_attr( $trip_start_date ); ?>">
								</div>
								<?php
								if ( $show_end_date ) :
									?>
									<div class="date-to">
										<?php if ( '' !== $trip_end_date ) : ?>
											<span class="availabily-heading-label"><?php echo esc_html__( 'end:', 'wp-travel' ); ?></span>
											<?php echo esc_html( date_i18n( 'l', strtotime( $trip_end_date ) ) ); ?>
											<span><?php echo esc_html( date_i18n( $date_format, strtotime( $trip_end_date ) ) ); ?></span>
											<input type="hidden" name="trip_departure_date" value="<?php echo esc_attr( $trip_end_date ); ?>">
										<?php else : ?>
											<?php esc_html_e( '-', 'wp-travel' ); ?>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							<?php else : ?>
								<div class="date-from">
									<span class="availabily-heading-label"><?php echo esc_html__( 'start:', 'wp-travel' ); ?></span>
									<?php
									$total_days = 0;
									if ( $trip_duration > 0 || $trip_duration_night > 0 ) {
										$days = $trip_duration > $trip_duration_night ? $trip_duration : $trip_duration_night;
										$days--; // As we need to exclude current selected date.
										$total_days = $days ? $days : $total_days;
									}
									$start_field = array(
										'label'         => esc_html__( 'start', 'wp-travel' ),
										'type'          => 'date',
										'name'          => 'trip_date',
										'placeholder'   => esc_html__( 'Arrival date', 'wp-travel' ),
										'class'         => 'wp-travel-pricing-days-night',
										'validations'   => array(
											'required' => true,
										),
										'attributes'    => array(
											'data-parsley-trigger' => 'change',
											'data-parsley-required-message' => esc_attr__( 'Please Select a Date', 'wp-travel' ),
											'data-totaldays' => $total_days,
										),
										'wrapper_class' => 'date-from',
									);
									$form_field->init()->render_input( $start_field );
									?>
								</div>
								<div class="date-to">
									<?php
									$end_field = array(
										'label'       => esc_html__( 'End', 'wp-travel' ),
										'type'        => 'date',
										'name'        => 'trip_departure_date',
										'placeholder' => esc_html__( 'Departure date', 'wp-travel' ),
									);
									$end_field = wp_parse_args( $end_field, $start_field );
									$form_field->init()->render_input( $end_field );
									?>
								</div>
							<?php endif; ?>
							<div class="status">
								<span class="availabily-heading-label"><?php echo esc_html__( 'Group Size:', 'wp-travel' ); ?></span>
								<span><?php echo esc_html( wp_travel_get_group_size() ); ?></span>
							</div>
							<?php if ( $status_col ) : ?>

								<div class="status">
									<span class="availabily-heading-label"><?php echo esc_html__( 'Status:', 'wp-travel' ); ?></span>
									<span><?php echo esc_html( $pricing_status_msg ); ?></span>
								</div>

								<?php endif; ?>
							<?php
							if ( class_exists( 'WP_Travel_Util_Inventory' ) && ! $trip_price ) :
								// display price unavailable text
								$no_price_text = isset( $settings['price_unavailable_text'] ) && '' !== $settings['price_unavailable_text'] ? $settings['price_unavailable_text'] : '';
								echo '<div class="price"><strong>' . esc_html( $no_price_text ) . '</strong></div>';
							else :
								?>
							<?php endif ?>
								
							<div class="price">
								<span class="availabily-heading-label"><?php echo esc_html__( 'price:', 'wp-travel' ); ?></span>

								<?php $pricing_option_type = get_post_meta( $trip_id, 'wp_travel_pricing_option_type', true ); ?>

								<?php if ( 'single-price' === $pricing_option_type) { ?>

									<?php $amount = get_post_meta ( $trip_id , 'single_additional_currency_amount', true );?>
									<?php $single_enable_additional_currency = get_post_meta( $trip_id, 'single_enable_additional_currency', true );?>

									<?php if ( $single_enable_additional_currency ) { 
										
										$single_enable_additional_currency_sale = get_post_meta ( $trip_id, 'single_enable_additional_currency_sale' , true );
										
										if ( $single_enable_additional_currency_sale ) {
										$additional_currency_sale_amount = get_post_meta ( $trip_id , 'additional_currency_sale_price' , true);
										?>
										<del>	
											<span><?php echo wp_travel_get_formated_price_currency( $amount, true ); ?></span>
										</del>	

										<span><?php echo wp_travel_get_formated_price_currency( $additional_currency_sale_amount, true); ?></span>

										<span class="person-count"> <?php } 

										else { ?>
											<span><?php echo wp_travel_get_formated_price_currency( $amount, true ); ?></span>		
																				
										<span class="person-count"> <?php
										} 
									}

									elseif ( $enable_sale ) { ?>
										<del>	
											<span><?php echo wp_travel_get_formated_price_currency( $regular_price, true ); ?></span>
										</del>
											<span><?php echo wp_travel_get_formated_price_currency( $trip_price, true ); ?></span>								
											<span class="person-count">
									<?php }

									else { ?>
										<ins>							
											<span><?php echo wp_travel_get_formated_price_currency( $trip_price ); ?></span>
										</ins>/<?php echo esc_html( $per_person_text ); ?>
									<?php }
									}  ?>
								</span>
								</div> 
									
							
							<div class="action">
								<?php
								// if ( $inventory_enabled && $general_sold_out ) :
								if ( $general_sold_out ) :
									?>

									<p class="wp-travel-sold-out"><?php echo $sold_out_btn_rep_msg; ?></p>

								<?php else : ?>
									<?php $pax = 1; ?>

									<input type="hidden" name="trip_id" value="<?php echo esc_attr( $trip_id ); ?>">
									<input type="hidden" name="pax" value="<?php echo esc_attr( $pax ); ?>">
									<input type="hidden" name="trip_duration" value="<?php echo esc_attr( $trip_duration ); ?>">
									<input type="hidden" name="trip_duration_night" value="<?php echo esc_attr( $trip_duration_night ); ?>">
									
									<input type="submit" value="<?php echo esc_html__( 'Book now', 'wp-travel' ); ?>" class="btn add-to-cart-btn btn-primary btn-sm btn-inverse" data-parent-id="<?php echo esc_attr( $parent_id ); ?>" >
										
								<?php endif; ?>
							</div>
							<div class="wp-travel-booking-row-single-price">
								<?php do_action( 'wp_travel_trip_extras' ); ?>
							</div>
						</form>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

}
add_action( 'wp_travel_booking_princing_options_list', 'wp_travel_tusk_photo_booking_tab_pricing_options_list' );