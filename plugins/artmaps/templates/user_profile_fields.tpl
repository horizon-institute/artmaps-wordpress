<a name="artmaps"></a>
<input type="hidden" name="artmaps_redirect" value="{$redirect}" />
<h3>My External Blog For Publishing</h3>
<table class="form-table">
    <tr>
        <td>
            <span class="description">
                ArtMaps can use your personal WordPress blog for publishing
                if you would like to share your ArtMaps activities with
                your subscribers.  If you wish to activate this functionality
                please enter your WordPress blog details below.
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <table class="form-table">
                <tr>
                    <th><label for="artmaps_use_personal_blog_url">Blog URL</label></th>
                    <td>
                        <input 
                                type="text" 
                                id="artmaps_use_personal_blog_url" 
                                name="artmaps_use_personal_blog_url" 
                                value="{$blog->url}" />
                        <span class="description">Your blog URL</span>
                    </td>
                </tr>
                <tr>
                    <th><label for="artmaps_use_personal_blog_username">Blog Username</label></th>
                    <td>
                        <input 
                                type="text"
                                id="artmaps_use_personal_blog_username"
                                name="artmaps_use_personal_blog_username"
                                value="{$blog->username}" />
                        <span class="description">Your blog username</span>
                    </td>
                </tr>
                <tr>
                    <th><label for="artmaps_use_personal_blog_password">Blog Password</label></th>
                    <td>
                        <input 
                                type="password"
                                id="artmaps_use_personal_blog_password"
                                name="artmaps_use_personal_blog_password"
                                value="" />
                        <span class="description">Your blog password</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>