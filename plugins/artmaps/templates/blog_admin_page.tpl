<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>ArtMaps Settings</h2>
	{if $updated}
	<div id="setting-error-settings_updated" class="updated settings-error"> 
		<p><strong>Settings saved.</strong></p>
	</div>
	{/if}	
    <form method="post" action="">   
        <table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="artmaps_blog_config_search_source">Search Source</label></th>
					<td>
						<select name="artmaps_blog_config_search_source">
				            <option value="artmaps"{if $searchSource == 'artmaps'} selected="selected"{/if}>ArtMaps</option>
				            <option value="tateartwork"{if $searchSource == 'tateartwork'} selected="selected"{/if}>Tate Collection</option>
				        </select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="artmaps_blog_option_object_page_title_template">Object Page Title Template</label></th>
					<td>
						<textarea 
				                name="artmaps_blog_option_object_page_title_template" 
				                style="width: 80%; height: 100px;">{$objectPageTitleTemplate}</textarea>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="artmaps_blog_option_comment_template">Semi-Structured Comment Template</label></th>
					<td>
						<textarea 
				                name="artmaps_blog_option_comment_template" 
				                style="width: 80%; height: 100px;">{$commentTemplate}</textarea>
					</td>
				</tr>
				
			</tbody>
		</table>
		<p class="submit">
        <input
                class="button-primary"
                type="submit"
                name="submit"
                value="Save Changes" />
        </p>
    </form>
</div>