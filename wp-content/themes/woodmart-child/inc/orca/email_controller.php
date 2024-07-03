<?php

// 禁止新用户注册时发送邮件给注册用户
add_filter( 'wp_new_user_notification_email', '__return_false' );
// 禁止新用户注册时发送邮件给管理员
add_filter( 'wp_new_user_notification_email_admin', '__return_false' );
// 禁止重置密码时发送邮件给用户
add_filter( 'send_password_change_email', '__return_false' );
// 禁止重置密码时发送邮件给管理员
add_filter( 'wp_password_change_notification_email', '__return_false' );
// 禁止邮箱地址改变时发送邮件给用户
add_filter( 'send_email_change_email', '__return_false' );

add_filter( 'recovery_email_debug_info', '__return_false');