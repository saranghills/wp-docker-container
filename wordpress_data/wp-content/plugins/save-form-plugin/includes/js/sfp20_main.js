jQuery(document).ready(function($){

    //Date Picker
    jQuery(function($){
        jQuery( ".date-picker" ).datepicker();
    });
  
    //Ajax Call
    jQuery("#form_submit_button").click(function(e) {
        
        var fname = jQuery('#u_fname').val();
        var lname = jQuery('#u_lname').val();
        var email = jQuery('#u_email').val();
        var phone = jQuery('#u_phone').val();
        var country = jQuery('#u_country').val();
        var dob = jQuery('#u_dob').val();
        var nonce = jQuery('#sfp20_form_nonce_field').val();

        
        if (jQuery('#defaultCheck1').is(":checked"))
        {
            if(fname == '' || lname == '' || email == ''){
                var msg = '<div class="p-2 pb-1 bg-danger text-white text-lg sfp20_ajax_msg d-block"><h4>Please fill the mandatory field(s)</h4></div>';
                jQuery('#form_submit_message').fadeIn().html(msg).delay(2000).fadeOut();
                return;
            }else{
                var data = {
                    'action'     : 'sfp20_ajax', 
                    'u_fname'    : fname,    
                    'u_lname'    : lname,
                    'u_email'    : email,
                    'u_phone'    : phone,
                    'u_country'  : country,
                    'u_dob'      : dob,
                    'u_nonce'    : nonce
                    };
                  
                 jQuery.post(ajaxurl, data, function(response) {
                    jQuery("#form_submit_message").fadeIn().html(response).delay(2000).fadeOut();
                 });
            }
            e.preventDefault();

        }else{

           var msg = '<div class="p-2 pb-1 bg-danger text-white text-lg sfp20_ajax_msg d-block"><h4>Please accept Terms and Conditions</h4></div>';
           jQuery('#form_submit_message').fadeIn().html(msg).delay(2000).fadeOut();
           return;
        }
        
     });

});