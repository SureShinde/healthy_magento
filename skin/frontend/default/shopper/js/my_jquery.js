function show_hide(){
	var hjQ=jQuery.noConflict();
	//current.style.display = 'none';	
	hjQ('.three_week').toggle();
	hjQ('.show_two').toggle();
	hjQ('.hide_two').toggle();
	hjQ('.two_week').toggle();
	hjQ('.phase_1_title').toggle();
}

jQuery('document').ready(function(){
	jQuery('.jquery-ui-datepicker' ).datepicker({dateFormat:'dd-MM yy'});
	jQuery('#kit-date-control').change(function(){
		var original = jQuery(this).data('original');
		var value = jQuery(this).val();
		var buttonEl = jQuery(this).next('.kit-date-toggler');
		if(original === value){
			buttonEl.prop('disabled', true);
		}else{
			buttonEl.prop('disabled', false);
		}
	});
});