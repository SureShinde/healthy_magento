/*
 * SimpleModal Basic Modal Dialog
 * http://simplemodal.com
 *
 * Copyright (c) 2013 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
var firstItem;
jQuery(function ($) {
	// Load dialog on page load
	//$('#basic-modal-content').modal();

	// Load dialog on click
	$('#basic-modal .basic').click(function (e) {
		$('#basic-modal-content').modal({
			minWidth:"90%",
			minHeight:"75%",
			position: [110]
		});

		return false;
	});

	// Move discount above shipping
	$('#shopping-cart-totals-table tbody tr').each(function(key, item){
		if(key === 0){ firstItem = item; }
		var html = $(item ).html();
		if( html.indexOf('Shipping') > 0 && html.indexOf('Handling') > 0 ){
			$( firstItem).after($(item));
		}
	});
});