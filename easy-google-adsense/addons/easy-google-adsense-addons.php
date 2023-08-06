<?php

function easy_google_adsense_display_addons()
{
    /*
    echo '<div class="wrap">';
    echo '<h2>' .__('Easy Google AdSense Add-ons', 'easy-google-adsense') . '</h2>';
    */
    $addons_data = array();

    $addon_1 = array(
        'name' => 'No Ads for WP Users',
        'thumbnail' => EASY_GOOGLE_ADSENSE_URL.'/addons/images/ega-no-ads-wp-users.png',
        'description' => 'Disable auto ads for logged-in WordPress users',
        'page_url' => 'https://noorsplugin.com/easy-google-adsense-plugin-wordpress/',
    );
    array_push($addons_data, $addon_1);
    
    $addon_2 = array(
        'name' => 'Bottom Anchor Ads',
        'thumbnail' => EASY_GOOGLE_ADSENSE_URL.'/addons/images/ega-bottom-anchor-ads.png',
        'description' => 'Show anchor ads at the bottom of the screen',
        'page_url' => 'https://noorsplugin.com/easy-google-adsense-plugin-wordpress/',
    );
    array_push($addons_data, $addon_2);
    
    //Display the list
    foreach ($addons_data as $addon) {
        ?>
        <div class="easy_google_adsense_addons_item_canvas">
        <div class="easy_google_adsense_addons_item_thumb">
            <img src="<?php echo esc_url($addon['thumbnail']);?>" alt="<?php echo esc_attr($addon['name']);?>">
        </div>
        <div class="easy_google_adsense_addons_item_body">
        <div class="easy_google_adsense_addons_item_name">
            <a href="<?php echo esc_url($addon['page_url']);?>" target="_blank"><?php echo esc_html($addon['name']);?></a>
        </div>
        <div class="easy_google_adsense_addons_item_description">
        <?php echo esc_html($addon['description']);?>
        </div>
        <div class="easy_google_adsense_addons_item_details_link">
        <a href="<?php echo esc_url($addon['page_url']);?>" class="easy_google_adsense_addons_view_details" target="_blank">View Details</a>
        </div>    
        </div>
        </div>
        <?php
    }
    echo '</div>';//end of wrap
}
