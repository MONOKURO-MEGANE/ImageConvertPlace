$(document).ready(function() {
	$('#modal-open').on('click', function() {
		$(this).parents().find('div#contact').addClass('modal-display').fadeIn();
	});
	$('.modal-close').on('click', function() {
		$(this).parents().find('div#contact').removeClass('modal-display').fadeOut();
	});
});
