/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2014 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
	jQuery('#signup-form').unbind().submit(function() {
    var popupId = jQuery(this).closest(".dialog").attr('data-popupid');
    var $this = this;
    var closeAction = jQuery(this).closest(".dialog").find('.dialogClose').attr('onclick');
    var submitText = jQuery(".registerPopup button").text();
    jQuery(".registerPopup button").text(workingText);    
    jQuery(".registerPopup button").attr('disabled','disabled');
    jQuery.ajax({
      type: "POST",
      url: jQuery(this).attr('action'),
      data: jQuery(this).serialize()+"&popupId="+popupId, 
      dataType:'json', 
      success: function(response){              
  			if(!response.exceptions) {  				
          mb_popup.gaTracking(mb_popups[popupId],'User popup registration completed');
          mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[popupId]);
          successMsg = successMsg.replace("{{var coupon_code}}",response.coupon);
          jQuery($this).closest(".dialogBody").html(successMsg);          
          if(parseInt(successAction)==2){
            setTimeout(function(){
              mb_popup.closeDialog(mb_popups[popupId])
            }, actionDelay);                  
          }else if(parseInt(successAction)==3){
            setTimeout(function(){
              window.location.href = successUrl;
            }, actionDelay);                  
          }          
  			} else {
          jQuery(".registerPopup button").text(submitText);
          jQuery(".registerPopup button").removeAttr('disabled');         
          var errorHtml = '';
  				for(var i = 0; i < response.exceptions.length; i++) {
  					errorHtml += '<p>'+response.exceptions[i]+'</p>';
  				}          
          jQuery($this).closest(".dialog").find(".error").html('');
          jQuery($this).closest(".dialog").find(".error").append(errorHtml);
          jQuery($this).closest(".dialog").find(".error").fadeIn();
          setTimeout(function(){
            jQuery($this).closest(".dialog").find(".error").fadeOut();
          }, 3500);                    
  			}             
      }             
    });  
  
	});