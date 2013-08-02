
jQuery(function($){

	if ( $.cookie( 'cftpeuc-seen' ) )
		return;

	$('#cftpeuc').show();
	mb = $('body').css('margin-bottom');
	$('body').css('margin-bottom',mb+=$('#cftpeuc').outerHeight());

	$('#cftpeuc-ok').click(function(e){
		$('#cftpeuc').slideUp();
		e.preventDefault();
		$.cookie( 'cftpeuc-seen', 1, { expires: 365, path: cftpeuc.path } );
	});

});
