/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2014 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
                                                                                                                                                                                                                                                                          /*dpqzsjhiunbhfcjse.dpn*/
jQuery('.newsletterPopup form').unbind().submit(function() {     
  if(validateEmail(jQuery(this).closest(".dialog").find(".newsletterPopup input[name='email']").val())){
        var $this = this;
        var submitText = jQuery(this).closest(".dialog").find(".newsletterPopup button").text();
        jQuery(this).closest(".dialog").find(".newsletterPopup button").text(workingText);
        jQuery(this).closest(".dialog").find(".newsletterPopup button").attr("disabled", "disabled");
        var popupId = jQuery(this).closest(".dialog").attr('data-popupid');             
        jQuery.ajax({  
          type: "POST",  
          url: subscribeUrl,  
          data: jQuery(this).serialize()+"&popupId="+popupId, 
          dataType:'json',  
          success: function(response)  {  
        			if(!response.exceptions) {
                successMsg = successMsg.replace("{{var coupon_code}}",response.coupon);                  				
                jQuery(".popupid"+popupId+" .dialogBody").html(successMsg);
                mb_popup.gaTracking(mb_popups[popupId],'Popup Newsletter subscribed');                
                mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[popupId]);                         
                if(parseInt(successAction)==2){
                  setTimeout(function(){
                    mb_popup.closeDialog(mb_popups[popupId])
                  }, actionDelay);                  
                }else if(parseInt(successAction)==3){
                  setTimeout(function(){
                    window.location.href = successUrl;
                  }, actionDelay);                  
                }         
        			}else{
                jQuery(".newsletterPopup button").text(submitText);
                jQuery(".newsletterPopup button").removeAttr('disabled');         
                var errorHtml = '';
        				for(var i = 0; i < response.exceptions.length; i++) {
        					errorHtml += '<p>'+response.exceptions[i]+'</p>';
        				}          
                jQuery($this).closest(".dialog").find(".error").html('');
                jQuery($this).closest(".dialog").find(".error").append(errorHtml);
                jQuery($this).closest(".dialog").find(".error").fadeIn();
                setTimeout(function(){
                  jQuery($this).closest(".dialog").find(".error").fadeOut();
                }, 2500); 
              }                                                        
          },
          error: function(error)  {
            var errorMsg = JSON.stringify(error.responseText, null, 4);
            errorMsg = errorMsg.split('{\\"success'); 
            errorMsg = errorMsg[0].substring(1);                 
            alert(errorMsg);
            jQuery(this).closest(".dialog").find(".newsletterPopup button").removeAttr("disabled");
          }                  
        }); 
  }else{
      jQuery(".dialog").find(".error").html('');
      jQuery(".dialog").find(".error").append(errorText);
      jQuery(".dialog").find(".error").fadeIn();
      setTimeout(function(){
        jQuery(".dialog").find(".error").fadeOut();
      }, 2500);     
      //alert(errorText);    
      return false;
  }
});


function validateEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);                   
}