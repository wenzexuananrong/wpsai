<?php
function wpcli_fix_pic($args, $assoc_args){
	global $wpdb;
		    	
    $fix_file = '/home/';
    $content = file_get_contents($fix_file);
	$items = json_decode($content, true);

	if(empty($items)){
		WP_CLI::success( 'total 0 pic fixed, fix file empty.' );
	}

    $i = 0;
	foreach($items as $item){
	    if(empty($item)) continue;

	    foreach($item as $image){
	        if(empty($image)) continue;

            foreach($image as $post_id=>$new_file){
            	$post_meta = get_post_meta($post_id, '_wp_attachment_metadata', true);

            	if(isset($post_meta['file'])){
	                $post_meta['file']=$new_file;
	            }
	            if(isset($post_meta['sizes']) && is_array($post_meta['sizes'])){
	                foreach ($post_meta['sizes'] as $key =>$value){
	                    //$post_meta['sizes'][$key]['file']=urlencode($post_meta['sizes'][$key]['file']);
	                }
	            }


                update_post_meta($post_id, '_wp_attached_file', $new_file);
                wp_update_attachment_metadata($post_id, $post_meta);


		        $url = addslashes($new_file);
		        $wpdb->query("update wp_posts set `guid` ='{$url}' where ID='{$post_id}'");

		        $i++;
            }
	    }
	}
    
    WP_CLI::success( 'total '. $i . ' pic fixed successfully.' );
}