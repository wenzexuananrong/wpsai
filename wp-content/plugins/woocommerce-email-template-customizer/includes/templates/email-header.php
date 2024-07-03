<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--[if !mso]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <![endif]-->

	<?php do_action( 'viwec_email_header' ); ?>

    <!--[if (mso 16)]>
    <style type="text/css">
        a {
            text-decoration: none;
        }

        span {
            vertical-align: middle;
        }
    </style>
    <![endif]-->

    <style type="text/css">
        #outlook a {
            padding: 0;
        }

        a {
            text-decoration: none;
            word-break: break-word;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            vertical-align: middle;
            background-color: transparent;
            max-width: 100%;
        }

        p {
            display: block;
            margin: 0;
            line-height: inherit;
            /*font-size: inherit;*/
        }

        div.viwec-responsive {
            display: inline-block;
        }

        small {
            display: block;
            font-size: 13px;
        }

        #viwec-transferred-content small {
            display: inline;
        }

        #viwec-transferred-content td {
            vertical-align: top;
        }

        td.viwec-row {
            background-repeat: no-repeat;
            background-size: cover;
            background-position: top;
        }
    </style>

    <!--[if mso]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->


    <!--[if mso | IE]>
    <style type="text/css">
        .viwec-responsive {
            /*width: 100% !important;*/
        }

        small {
            display: block;
            font-size: 13px;
        }

        table {
            font-family: Helvetica, Arial, sans-serif;
        }
    </style>
    <![endif]-->

    <style type="text/css">

        @media only screen and (min-width: <?php echo esc_attr($responsive);?>px) {
            a {
                text-decoration: none;
            }

            td {
                overflow: hidden;
            }

            div.viwec-responsive {
                display: inline-block;
            }

            .viwec-responsive-min-width {
                min-width: <?php echo esc_attr($width);?>px;
            }
        }

        @media only screen and (max-width: <?php echo esc_attr($responsive);?>px) {
            a {
                text-decoration: none;
            }

            td {
                overflow: hidden;
            }

            img {
                padding-bottom: 10px;
            }

            .viwec-responsive, .viwec-responsive table, .viwec-button-responsive {
                width: 100% !important;
                min-width: 100%;
            }

            table.viwec-no-full-width-on-mobile {
                min-width: 0 !important;
                width: auto !important;
            }

            .viwec-responsive-padding {
                padding: 0 !important;
            }

            .viwec-mobile-hidden {
                display: none !important;
            }

            .viwec-responsive-center, .viwec-responsive-center p {
                text-align: center !important;
            }

            .viwec-mobile-50 {
                width: 50% !important;
            }

            .viwec-center-on-mobile p {
                text-align: center !important;
            }

            #body_content {
                min-width: 100% !important;
            }
        }

        <?php echo wp_kses_post( apply_filters('viwec_after_render_style','') )?>
    </style>

</head>

<body vlink="#FFFFFF" <?php echo $direction == 'rtl' ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">

