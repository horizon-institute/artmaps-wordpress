<?php
remove_action('login_form', 'openid_wp_login_form');
wp_print_scripts(array('jquery', 'jquery-ui-complete'));
?>

<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />

<?php wp_print_styles(array('jquery-theme', 'artmaps-login')); ?>

<script type="text/javascript">
var providers = {
	"google" : { "url" : "https://www.google.com/accounts/o8/id" },
	"yahoo" : { "url" : "http://me.yahoo.com/" },
	"aol" : {
		"label" : "Enter your AOL screenname.",
		"url" : "http://openid.aol.com/{username}"
	},
	"myopenid" : {
		"label" : "Enter your MyOpenID username.",
		"url" : "http://{username}.myopenid.com/"
	},
	"openid" : {
		"label" : "Enter your OpenID.",
		"url" : "{username}"
	},
	"livejournal" : {
		"label" : "Enter your Livejournal username.",
		"url" : "http://{username}.livejournal.com/"
	},
	"wordpress" : {
		"label" : "Enter your Wordpress.com username.",
		"url" : "http://{username}.wordpress.com/"
	},
	"blogger" : {
		"label" : "Your Blogger account",
		"url" : "http://{username}.blogspot.com/"
	},
	"verisign" : {
		"label" : "Your Verisign username",
		"url" : "http://{username}.pip.verisignlabs.com/"
	},
	"claimid" : {
		"label" : "Your ClaimID username",
		"url" : "http://claimid.com/{username}"
	}
};

jQuery(function($) {
    $("#artmaps-nav-bar").detach().prependTo($(document.body));
    $("#loginform > p").detach().prependTo($("#artmaps-password"));
    $("#artmaps-login-tabs").tabs();

    var login = $("#loginform");
    var oid = $("#openid_identifier");
    $(".artmaps-openid-provider").each(function(i, e) {
        var l = $(e);
        l.click(function () {
            var id = l.attr("id").replace("artmaps-openid-provider-", "");
            var provider = providers[id];
            if(typeof provider.label !== 'undefined') {
                var username = window.prompt(provider.label);
                if(username != null && username != "") {
                    var url = provider.url.replace("{username}", username);
                    oid.val(url);
                    login.submit();
                }
            } else {
                oid.val(provider.url);
                login.submit();
            }
        });
    });

    var displayError = function(msg) {
        alert(msg);
        return false;
    };

    var login = $("#loginform");
    $("#artmaps-register-submit").click(function() {
        var dn = $("#artmaps-register-name");
        var un = $("#artmaps-register-login");
        var pw = $("#artmaps-register-password");
        var pwc = $("#artmaps-register-password-confirm");
        var em = $("#artmaps-register-email");
        var bg = $("#artmaps-register-blog");
        if(dn.val() == null || dn.val() == "")
            return displayError("Please enter a display name");
        if(un.val() == null || un.val() == "")
            return displayError("Please enter a username");
        if(pw.val() == null || pw.val() == "")
            return displayError("Please enter a password");
        if(pw.val() != pwc.val())
            return displayError("Passwords do not match");
        if(em.val() == null || em.val() == "")
            return displayError("Please enter a valid email address");
        jQuery.ajax("<?= admin_url('admin-ajax.php', is_ssl() ? 'https' : 'http')?>", {
            "type": "post",
            "data": {
                "action": "artmaps.createUser",
                "username": un.val(),
                "password": pw.val(),
                "email": em.val(),
                "displayName": dn.val(),
                "blog": bg.val()
            },
            "success": function(response) {
                if(response != true)
                  return displayError(response);
                $("#user_login").val(un.val());
                $("#user_pass").val(pw.val());
                login.submit();
            }});
        return false;
    });
});
</script>