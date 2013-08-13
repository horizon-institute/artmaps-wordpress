jQuery(function($) {
    $("time").each(function(i, e) {
        var d = new Date($(e).attr("datetime"));
        $(e).attr("title", d.toLocaleString());
    });
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