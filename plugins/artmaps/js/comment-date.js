jQuery(function($) {
   $("#comments").find("time").each(function(i, e) {
       $(e).timeago();
   });
   $("#comments").find(".comment-author").contents().filter(
           function() { 
               return this.nodeType == Node.TEXT_NODE; 
           }).each(
                   function(i, e) {
       if(e.data.indexOf("on") > -1)
           e.data = " ";
   });
});