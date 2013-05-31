<?php
if(!class_exists('ArtMapsOpenID')) {
class ArtMapsOpenID {

    public function displayHead() {
        remove_action('login_form', 'openid_wp_login_form');
        wp_print_scripts('jquery');
        ?>
        <style type="text/css">
            #nav { display: none; }
            #loginform p:first-child { display: none; }
            #loginform p:nth-child(2) { display: none; }
        </style>
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

        function doArtmapsSiginInternal(providerID) {
            var provider = providers[providerID];
            if(typeof provider.label !== 'undefined') {
                var username = window.prompt(provider.label);
                if(username != null && username != "") {
                    var url = provider.url.replace("{username}", username);
                    jQuery("#openid_identifier").val(url);
                    jQuery("#loginform").submit();
                }
            } else {
                jQuery("#openid_identifier").val(provider.url);
                jQuery("#loginform").submit();
            }
        }

        var doArtmapsSignin = function (providerID) {
            jQuery(document).ready(function() {
                doArtmapsSiginInternal(providerID);
            });
        };

        jQuery(function($) {
            doArtmapsSignin = doArtmapsSiginInternal;
        });
        </script>
        <?php
    }

    public function displayForm() {
        ?>
        <p>
            <label for="artmaps_display_name">This is a new account:
                <input name="artmaps_new_account" id="artmaps_new_account" type="checkbox" class="input" size="10" />
            </label>
        </p>
        <script type="text/javascript">
        jQuery(function($) {
            $("#artmaps_new_account").click(function() {
                $("#artmaps-new-account-section").toggle();
            });
        });
        </script>
        <p id="artmaps-new-account-section" style="display:none;">
            <label for="artmaps_display_name">Display name:<br />
                <input name="artmaps_display_name" id="artmaps_display_name" type="text" class="input" size="10" />
            </label>

            <label for="artmaps_blog_url">Blog URL:<br />
                <input name="artmaps_blog_url" id="artmaps_blog_url" type="text" class="input" size="20" />
            </label>
        </p>
        <hr style="height: 1px;" />
        <?php
        $providers = array(
                'google' => 'Google',
                'yahoo' => 'Yahoo',
                'blogger' => 'Blogger',
                'aol' => 'AOL',
                'wordpress' => 'WordPress',
                'livejournal' => 'LiveJournal',
                'myopenid' => 'My OpenID',
                'claimid' => 'ClaimID',
                'verisign' => 'Verisign',
                'openid' => 'OpenID'
                );
        foreach ($providers as $p => $n) {
        ?>
        <a href="javascript:doArtmapsSignin('<?= $p ?>');">
            <img src="<?= plugins_url("artmaps/content/$p.png") ?>" alt="Sign in using <?= $n ?>"  />
        </a>
        <?php
        }
        ?>
        <input type="hidden" name="openid_identifier" id="openid_identifier" />
        <hr style="height: 1px;" />
        <?php
    }
}}
?>
