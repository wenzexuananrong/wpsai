<?php
use WP_CLI\Utils;

function wpcli_add_media($args, $assoc_args){
    $url = $args[0];
    $width = $assoc_args['width'];
    $height = $assoc_args['height'];
    $size = $assoc_args['size'];

    if(empty($url)){
        WP_CLI::error( 'empty url' );
    }

    $file = pathinfo($url);
    $file_path = str_replace('\\', '-', parse_url($url)['path']);
    $file_path = str_replace('/', '-', $file_path);
    $file_path = substr($file_path,1);
    $file_path = str_replace((".".$file['extension']), '', $file_path);

    $post_array = [
        'post_name' => $file_path,
        'post_title' => $file['filename'],
        'post_mime_type' => str_replace('jpg', 'jpeg', "image/{$file['extension']}"),
        'guid' => $url,
    ];

    $post_id = wpcli_insert_attachment( $post_array, $url);

    $image_info = wpcli_get_image_info($url,$width,$height,$size);

    wpcli_insert_postmeta($post_id, '_wp_attached_file', $url);
    wpcli_insert_postmeta($post_id, '_wp_attachment_metadata', serialize($image_info));

    WP_CLI::log(sprintf("Imported file '%s' as attachment ID %d.", $url, $post_id));
    Utils\report_batch_operation_results( 'item', 'import', 1, 1, 0 );
}

function wpcli_insert_postmeta($post_id, $key, $value){
    global $wpdb;

    $datas = array(
        'post_id' => $post_id,
        'meta_key' => $key, 
        'meta_value' => $value,
    );
    $wpdb->insert( $wpdb->postmeta, $datas );
}

function wpcli_insert_attachment( $args, $file = false, $parent_post_id = 0) {
    global $wpdb;

    $defaults = array(
        'post_parent' => 0,
    );

    $data = wp_parse_args( $args, $defaults );

    if ( ! empty( $parent_post_id ) ) {
        $data['post_parent'] = $parent_post_id;
    }

    $data['post_type'] = 'attachment';

    if ( ! $wpdb->insert( $wpdb->posts, $data ) ) {
        return false;
    }

    $id = (int) $wpdb->insert_id;

    return $id;
}

/**
 * 取得缩略图信息
 * @param $url 文件地址
 * @param $width 文件宽度
 * @param $height 文件高度
 * @param $size 文件大小
 * @return array
 */
function wpcli_get_image_info($url, $width=0, $height=0, $size=0)
{

    $wp_get_registered_image_subsizes = [
        'medium' => [
            'width' => '300',
            'height' => '300',
            'crop' => false
        ],
        'thumbnail' => [
            'width' => '150',
            'height' => '150',
            'crop' => true
        ],
        'medium_large' => [
            'width' => '768',
            'height' => '0',
            'crop' => false
        ],
        'large' => [
            'width' => '1024',
            'height' => '1024',
            'crop' => false
        ],
        '1536x1536' => [
            'width' => '1536',
            'height' => '1536',
            'crop' => false
        ],
        '2048x2048' => [
            'width' => '2048',
            'height' => '2048',
            'crop' => false
        ],
        'woodmart_shop_catalog_x2' => [
            'width' => '1200',
            'height' => '1200',
            'crop' => true
        ],
        'woocommerce_thumbnail' => [
            'width' => '600',
            'height' => '600',
            'crop' => true
        ],
        'woocommerce_single' => [
            'width' => '1200',
            'height' => '0',
            'crop' => false
        ],
        'woocommerce_gallery_thumbnail' => [
            'width' => '150',
            'height' => '0',
            'crop' => false
        ]
    ];
    
    $image_meta = [
        'width' => (int)$width,
        'height' => (int)$height,
        'file' => $url,
        'filesize' => (int)$size,
    ];

    $file = pathinfo($url);
    foreach ($wp_get_registered_image_subsizes as $key => $new_size_data) {
        $result = wpcli_get_make_subsize($width,$height,$new_size_data['width'],$new_size_data['height'],$new_size_data['crop']);
        if($result){
            $data=[
                'file'=>"{$file['filename']}-{$result[0]}x{$result[1]}.{$file['extension']}",
                'width'=>$result[0],
                'height'=>$result[1],
                'mime-type'=>str_replace('jpg','jpeg',"image/{$file['extension']}"),
                'filesize'=>(int)$size
            ];
            $image_meta['sizes'][$key] = $data;
        }
    };
    $image_meta['image_meta'] = [
        'aperture'=>0,
        'credit'=>"",
        'camera'=>"",
        'caption'=>"",
        'created_timestamp'=>"0",
        'copyright'=>"",
        'focal_length'=>"0",
        'iso'=>"0",
        'shutter_speed'=>"0",
        'title'=>"",
        'orientation'=>"0",
        'keywords'=>[]
    ];
    return $image_meta;
}

function wpcli_get_make_subsize($orig_w, $orig_h, $dest_w, $dest_h, $crop = false)
{
    if ($orig_w <= 0 || $orig_h <= 0) {
        return false;
    }
    // At least one of $dest_w or $dest_h must be specific.
    if ($dest_w <= 0 && $dest_h <= 0) {
        return false;
    }

    // Stop if the destination size is larger than the original image dimensions.
    if (empty($dest_h)) {
        if ($orig_w < $dest_w) {
            return false;
        }
    } elseif (empty($dest_w)) {
        if ($orig_h < $dest_h) {
            return false;
        }
    } else {
        if ($orig_w < $dest_w && $orig_h < $dest_h) {
            return false;
        }
    }

    if ($crop) {
        $aspect_ratio = $orig_w / $orig_h;
        $new_w = min($dest_w, $orig_w);
        $new_h = min($dest_h, $orig_h);

        if (!$new_w) {
            $new_w = (int)round($new_h * $aspect_ratio);
        }

        if (!$new_h) {
            $new_h = (int)round($new_w / $aspect_ratio);
        }

        $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

        $crop_w = round($new_w / $size_ratio);
        $crop_h = round($new_h / $size_ratio);

        if (!is_array($crop) || count($crop) !== 2) {
            $crop = array('center', 'center');
        }

        list($x, $y) = $crop;

        if ('left' === $x) {
            $s_x = 0;
        } elseif ('right' === $x) {
            $s_x = $orig_w - $crop_w;
        } else {
            $s_x = floor(($orig_w - $crop_w) / 2);
        }

        if ('top' === $y) {
            $s_y = 0;
        } elseif ('bottom' === $y) {
            $s_y = $orig_h - $crop_h;
        } else {
            $s_y = floor(($orig_h - $crop_h) / 2);
        }
    } else {
        // Resize using $dest_w x $dest_h as a maximum bounding box.
        $crop_w = $orig_w;
        $crop_h = $orig_h;

        $s_x = 0;
        $s_y = 0;

        list($new_w, $new_h) = wpcli_constrain_dimensions($orig_w, $orig_h, $dest_w, $dest_h);
    }
    return array((int)$new_w, (int)$new_h);
}

function wpcli_constrain_dimensions( $current_width, $current_height, $max_width = 0, $max_height = 0 ) {
    if ( ! $max_width && ! $max_height ) {
        return array( $current_width, $current_height );
    }

    $width_ratio  = 1.0;
    $height_ratio = 1.0;
    $did_width    = false;
    $did_height   = false;

    if ( $max_width > 0 && $current_width > 0 && $current_width > $max_width ) {
        $width_ratio = $max_width / $current_width;
        $did_width   = true;
    }

    if ( $max_height > 0 && $current_height > 0 && $current_height > $max_height ) {
        $height_ratio = $max_height / $current_height;
        $did_height   = true;
    }

    // Calculate the larger/smaller ratios.
    $smaller_ratio = min( $width_ratio, $height_ratio );
    $larger_ratio  = max( $width_ratio, $height_ratio );

    if ( (int) round( $current_width * $larger_ratio ) > $max_width || (int) round( $current_height * $larger_ratio ) > $max_height ) {
        // The larger ratio is too big. It would result in an overflow.
        $ratio = $smaller_ratio;
    } else {
        // The larger ratio fits, and is likely to be a more "snug" fit.
        $ratio = $larger_ratio;
    }

    // Very small dimensions may result in 0, 1 should be the minimum.
    $w = max( 1, (int) round( $current_width * $ratio ) );
    $h = max( 1, (int) round( $current_height * $ratio ) );

    // Note: $did_width means it is possible $smaller_ratio == $width_ratio.
    if ( $did_width && $w === $max_width - 1 ) {
        $w = $max_width; // Round it up.
    }

    // Note: $did_height means it is possible $smaller_ratio == $height_ratio.
    if ( $did_height && $h === $max_height - 1 ) {
        $h = $max_height; // Round it up.
    }

    return array( $w, $h );
}