<?php
if (!defined('ABSPATH')) {
    exit();
}

global $current_tab;

$current_tab = 'general';

if( !empty($_REQUEST['tab']) )
    $current_tab = $_REQUEST['tab'];

$tabs = array (
    'general' 		=> esc_html__('General', 'envato-market'),
    'cronjob'       => esc_html__('Cron Job', 'envato-market'),
    'notices'		=> esc_html__('Email & Notices', 'envato-market'),
);
?>
<div class="wrap">
    <h2>Market Setting</h2>

    <form method="post" action="options.php">

        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <?php foreach ($tabs as $key => $tab): ?>
                <a href="<?php echo admin_url( 'admin.php?page=envato-market-options&tab=' . $key ); ?>" class="nav-tab<?php echo ( $current_tab == $key ? ' nav-tab-active' : '' ) ; ?>"><?php echo esc_html($tab); ?></a>
            <?php endforeach; ?>
        </h2>

        <?php $tab_dir = envato_market()->plugin_dir . "inc/admin-page/admin.tab-{$current_tab}.php"; if(file_exists($tab_dir)) require_once ($tab_dir); ?>

        <?php submit_button(); ?>

    </form>
</div>
