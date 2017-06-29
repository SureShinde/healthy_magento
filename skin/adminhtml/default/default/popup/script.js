(function(){ 
  jQuery(document).ready(function() {
  
    if ( jQuery.isFunction(jQuery.fn.on) ) {      
        jQuery('body').on('change', '#page_id', function(){                      
          showIdsField();
        });
        jQuery('body').on('change', '#horizontal_position', function(){                      
          showHorizontalPosField();
        });      
    }else{
      jQuery("#page_id").change(function() {
        showIdsField();        
      });
      jQuery("#horizontal_position").change(function() {
        showHorizontalPosField();        
      });              
    }                          
    showIdsField();
    showHorizontalPosField();
    activateTooltip();
    if ( jQuery.isFunction(jQuery.fn.on) ) {
      jQuery('body').on('change', '#width_unit', function(){                      
        widthUnitListener();
      });     
    }else{
      jQuery("#width_unit").change(function() {
        widthUnitListener();        
      });    
    }
    
    widthUnitListener();
  });
  
  function widthUnitListener(){
      var widthUnit = jQuery('#width_unit').val();
      if(widthUnit==1){
        jQuery("#horizontal_position").prop("disabled", false);
      }else{
        jQuery("#horizontal_position").prop("disabled", true);
        jQuery("#horizontal_position").val(1);
      }    
  }
  
  function showIdsField(){
      var showAt = jQuery('#page_id').val();
      if(jQuery.inArray('2', showAt)==-1){
        jQuery('#product_ids').parent().parent().hide();
      }else{
        jQuery('#product_ids').parent().parent().show();
      }
      
      if(jQuery.inArray('3', showAt)==-1){
        jQuery('#category_ids').parent().parent().hide();  
      }else{
        jQuery('#category_ids').parent().parent().show();
      }  
      
      if(jQuery.inArray('6', showAt)==-1){
        jQuery('#specified_url').parent().parent().hide();  
      }else{
        jQuery('#specified_url').parent().parent().show();
      }                                    
  }
  
  function showHorizontalPosField(){
      var verticalPos = jQuery('#horizontal_position').val();
      if(verticalPos==1){
        jQuery('#horizontal_position_px').parent().parent().hide();
      }else{
        jQuery('#horizontal_position_px').parent().parent().show();
      }                                  
  }  
  
  function activateTooltip(){
    jQuery('.popupTooltip').hover(function(e){ // Hover event
    var titleText = jQuery(this).attr('title');
    jQuery(this).data('tiptext', titleText).removeAttr('title');
    jQuery('<p class="tooltip"></p>')
      .html(titleText)
      .appendTo('body')
      .css('top', (e.pageY -50) + 'px')
      .css('left', (e.pageX - 340) + 'px')
      .fadeIn('fast');
    }, function(){ // Hover off event
      jQuery(this).attr('title', jQuery(this).data('tiptext'));
      jQuery('.tooltip').remove();
    });
  }  
})();

function setIssetCookie(cname) {
    var d = new Date();
    d.setTime(d.getTime() + (900*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=1; " + expires + "; path=/";        
}

function getIssetCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}   