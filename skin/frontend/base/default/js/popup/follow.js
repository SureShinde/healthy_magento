jQuery('#pinButton').click(function () {
    showCoupon('Pinterest follow');
});  

window.fbAsyncInit = function() {
  FB.init({
    appId      : '266922980004856',
    xfbml      : true,
    version    : 'v2.1'
  });
  FB.Event.subscribe('edge.create', function(response) {
      showCoupon('Facebook like')
      jQuery(".popupid"+popupId+" .dialogBody").trigger('click');
  });        
};

(function(d, s, id){
   var js, fjs = d.getElementsByTagName(s)[0];
   if (d.getElementById(id)) {return;}
   js = d.createElement(s); js.id = id;
   js.src = "//connect.facebook.net/en_US/sdk.js";
   fjs.parentNode.insertBefore(js, fjs);
 }(document, 'script', 'facebook-jssdk')); 

if (typeof window.twttr == "undefined") {
  window.twttr = (function (d,s,id) {
      var t, js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
      js.src="https://platform.twitter.com/widgets.js";
      fjs.parentNode.insertBefore(js, fjs);
      return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
  }(document, "script", "twitter-wjs"));
}

twttr.ready(function (twttr) {
    twttr.events.bind('click', function (event) { 
      jQuery(".popupid"+popupId+" .dialogBody").trigger('click');
    });
    twttr.events.bind('follow', function(event) {
        showCoupon('Twitter follow'); 
    });
    twttr.events.bind('tweet', function(event) {
        showCoupon('Twitter tweet');
    });        
});     
   
function showCoupon(action){
  mb_popup.gaTracking(mb_popups[popupId],'Popup ' + action);
  mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[popupId]);
  var couponAction = jQuery("#coupon-form").attr('action');
  var couponData = jQuery("#coupon-form").serialize()+"&popupId="+popupId;   
  var timeoutSeconds = 0;
  jQuery(".popupid"+popupId+" .dialogBody").append('<p style="font-size:16px; font-weight:bold; position:absolute;left:10px; top:5px;">'+workingText+'</p>')
  if(action=="Gplus follow"){
    timeoutSeconds = 2000;    
  }
     
  setTimeout(function(){  
    jQuery.ajax({
      type: "POST",
      url: couponAction,
      data: couponData,
      dataType:'json', 
      success: function(response){      
  			if(!response.exceptions) {  				                 
          successMsg = successMsg.replace("{{var coupon_code}}",response.coupon);
          jQuery(".popupid"+popupId+" .dialogBody").html(successMsg);         
  			}            
      }             
    })
  },timeoutSeconds); 
} 

function gplusCallback(jsonParam) {
  if(jsonParam.state=="on"){
    showCoupon('Gplus follow')
    jQuery(".popupid"+popupId+" .dialogBody").trigger('click');
  }
}