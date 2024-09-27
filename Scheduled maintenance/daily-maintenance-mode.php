<?php
/*
Plugin Name: 每日定时维护
Description: 一网站每天定时进入维护模式。作者纯瞎写着玩的，在作者的网站上可以正常跑，但是俺也不知道会不会有bug。
Version: 1.1Beta
Author: Cormac
*/

// 激活插件时的操作
register_activation_hook(__FILE__, 'dmm_activate');
function dmm_activate() {
    add_option('dmm_mode', 'off');
    add_option('dmm_schedule', array('start' => '', 'end' => ''));
    add_option('dmm_custom_page', '');
    add_option('dmm_custom_message', '网站正在维护中，请稍后访问。');
    add_option('dmm_custom_title', '网站维护');
}

// 停用插件时的操作
register_deactivation_hook(__FILE__, 'dmm_deactivate');
function dmm_deactivate() {
    delete_option('dmm_mode');
    delete_option('dmm_schedule');
    delete_option('dmm_custom_page');
    delete_option('dmm_custom_message');
    delete_option('dmm_custom_title');
}

// 添加“设置成功”提示
add_action('admin_notices', 'dmm_settings_saved_notice');
function dmm_settings_saved_notice() {
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        echo '<div class="notice notice-success is-dismissible"><p>设置成功</p></div>';
    }
}

// 添加设置链接到插件页面
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dmm_add_settings_link');
function dmm_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=dmm_settings">设置</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// 注册设置页面
add_action('admin_menu', 'dmm_add_admin_menu');
function dmm_add_admin_menu() {
    add_options_page(
        '每日定时维护设置', // 页面标题
        '每日定时维护', // 菜单标题
        'manage_options', // 权限
        'dmm_settings', // 菜单别名
        'dmm_settings_page' // 回调函数
    );
}

// 设置页面
function dmm_settings_page() {
    if (isset($_POST['dmm_save'])) {
        update_option('dmm_mode', $_POST['dmm_mode']);
        update_option('dmm_schedule', array('start' => $_POST['dmm_start'], 'end' => $_POST['dmm_end']));
        update_option('dmm_custom_page', $_POST['dmm_custom_page']);
        update_option('dmm_custom_message', $_POST['dmm_custom_message']);
        update_option('dmm_custom_title', $_POST['dmm_custom_title']);
        update_option('dmm_test_mode', isset($_POST['dmm_test_mode']) ? 'on' : 'off'); // 1.1新增
        add_settings_error('dmm_messages', 'dmm_message', '设置成功', 'updated');
        // 重定向以显示“设置成功”提示
        $redirect_url = add_query_arg('settings-updated', 'true', wp_get_referer());
        wp_redirect($redirect_url);
        exit;
    }
    $dmm_mode = get_option('dmm_mode');
    $dmm_schedule = get_option('dmm_schedule');
    $dmm_custom_page = get_option('dmm_custom_page');
    $dmm_custom_message = get_option('dmm_custom_message');
    $dmm_custom_title = get_option('dmm_custom_title');
    $dmm_test_mode = get_option('dmm_test_mode'); // 1.1新增
    ?>
    <div class="wrap">
        <h1>每日定时维护</h1>
        <p>作者：<a href="https://cormac.top">Cormac</a> 版本：1.1Beta</p>
        <p>本项目<a href="https://github.com/Cormac315/Wordpress-Daily-Maintenance-Mode">github地址</a>，有bug请发issues，喜欢的话请点个star哦~</p>
        <form method="post" action="">
            <label for="dmm_mode">总开关：</label>
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
            <label for="dmm_custom_title">自定义维护标题</label>
            <p></p>
            <input type="text" name="dmm_custom_title" id="dmm_custom_title" value="<?php echo esc_attr($dmm_custom_title); ?>">
            <br>
            <p></p>
            <label for="dmm_test_mode">测试模式:无视时间，直接开启维护（需将总开关打开）</label> <!-- 1.1新增 -->
            <input type="checkbox" name="dmm_test_mode" id="dmm_test_mode" <?php checked($dmm_test_mode, 'on'); ?>> <!-- 1.1新增 -->
            <br>
            <p></p>
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
        $dmm_custom_title = get_option('dmm_custom_title');
        $dmm_test_mode = get_option('dmm_test_mode'); // 1.1新增
        $current_time = current_time('H:i');
        
        if ($dmm_mode == 'on' && ($dmm_test_mode == 'on' || ($current_time >= $dmm_schedule['start'] && $current_time <= $dmm_schedule['end']))) {
            if ($dmm_custom_page) {
                wp_redirect(get_permalink($dmm_custom_page));
                exit;
            } else {
                wp_die($dmm_custom_message, $dmm_custom_title);
            }
        }
    }
}
?>
