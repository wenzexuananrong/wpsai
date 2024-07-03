<?php
//优化分类页面左分类导航长度显示问题
function orca_wp_print_inline_script_tag_ppb(){
    wp_print_inline_script_tag( "
        jQuery(document).ready(function() {
            window.onscroll = function(){

                let left = document.querySelector('.wd-alignment-left');
                if(!left){
                    return ;
                }

                let right = document.querySelector('.wd-alignment-left').nextElementSibling;

                // 设置子元素的最大高度与兄弟元素相同
                if(left.offsetHeight> 2080){
                    if(left.offsetHeight > right.offsetHeight){

                      left.style.maxHeight = right.offsetHeight + 'px';
                      left.style.overflowY = 'scroll';

                    } else {
                        if(left.scrollHeight > left.offsetHeight) {
                            left.style.maxHeight = right.offsetHeight + 'px';
                        } else {
                            left.style.overflowY = '';
                        }
                        console.log('scroll')
                    }
                }
            }

            window.addEventListener('resize', () => {
                
                let left = document.querySelector('.wd-alignment-left');
                if(!left){
                    return ;
                }

                let right = document.querySelector('.wd-alignment-left').nextElementSibling;

                // 设置子元素的最大高度与兄弟元素相同
                if(left.offsetHeight> 2080){
                    if(left.offsetHeight > right.offsetHeight){

                      left.style.maxHeight = right.offsetHeight + 'px';
                      left.style.overflowY = 'scroll';

                    } else {
                        if(left.scrollHeight > left.offsetHeight) {
                            left.style.maxHeight = right.offsetHeight + 'px';
                        } else {
                            left.style.overflowY = '';
                        }
                    }
                }

               // console.log(left.scrollHeight, left.offsetHeight, right.offsetHeight)
            });
            
        });
    ", [ 'type' => 'text/javascript' ] );
}

add_action('wp_head', 'orca_wp_print_inline_script_tag_ppb');