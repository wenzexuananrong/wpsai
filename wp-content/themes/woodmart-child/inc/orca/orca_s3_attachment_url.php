<?php
function orca_s3_attachment_url($url, $post_id) {
	$upload_dir = wp_get_upload_dir();
    $file_path = get_post_meta($post_id, '_wp_attached_file', true);
    if(stripos($file_path,'http')===false){
        $url = $upload_dir['baseurl'] . "/$file_path";
    } else {
        $url = $file_path;
    }
    return $url;
}
add_filter('wp_get_attachment_url', 'orca_s3_attachment_url', 10, 2);


function orca_calculate_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    $upload_dir = wp_get_upload_dir();
    $s3url = trailingslashit( $upload_dir['baseurl'] ).'http';

    foreach ($sources as $key=>$source) {
        if (0 === stripos($source['url'], $s3url)) {
            $sources[$key]['url'] = substr($source['url'], strlen($upload_dir['baseurl'])+1);
        }
    }
    return $sources;
}
add_filter('wp_calculate_image_srcset', 'orca_calculate_image_srcset', 10, 5);