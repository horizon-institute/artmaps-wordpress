<div id="artmaps-post-body">
    <div id="artmaps-data-section">
        {if isset($metadata->imageurl)}
        <img class="artmaps-data" src="{$metadata->imageurl}" alt="{$metadata->title}" style="max-width: 200px; max-height: 200px;" />
        <br />
        {/if}
        <span class="artmaps-data">Artist: {$metadata->artist} {$metadata->artistdate}</span>
        <br />
        <span class="artmaps-data">Title: {$metadata->title}</span>
        <br />
        <span class="artmaps-data">Date: {$metadata->artworkdate}</span>
        <br />
        <span class="artmaps-data">Reference: {$metadata->reference}</span>
        <br />
        <a class="artmaps-data" href="{$link}">View the artwork on ArtMaps</a>
    </div>
    <div id="artmaps-comment-text">Enter your comment here</div>
</div>