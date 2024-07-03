<?php
function wpcli_import_comment($args, $assoc_args){
    global $wpdb;

    $file_path = $args[0];

    if (!file_exists($file_path)) {
        WP_CLI::error("文件 {$file_path} 不存在.");
    }

    $content = file_get_contents($file_path);
    $items = json_decode($content, true);

    if(empty($items)){
        WP_CLI::success( 'total 0 comment imported, empty file.' );
    }

    $i = 0;
    foreach($items as $post_id=>$item){
        if(empty($item)) continue;
        if(empty($post_id)) continue;

        $sql = "SELECT COUNT(*) FROM wp_comments WHERE comment_post_ID=%d";
        $total = $wpdb->get_var( $wpdb->prepare($sql, $post_id) );

        $begain_total = $total;

        $sum_score = 0;
        $groups = [];
        foreach($item as $comment){
            $author = $comment['author']; 
            $content = $comment['content'];
            $score = !empty($comment['score'])?$comment['score']:5;
            $like_num = !empty($comment['like_num'])?$comment['like_num']:0;
            $datetime = !empty($comment['datetime'])?$comment['datetime']:date('Y-m-d H:i:s');

            $res = wpcli_import_comment_save($post_id, $author, $content, $score, $like_num, $datetime);
            if($res){
                $i++;
                $total++;
                $sum_score += $score;
                $score_key = (int) $score;
                if(isset($groups[$score_key])){
                    $groups[$score_key] ++; 
                } else {
                    $groups[$score_key] = 1;
                }
                
            }
        }

        $wpdb->update( $wpdb->posts, array( 'comment_count' => $total ), array( 'ID' => $post_id ) ); 


        //重新计算平均值和分组统计值                                     
        if($begain_total>0){
            $sql = "SELECT b.meta_value AS rating, COUNT(a.comment_ID) AS num_reviews
            FROM wp_comments a
            INNER JOIN wp_commentmeta b ON a.comment_ID = b.comment_id
            WHERE b.meta_key='rating' and a.comment_post_ID=%d 
            GROUP BY b.meta_value";
            $results = $wpdb->get_results( $wpdb->prepare($sql, $post_id) );
            $sum_score = 0;
            $groups = [];
            foreach($results as $result){
                $sum_score += $result->rating*$result->num_reviews;
                $groups[$result->rating] = $result->num_reviews;
            }
        }

        $avg_score = round($sum_score/$total, 1);
        $where = array(
            'post_id' => $post_id,
            'meta_key' => '_wc_average_rating', 
        );
        $wpdb->update( $wpdb->postmeta, ['meta_value' => $avg_score],  $where); 

        $where = array(
            'post_id' => $post_id,
            'meta_key' => '_wc_review_count', 
        );
        $wpdb->update( $wpdb->postmeta, ['meta_value' => $total],  $where);  

        $sql = "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key = '_wc_rating_count' and post_id=%d";
        $rating_count = $wpdb->get_var( $wpdb->prepare($sql, $post_id) );
        if(!empty($groups)){
            $rating_group = serialize($groups);
            if($rating_count > 0 ){
                $where = array(
                    'post_id' => $post_id,
                    'meta_key' => '_wc_rating_count', 
                );
                $wpdb->update( $wpdb->postmeta, ['meta_value' => $rating_group],  $where);
            } else {
                $datas = array(
                    'post_id' => $post_id,
                    'meta_key' => '_wc_rating_count', 
                    'meta_value' => $rating_group,
                );
                $wpdb->insert( $wpdb->postmeta, $datas );
            }
        }
    }

    WP_CLI::success( 'total '. $i . ' comment imported successfully.' );
}

function wpcli_import_comment_save($post_id, $author, $content, $score, $like_num, $datetime){
    try{
        // 评论信息
        $comment_data = array(
            'comment_post_ID' => $post_id, // 替换为文章或页面的ID
            'comment_author' => $author,
            'comment_author_email' => '',
            'comment_author_url' => '',
            'comment_content' => $content,
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => 0,
            'comment_author_IP' => '127.0.0.1',
            'comment_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36',
            'comment_date' => $datetime,
            'comment_approved' => 1 // 0 代表待审核，1 代表已审核
        );

        // 向wp_comments表插入评论
        $comment_id = wpcli_insert_comment($comment_data);

        // 如果评论插入成功，添加评分元数据
        if ($comment_id) {
            // 添加评论评分元数据
            wpcli_import_commentmeta('comment', $comment_id, 'rating', $score);
            wpcli_import_commentmeta('comment', $comment_id, 'wd_likes', $like_num);
        } else {
            return false;
        }
    } catch(\Exception $e){
        return false;
    }

    return true;
}

function wpcli_insert_comment($commentdata){
    global $wpdb;

    $data = wp_unslash( $commentdata );

    $comment_author       = ! isset( $data['comment_author'] ) ? '' : $data['comment_author'];
    $comment_author_email = ! isset( $data['comment_author_email'] ) ? '' : $data['comment_author_email'];
    $comment_author_url   = ! isset( $data['comment_author_url'] ) ? '' : $data['comment_author_url'];
    $comment_author_ip    = ! isset( $data['comment_author_IP'] ) ? '' : $data['comment_author_IP'];

    $comment_date     = ! isset( $data['comment_date'] ) ? current_time( 'mysql' ) : $data['comment_date'];
    $comment_date_gmt = ! isset( $data['comment_date_gmt'] ) ? get_gmt_from_date( $comment_date ) : $data['comment_date_gmt'];

    $comment_post_id  = ! isset( $data['comment_post_ID'] ) ? 0 : $data['comment_post_ID'];
    $comment_content  = ! isset( $data['comment_content'] ) ? '' : $data['comment_content'];
    $comment_karma    = ! isset( $data['comment_karma'] ) ? 0 : $data['comment_karma'];
    $comment_approved = ! isset( $data['comment_approved'] ) ? 1 : $data['comment_approved'];
    $comment_agent    = ! isset( $data['comment_agent'] ) ? '' : $data['comment_agent'];
    $comment_type     = empty( $data['comment_type'] ) ? 'comment' : $data['comment_type'];
    $comment_parent   = ! isset( $data['comment_parent'] ) ? 0 : $data['comment_parent'];

    $user_id = ! isset( $data['user_id'] ) ? 0 : $data['user_id'];

    $compacted = array(
        'comment_post_ID'   => $comment_post_id,
        'comment_author_IP' => $comment_author_ip,
    );

    $compacted += compact(
        'comment_author',
        'comment_author_email',
        'comment_author_url',
        'comment_date',
        'comment_date_gmt',
        'comment_content',
        'comment_karma',
        'comment_approved',
        'comment_agent',
        'comment_type',
        'comment_parent',
        'user_id'
    );

    if ( ! $wpdb->insert( $wpdb->comments, $compacted ) ) {
        return false;
    }

    $id = (int) $wpdb->insert_id;

    return $id;
}

function wpcli_import_commentmeta( $meta_type, $object_id, $meta_key, $meta_value){
    global $wpdb;

    $table = _get_meta_table( $meta_type );
    if ( ! $table ) {
        return false;
    }

    $column = sanitize_key( $meta_type . '_id' );

    $result = $wpdb->insert(
        $table,
        array(
            $column      => $object_id,
            'meta_key'   => $meta_key,
            'meta_value' => $meta_value,
        )
    );

    if ( ! $result ) {
        return false;
    }

    $mid = (int) $wpdb->insert_id;
}

function wpcli_import_comment_count($post_id){
        // 计算平均评分
        $args = array(
            'post_id' => $post_id, // 替换为文章或页面的ID
            'status' => 'approve', // 只统计已审核的评论
            'meta_key' => 'rating' // 评论评分元数据的键
        );

        $comments = get_comments($args);
        $total_rating = 0;
        $comment_count = 0;

        foreach ($comments as $comment) {
            $total_rating += get_comment_meta($comment->comment_ID, 'rating', true);
            $comment_count++;
        }

        $average_rating = $total_rating / $comment_count;

        // 更新文章的总评分
        update_post_meta($post_id, 'total_rating', $average_rating);
}

?>
