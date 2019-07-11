// $(document).ready(function(){
//     $document.on('change', '.wp-travel-enable-additional-currency-sale', function(){
//         $('.enable-additional-currency-wrapper').fadeToggle();
    
//       });
    

//     // Hide/show additional currency fields on toggle change.
//     $(document).on( 'change', '.wp-travel-enable-additional-currency', function(){
//         var parent = $(this).closest('.panel-body');
//         if($(this).is( ':checked' )){
//             parent.find('.wp-travel-additional-currency-hide-show').fadeIn(200);
//         }
//         else {
//             parent.find('.wp-travel-additional-currency-hide-show').fadeOut(200);
//         }
//     });
//     $('.wp-travel-enable-additional-currency').trigger('change');
  
  
//     // Hide/show sale additional currency fields on toggle change of sale...
//     $(document).on( 'change', '.wp-travel-enable-variation-price-sale', function(){
//         var parent = $(this).closest('.panel-body');
//         if($(this).is( ':checked' )){
//             parent.find('.wp-travel-sale-additional-currency-hide-show').fadeIn();
//         }
//         else {
//             parent.find('.wp-travel-sale-additional-currency-hide-show').fadeOut();
//         }
//     });
//     $('.wp-travel-enable-variation-price-sale').trigger('change');

// });

// function show_additional_price_fields() {
//     var pricing_options_type_additional = $( '#wp-travel-pricing-option-type' ).val();
//     var parent = $(this).closest('.panel-body');
//     if ( 'single-price' == pricing_options_type_additional && $('#single-enable-additional-currency') .is(':checked')){
//         $('#additional-currency-sale-price-hide').removeAttr('disabled').closest('.price-option-row').removeClass( 'hidden' );
//     }
//     else {
//         $('#additional-currency-sale-price-hide').attr( 'disabled', 'disabled' ).closest( '.price-option-row' ).addClass ('hidden');
//     }
// }

// $(document).on('click', '#single-enable-additional-currency', function() {
//     show_additional_price_fields();
// });