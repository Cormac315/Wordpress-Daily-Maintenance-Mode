<?php
/*
Plugin Name: 每日定时维护
Description: 一网站每天定时进入维护模式。作者纯瞎写着玩的，在作者的网站上可以正常跑，但是俺也不知道会不会有bug。
Version: 1.0Beta
Author: Cormac
*/

// 激活插件时的操作
register_activation_hook(__FILE__, 'dmm_activate');
function dmm_activate() {
    add_option('dmm_mode', 'off');
    add_option('dmm_schedule', array('start' => '', 'end' => ''));
    add_option('dmm_custom_page', '');
    add_option('dmm_custom_message', 'The site is under maintenance. Please check back later.');
}

// 停用插件时的操作
register_deactivation_hook(__FILE__, 'dmm_deactivate');
function dmm_deactivate() {
    delete_option('dmm_mode');
    delete_option('dmm_schedule');
    delete_option('dmm_custom_page');
    delete_option('dmm_custom_message');
}

// 添加管理菜单
add_action('admin_menu', 'dmm_admin_menu');
function dmm_admin_menu() {
    add_options_page('Daily Maintenance Mode', '每日定时维护', 'manage_options', 'daily-maintenance-mode', 'dmm_settings_page');
}

// 设置页面
function dmm_settings_page() {
    if (isset($_POST['dmm_save'])) {
        update_option('dmm_mode', $_POST['dmm_mode']);
        update_option('dmm_schedule', array('start' => $_POST['dmm_start'], 'end' => $_POST['dmm_end']));
        update_option('dmm_custom_page', $_POST['dmm_custom_page']);
        update_option('dmm_custom_message', $_POST['dmm_custom_message']);
    }
    $dmm_mode = get_option('dmm_mode');
    $dmm_schedule = get_option('dmm_schedule');
    $dmm_custom_page = get_option('dmm_custom_page');
    $dmm_custom_message = get_option('dmm_custom_message');
    ?>
    <div class="wrap">
        <h1>每日定时维护</h1>
        <p>作者：Cormac        版本：1.0Beta</p>
        <p>欢迎访问<a href="https://cormac.top">我的博客</a>~~</p>
        <p>本项目<a href="https://github.com/cormac-top/daily-maintenance-mode">github地址</a>，喜欢的话请点个star哦~</p>
        <form method="post" action="">
            <label for="dmm_mode">维护模式：</label>
            <select name="dmm_mode" id="dmm_mode">
                <option value="off" <?php selected($dmm_mode, 'off'); ?>>关闭</option>
                <option value="on" <?php selected($dmm_mode, 'on'); ?>>开启</option>
            </select>
            <h2>定时</h2>
            <label for="dmm_start">开始时间: </label>
            <input type="time" name="dmm_start" id="dmm_start" value="<?php echo $dmm_schedule['start']; ?>">
            <br>
            <label for="dmm_end">结束时间: </label>
            <input type="time" name="dmm_end" id="dmm_end" value="<?php echo $dmm_schedule['end']; ?>">
            <br>
            <h2>自定义维护界面</h2>
            <p>您可以在下方选择已有界面。在每天的维护时间内，网站将被重定向到指定的页面。</p>
            <p>您也可以选择默认页面，默认页面的维护信息是可定义的。</p>
            <p></p>
            <label for="dmm_custom_page">选择一个已有页面：</label>
            <select name="dmm_custom_page" id="dmm_custom_page">
                <option value="">默认</option>
                <?php
                $pages = get_pages();
                foreach ($pages as $page) {
                    echo '<option value="' . $page->ID . '" ' . selected($dmm_custom_page, $page->ID, false) . '>' . $page->post_title . '</option>';
                }
                ?>
            </select>
            <br>
            <p></p>
            <label for="dmm_custom_message">自定义维护信息</label>
            <p></p>
            <textarea name="dmm_custom_message" id="dmm_custom_message" rows="4" cols="50"><?php echo esc_textarea($dmm_custom_message); ?></textarea>
            <br>
            <input type="submit" name="dmm_save" value="保存">
        </form>
    </div>
    <?php
}

// 检查并应用维护模式
add_action('init', 'dmm_check_maintenance_mode');
function dmm_check_maintenance_mode() {
    if (!current_user_can('administrator')) {
        $dmm_mode = get_option('dmm_mode');
        $dmm_schedule = get_option('dmm_schedule');
        $dmm_custom_page = get_option('dmm_custom_page');
        $dmm_custom_message = get_option('dmm_custom_message');
        $current_time = current_time('H:i');
        
        if ($dmm_mode == 'on' && ($current_time >= $dmm_schedule['start'] && $current_time <= $dmm_schedule['end'])) {
            if ($dmm_custom_page) {
                wp_redirect(get_permalink($dmm_custom_page));
                exit;
            } else {
                wp_die($dmm_custom_message);
            }
        }
    }
}
?>
