jQuery('#panel').submit(function(evt) {
    evt.preventDefault();
    var optdata =  {
        'action' : "login",
        "username": jQuery( "input[name*='woocommerce_yourpay_settings[username]']" ).val(),
        "password": jQuery( "input[name*='woocommerce_yourpay_settings[password]']" ).val()
    };
    jQuery.post(
        ajaxurl, optdata,
        function(){
            window.location.replace("admin.php?page=yourpay_settings");
        }
    );
});
jQuery("#create_account").click(function(evt){
    evt.preventDefault();
    jQuery.post(
        ajaxurl,
        {
            'action': 'create_account'
        }
        );
});