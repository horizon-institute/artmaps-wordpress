<div id="artmaps-nav-bar">
	<div id="artmaps-nav-bar-home" class="artmaps-nav-bar-link">
	    <a href="<?= get_site_url() ?>/?p=1">Home</a>
	</div>
	<div id="artmaps-nav-bar-map" class="artmaps-nav-bar-link">
	    <a href="<?= get_site_url() ?>/map">The Art Map</a>
	</div>
	<div id="artmaps-nav-bar-<?= is_user_logged_in() ? 'logout' : 'login' ?>" class="artmaps-nav-bar-link">
	    <?php wp_loginout() ?>
	</div>
</div>

<div id="artmaps-login-tabs">

    <ul>
        <li><a href="#artmaps-password">Login with password</a></li>
        <li><a href="#artmaps-openid">Login using an OpenID</a></li>
        <li><a href="#artmaps-register">Register</a></li>
    </ul>

    <div id="artmaps-password"></div>

    <div id="artmaps-openid">
        <input type="hidden" name="openid_identifier" id="openid_identifier" />
        <?php
            $providers = array(
                    'google' => 'Google', 'yahoo' => 'Yahoo', 'blogger' => 'Blogger',
                    'aol' => 'AOL', 'wordpress' => 'WordPress', 'livejournal' => 'LiveJournal',
                    'myopenid' => 'My OpenID', 'claimid' => 'ClaimID', 'verisign' => 'Verisign',
                    'openid' => 'OpenID' );
            foreach ($providers as $p => $n) {
        ?>
        <img id="artmaps-openid-provider-<?= $p ?>" class="artmaps-openid-provider"
                src="<?= get_stylesheet_directory_uri() ?>/content/openid/<?= $p ?>.png"
                alt="Sign in using <?= $n ?>"  />
        <?php } ?>
    </div>

    <div id="artmaps-register">
        <p>
    		<label for="artmaps-register-name">Display name<br>
    		<input type="text" name="artmaps-register-name" id="artmaps-register-name"
    		        class="input" value="" size="20" tabindex="10"></label>
    	</p>
        <p>
    		<label for="artmaps-register-login">Username<br>
    		<input type="text" name="artmaps-register-login" id="artmaps-register-login"
    		        class="input" value="" size="20" tabindex="10"></label>
    	</p>
    	<p>
    		<label for="artmaps-register-password">Password<br>
    		<input type="password" name="artmaps-register-password" id="artmaps-register-password"
    		        class="input" value="" size="20" tabindex="20"></label>
    	</p>
    	<p>
    		<label for="artmaps-register-password-confirm">Repeat Password<br>
    		<input type="password" name="artmaps-register-password-confirm" id="artmaps-register-password-confirm"
    		        class="input" value="" size="20" tabindex="20"></label>
    	</p>
    	<p>
    		<label for="artmaps-register-email">E-Mail<br>
    		<input type="text" name="artmaps-register-email" id="artmaps-register-email"
    		        class="input" value="" size="20" tabindex="20"></label>
    	</p>
    	<p>
    		<label for="artmaps-register-blog">Blog URL<br>
    		<input type="text" name="artmaps-register-blog" id="artmaps-register-blog"
    		        class="input" value="" size="20" tabindex="20"></label>
    	</p>
    	<p class="submit">
    		<input type="button" name="artmaps-register-submit" id="artmaps-register-submit"
    		        class="button-primary" value="Register" tabindex="100">
    	</p>
    </div>
</div>