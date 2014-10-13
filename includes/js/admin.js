// Cacher le loader et l'overlay 
// quand la page est chargée
jQuery(window).load(function() {
	jQuery("#load").fadeOut(500);
	jQuery("#overlay2").fadeOut(500);
})

// Afficher le loader et l'overlay 
// à chaque submit de formulaire
jQuery('form').submit(function() {
	jQuery("#load").fadeIn(500);
	jQuery("#overlay2").fadeIn(500);
});

jQuery(document).ready(function() {

	jQuery('.fancybox').fancybox({
		'width' : '80%',
		'height' : '90%',
		'autoScale' : false,
		'transitionIn' : 'none',
		'transitionOut' : 'none',
		'type' : 'iframe' 
	});

	var win_height = jQuery(window).height();
	console.log("Window size: ", win_height);
	jQuery('#dashboard_content').css( 'height', win_height );
	jQuery("#dash_iframe").css( 'height', win_height );

	// Afficher le formulaire d'enregistrement
	jQuery('#gotoregister').click(function() {
		jQuery('#login-box').hide();
		jQuery('#register-box').show();

		return false;
	});
	// Afficher le formulaire de login
	jQuery('#gotologin').click(function() {
		jQuery('#login-box').show();
		jQuery('#register-box').hide();

		return false;
	});

	var winWidth = jQuery("#wpcontent").width();
	console.log(winWidth);
	var width = (winWidth / 2) + jQuery('#adminmenuwrap').width();
	console.log(width);

	jQuery("#load").css("left", (width - 10) + 'px');


	// Gestion des exceptions (affichage)
	var url     = jQuery(location).attr('href');
	var pos     = url.indexOf('&');
	var red_url = url.substring(0, pos);
	if (pos != -1) {
		jQuery('#overlay').css('display', 'block');
		var excWidth = jQuery('.izeeException').width();
		var winWidth = jQuery(window).width();
		var value = (winWidth - excWidth) / 2;
		jQuery('.izeeException').css('left', value + 'px' );

		var headerHeight = jQuery('#wpadminbar').height() + 32;
		jQuery('.izeeException').css('top', headerHeight + 'px' );

		setTimeout( function(){ 
			window.location.replace(red_url);
		 }, 5000 );
	}

	// Gestion des exceptions (cacher)
	jQuery('.izeeException .close').click(function() {
		jQuery('.izeeException').css('display', 'none');

		var url     = jQuery(location).attr('href');
		var pos     = url.indexOf('&');
		var red_url = url.substring(0, pos);
		window.location.replace(red_url);
	});

});

function checkPass() {
    //Store the password field objects into variables ...
    var pass1 = document.getElementById('pass1');
    var pass2 = document.getElementById('pass2');
    //Store the Confimation Message Object ...
    var message = document.getElementById('confirmMessage');
    //Set the colors we will be using ...
    var goodColor = "#66cc66";
    var badColor = "#ff6666";
    //Compare the values in the password field
    //and the confirmation field
    if(pass1.value == pass2.value){
    	console.log(pass1.value);
    	if(pass1.value == "") {
    		console.log("empty");
    		pass2.style.backgroundColor = "#fff";
	        message.style.color = "#fff";
	        message.innerHTML = ''
    	} else {
	        //The passwords match.
	        //Set the color to the good color and inform
	        //the user that they have entered the correct password
	        pass2.style.backgroundColor = goodColor;
	        message.style.color = goodColor;
	        message.innerHTML = '<img src="/wp-content/plugins/izeechat/includes/img/tick.png" alt="Passwords Match!">';
	    }
    } else {
        //The passwords do not match.
        //Set the color to the bad color and
        //notify the user.
        pass2.style.backgroundColor = badColor;
        message.style.color = badColor;
        message.innerHTML = '<img src="/wp-content/plugins/izeechat/includes/img/publish_x.png" alt="Passwords Do Not Match!">'
    }
}  