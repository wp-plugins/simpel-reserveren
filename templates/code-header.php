<?php
/* We add some JavaScript to pages with the comment form
 * to support sites with threaded comments (when in use).
 */
if ( is_singular() && get_option( 'thread_comments' ) )
	wp_enqueue_script( 'comment-reply' );

/* Always have wp_head() just before the closing </head>
 * tag of your theme, or you will break many plugins, which
 * generally use this hook to add elements to <head> such
 * as styles, scripts, and meta tags.
 */
wp_deregister_script('jquery'); // wordt al eerder ingeladen in header
wp_head();
wp_footer();
if(SITE == 'stoetenslagh') : ?>
	<link rel='stylesheet' id='booking-stoeten-style-css'  href='<?php echo  $this->plugin_url ?>css/stoetenslagh.css?ver=1.1' type='text/css' media='all' />
<? else : ?>
	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<!--[if lte IE 7]><link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('template_directory'); ?>/style-ie.css" /><![endif]-->
<? endif; ?>
<? if(SITE && SITE != 'stoetenslagh') : ?>
	<link rel='stylesheet' id='booking-stoeten-style-css'  href='<?php echo  $this->plugin_url ?>css/micro.css?ver=1.1' type='text/css' media='all' />
<? endif; ?>
<script> var root_url = '<?php echo  site_url() ?>'; </script>