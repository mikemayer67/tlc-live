jQuery(document).ready( function() {

   jQuery(".ajax_count").click( function(e) {
      e.preventDefault(); 
      nonce = jQuery(this).attr("data-nonce")

      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : tlcAjax.ajaxurl,
         data : {action: "tlc-livestream-test", has_js:true, nonce: nonce},
         success: function(response) {
            if(response.type == "success") {
               jQuery("#ajax_run_count").html(response.count)
            }
            else {
               alert("Something went wrong")
            }
         }
      })   

   })

})
