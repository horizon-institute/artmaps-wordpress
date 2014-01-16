<?php
/*
Copyright (C) 2009 NetWebLogic LLC

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Class initialization
class LoginWithAjaxAdmin{
	// action function for above hook
	function LoginWithAjaxAdmin() {
		global $user_level;
		add_action ( 'admin_menu', array (&$this, 'menus') );
		if( !empty($_GET['lwa_dismiss_notice']) && $_GET['lwa_dismiss_notice'] == '1' ){
			update_option('lwa_notice', 1);
		}elseif( get_option('lwa_notice') != 1 && $user_level == 10 ){
			add_action('admin_notices', array(&$this, 'admin_notices') );			
		}
	}
	
	function menus(){
		$page = add_options_page('Login With Ajax', 'Login With Ajax', 'manage_options', 'login-with-ajax', array(&$this,'options'));
		add_action('admin_head-'.$page, array(&$this,'options_head'));
	}

	function admin_notices() {
		$dismiss = $_SERVER['REQUEST_URI'];
		$dismiss .= (strpos($dismiss, '?')) ? "&amp;":"?";
		$dismiss .= "lwa_dismiss_notice=1";
		?>
		<div id='lwa-warning' class='updated fade'>
			<p>
				<?php _e('New features in Login With AJAX (including registration!), check out the settings and widget pages!', 'login-with-ajax') ?> - 
				<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=login-with-ajax">Settings</a> | 
				<a href="<?php echo $dismiss ?>"><?php _e('Dismiss','login-with-ajax') ?></a> 
			</p>
		</div>
		<?php
	}
	
	
	function options_head(){
		?>
		<style type="text/css">
			.nwl-plugin table { width:100%; }
			.nwl-plugin table .col { width:100px; }
			.nwl-plugin table input.wide { width:100%; padding:2px; }
		</style>
		<?php
	}
	
	function options() {
		global $LoginWithAjax;
		add_option('lwa_data');
		$lwa_data = array();	
		
		if( !empty($_POST['lwasubmitted']) && current_user_can('list_users') && wp_verify_nonce($_POST['_nonce'], 'login-with-ajax-admin'.get_current_user_id()) ){
			//Build the array of options here
			foreach ($_POST as $postKey => $postValue){
				if( $postValue != '' && preg_match('/lwa_role_log(in|out)_/', $postKey) ){
					//Custom role-based redirects
					if( preg_match('/lwa_role_login/', $postKey) ){
						//Login
						$lwa_data['role_login'][str_replace('lwa_role_login_', '', $postKey)] = $postValue;
					}else{
						//Logout
						$lwa_data['role_logout'][str_replace('lwa_role_logout_', '', $postKey)] = $postValue;
					}
				}elseif( substr($postKey, 0, 4) == 'lwa_' ){
					//For now, no validation, since this is in admin area.
					if($postValue != ''){
						$lwa_data[substr($postKey, 4)] = $postValue;
					}
				}
			}
			update_option('lwa_data', $lwa_data);
			if( !empty($_POST['lwa_notification_override']) ){
				update_option('lwa_notification_override',$_POST['lwa_notification_override']);
			}
			?>
			<div class="updated"><p><strong><?php _e('Changes saved.'); ?></strong></p></div>
			<?php
		}else{
			$lwa_data = get_option('lwa_data');	
		}
		?>
		<div class="wrap nwl-plugin">
			<h2>Login With Ajax</h2>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle" style="color:green;">** Support this plugin! **</h3>
						<div class="inside">
							<p>This plugin was developed by <a href="http://msyk.es/" target="_blank">Marcus Sykes</a>, sponsored by proceeds from <a href="http://netweblogic.com" target="_blank">NetWebLogic</a></p>
							<p>We're not asking for donations, but we'd appreciate a 5* rating and/or a link to our plugin page!</p>
							<ul>
								<li><a href="http://wordpress.org/support/view/plugin-reviews/login-with-ajax" target="_blank" >Give us 5 Stars on WordPress.org</a></li>
								<li><a href="http://wordpress.org/extend/plugins/login-with-ajax/" target="_blank" >Link to our plugin page.</a></li>
							</ul>
						</div>
					</div>
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Getting Help</h3>
						<div class="inside">
							<p>Before asking for help, check the readme files or the plugin pages for answers to common issues.</p>
							<p>If you're still stuck, try the <a href="http://wordpress.org/support/plugin/login-with-ajax/">community forums</a>.</p>
							<p>If you have any suggestions, come over to the forums and leave a comment. It may just happen!</p>
						</div>
					</div>
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Translating</h3>
						<div class="inside">
							<p>Translations are done by volunteers, see our new <a href="http://translate.netweblogic.com/projects/login-with-ajax">translation site</a> to join in or to add a new langauge!</p>
						</div>
					</div>
				</div>
				<div id="post-body">
					<div id="post-body-content">
						<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
						<h2><?php _e("General Settings", 'login-with-ajax'); ?></h2>
						<table class="form-table">
							<?php if( count(LoginWithAjax::$templates) > 1 ) : ?>
							<tr valign="top">
								<td scope="row">
									<label><?php _e("Default Template", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<select name="lwa_template"  style="margin:0px; padding:0px; width:auto;">
					            		<?php foreach( array_keys(LoginWithAjax::$templates) as $template ): ?>
					            		<option <?php echo (!empty($lwa_data['template']) && $lwa_data['template'] == $template) ? 'selected="selected"':""; ?>><?php echo $template ?></option>
					            		<?php endforeach; ?>
					            	</select>
									<br />
									<em><?php _e("Choose the default theme you'd like to use. This can be overriden in the widget, shortcode and template tags.", 'login-with-ajax'); ?></em>
									<em><?php _e("Further documentation for this feature coming soon...", 'login-with-ajax'); ?></em>
								</td>
							</tr>
							<?php endif; ?>
							<tr valign="top">
								<td scope="row">
									<label><?php _e("Disable refresh upon login?", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<input style="margin:0px; padding:0px; width:auto;" type="checkbox" name="lwa_no_login_refresh" value='1' class='wide' <?php echo ( !empty($lwa_data['no_login_refresh']) && $lwa_data['no_login_refresh'] == '1' ) ? 'checked="checked"':''; ?> />
									<br />
									<em><?php _e("If the user logs in and you check the button above, only the login widget will update itself without refreshing the page. Not a good idea if your site shows different content to users once logged in, as a refresh would be needed.", 'login-with-ajax'); ?></em>
								</td>
							</tr>
						</table>
						
						
						<h2><?php _e("Redirection Settings", 'login-with-ajax'); ?></h2>
						<table class="form-table">
							<tr valign="top">
								<td scope="row">
									<label><?php _e("Global Login Redirect", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<input type="text" name="lwa_login_redirect" value='<?php echo (!empty($lwa_data['login_redirect'])) ? $lwa_data['login_redirect']:''; ?>' class='wide' />
									<em><?php _e("If you'd like to send the user to a specific URL after login, enter it here (e.g. http://wordpress.org/)", 'login-with-ajax'); ?></em>
									<br/><em><?php _e("Use %USERNAME% and it will be replaced with the username of person logging in.", 'login-with-ajax'); ?></em>
									<?php
									//WMPL itegrations
									function lwa_icl_inputs( $name, $lwa_data ){
										if( function_exists('icl_get_languages') ){
											$langs = icl_get_languages();
											if( count($langs) > 1 ){
												?>
												<table id="lwa_<?php echo $name; ?>_langs">
												<?php
												foreach($langs as $lang){
													if( substr(get_locale(),0,2) != $lang['language_code'] ){
													?>
													<tr>
														<td style="width:100px;"><?php echo $lang['translated_name']?>: </td>
														<td><input type="text" name="lwa_<?php echo $name; ?>_<?php echo $lang['language_code']; ?>" value='<?php echo ( !empty($lwa_data[$name.'_'.$lang['language_code']]) ) ? $lwa_data[$name.'_'.$lang['language_code']]:''; ?>' class="wide" /></td>
													</tr>
													<?php
													} 
												}
												?>
												</table>
												<em><?php _e('With WPML enabled you can provide different redirection destinations based on language too.','login-with-ajax'); ?></em>
												<?php
											}
										}
									}
									lwa_icl_inputs('login_redirect', $lwa_data);
									?> 
								</td>
							</tr>
							<tr valign="top">
								<td scope="row">
									<label><?php _e("Global Logout Redirect", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<input type="text" name="lwa_logout_redirect" value='<?php echo (!empty($lwa_data['logout_redirect'])) ? $lwa_data['logout_redirect']:''; ?>' class='wide' />
									<em><?php _e("If you'd like to send the user to a specific URL after logout, enter it here (e.g. http://wordpress.org/)", 'login-with-ajax'); ?></em>
									<br /><em><?php _e("Enter %LASTURL% to send the user back to the page they were previously on.", 'login-with-ajax'); ?></em>
									<?php 
									lwa_icl_inputs('logout_redirect', $lwa_data);
									?> 
								</td>
							</tr>
							<tr valign="top">
								<td scope="row">
									<label><?php _e("Role-Based Custom Login Redirects", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<em><?php _e("If you would like a specific user role to be redirected to a custom URL upon login, place it here (blank value will default to the global redirect)", 'login-with-ajax'); ?></em>
									<table>
									<?php 
									//Taken from /wp-admin/includes/template.php Line 2715  
									$editable_roles = get_editable_roles();	
									//WMPL integration	
									function lwa_icl_inputs_roles( $name, $lwa_data, $role ){
										if( function_exists('icl_get_languages') ){
											$langs = icl_get_languages();
											if( count($langs) > 1 ){
												?>
												<table id="lwa_<?php echo $name; ?>_langs">
												<?php
												foreach($langs as $lang){
													if( substr(get_locale(),0,2) != $lang['language_code'] ){
													?>
													<tr>
														<td style="width:100px;"><?php echo $lang['translated_name']?>: </td>
														<td><input type="text" name="lwa_<?php echo $name; ?>_<?php echo $role ?>_<?php echo $lang['language_code']; ?>" value='<?php echo ( !empty($lwa_data[$name][$role.'_'.$lang['language_code']]) ) ? $lwa_data[$name][$role.'_'.$lang['language_code']]:''; ?>' class="wide" /></td>
													</tr>
													<?php
													} 
												}
												?>
												</table>
												<em><?php _e('With WPML enabled you can provide different redirection destinations based on language too.','login-with-ajax'); ?></em>
												<?php
											}
										}
									}	
									foreach( $editable_roles as $role => $details ) {
										$role_login = ( !empty($lwa_data['role_login']) && is_array($lwa_data['role_login']) && array_key_exists($role, $lwa_data['role_login']) ) ? $lwa_data['role_login'][$role]:''
										?>
										<tr>
											<td class="col"><?php echo translate_user_role($details['name']) ?></td>
											<td>
												<input type='text' class='wide' name='lwa_role_login_<?php echo esc_attr($role) ?>' value="<?php echo $role_login ?>" />
												<?php 												
													lwa_icl_inputs_roles('role_login', $lwa_data, esc_attr($role)); 
												?>
											</td>
										</tr>
										<?php
									}
									?>
									</table>
								</td>
							</tr>
							<tr valign="top">
								<td scope="row">
									<label><?php _e("Role-Based Custom Logout Redirects", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<em><?php _e("If you would like a specific user role to be redirected to a custom URL upon logout, place it here (blank value will default to the global redirect)", 'login-with-ajax'); ?></em>
									<table>
									<?php 
									//Taken from /wp-admin/includes/template.php Line 2715  
									$editable_roles = get_editable_roles();		
									foreach( $editable_roles as $role => $details ) {
										$role_logout = ( !empty($lwa_data['role_logout']) && is_array($lwa_data['role_logout']) && array_key_exists($role, $lwa_data['role_logout']) ) ? $lwa_data['role_logout'][$role]:''
										?>
										<tr>
											<td class='col'><?php echo translate_user_role($details['name']) ?></td>
											<td>
												<input type='text' class='wide' name='lwa_role_logout_<?php echo esc_attr($role) ?>' value="<?php echo $role_logout ?>" />
												<?php lwa_icl_inputs_roles('role_logout', $lwa_data, $role); ?>
											</td>
										</tr>
										<?php
									}
									?>
									</table>
								</td>
							</tr>
						</table>
						
						<h2><?php _e("Notification Settings", 'login-with-ajax'); ?></h2>
						<p>
							<em><?php _e("If you'd like to override the default Wordpress email users receive once registered, make sure you check the box below and enter a new email subject and message.", 'login-with-ajax'); ?></em><br />
							<em><?php _e("If this feature doesn't work, please make sure that you don't have another plugin installed which also manages user registrations (e.g. BuddyPress and MU).", 'login-with-ajax'); ?></em>										
						</p>
						<table class="form-table">
							<tr valign="top">
								<td>
									<label><?php _e("Override Default Email?", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<input style="margin:0px; padding:0px; width:auto;" type="checkbox" name="lwa_notification_override" value='1' class='wide' <?php echo ( !empty($lwa_data['notification_override']) && $lwa_data['notification_override'] == '1' ) ? 'checked="checked"':''; ?> />
								</td>
							</tr>
							<tr valign="top">
								<td>
									<label><?php _e("Subject", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<?php 
									if(empty($lwa_data['notification_subject'])){
										$lwa_data['notification_subject'] = __('Your registration at %BLOGNAME%', 'login-with-ajax');
									}
									?>
									<input type="text" name="lwa_notification_subject" value='<?php echo (!empty($lwa_data['notification_subject'])) ? $lwa_data['notification_subject'] : ''; ?>' class='wide' />
									<em><?php _e("<code>%USERNAME%</code> will be replaced with a username.", 'login-with-ajax'); ?></em><br />
									<em><?php _e("<code>%PASSWORD%</code> will be replaced with the user's password.", 'login-with-ajax'); ?></em><br />
									<em><?php _e("<code>%BLOGNAME%</code> will be replaced with the name of your blog.", 'login-with-ajax'); ?></em>
									<em><?php _e("<code>%BLOGURL%</code> will be replaced with the url of your blog.", 'login-with-ajax'); ?></em>
								</td>
							</tr>
							<tr valign="top">
								<td>
									<label><?php _e("Message", 'login-with-ajax'); ?></label>
								</td>
								<td>
									<?php 
										if( empty($lwa_data['notification_message']) ){
											$lwa_data['notification_message'] = __('Thanks for signing up to our blog. 

You can login with the following credentials by visiting %BLOGURL%

Username : %USERNAME%
Password : %PASSWORD%

We look forward to your next visit!

The team at %BLOGNAME%', 'login-with-ajax');
										}
										?>
									<textarea name="lwa_notification_message" class='wide' style="width:100%; height:250px;"><?php echo $lwa_data['notification_message'] ?></textarea>
									<em><?php _e("<code>%BLOGNAME%</code> will be replaced with the name of your blog.", 'login-with-ajax'); ?></em>
									<em><?php _e("<code>%BLOGURL%</code> will be replaced with the url of your blog.", 'login-with-ajax'); ?></em>
								</td>
							</tr>
						</table>
							<div>
								<input type="hidden" name="lwasubmitted" value="1" />
								<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('login-with-ajax-admin'.get_current_user_id()); ?>" />
								<p class="submit">
									<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
								</p>
							</div>		
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

function LoginWithAjaxAdminInit(){
	global $LoginWithAjaxAdmin; 
	$LoginWithAjaxAdmin = new LoginWithAjaxAdmin();
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'LoginWithAjaxAdminInit' );
?>