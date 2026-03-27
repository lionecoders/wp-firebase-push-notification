<?php
if (!defined('ABSPATH')) {exit;}
?>

<h1><?php esc_html_e('Firebase Push Notification', 'wp-firebase-push-notification');?></h1>

<form action="options.php" method="post">
    <?php settings_fields( 'fcm_group'); ?>
    <?php do_settings_sections( 'fcm_group' ); ?>
<table>
    <tbody>

    <tr  height="70">
        <td><label for="fcm_api"><?php esc_html_e("FCM API Key", 'wp-firebase-push-notification');?></label> </td>
        <td><input id="fcm_api" name="stf_fcm_api" type="text" value="<?php echo esc_attr( get_option( 'stf_fcm_api' ) ); ?>" required="required" /></td>
    </tr>

    <tr  height="70">
        <td><label for="stf_align"><?php esc_html_e("FCM Option", 'wp-firebase-push-notification');?></label></td>
        <td>
            <!-- Using selected() instead -->
            <select name="fcm_option">
                <option value="topic"   <?php selected( get_option( 'fcm_option' ), 'topic'); ?>><?php esc_html_e("Topic", 'wp-firebase-push-notification');?></option>
            </select>
        </td>
    </tr>

    <tr  height="70">
        <td><label for="fcm_topic"><?php esc_html_e("FCM Topic Setup in Application", 'wp-firebase-push-notification');?></label> </td>
        <td><input id="fcm_topic" placeholder="Name of Topic setup in application" name="fcm_topic" type="text" value="<?php echo esc_attr( get_option( 'fcm_topic' ) );  ?>" required="required" /></td>
    </tr>

    <tr  height="70">
        <td><label for="post_disable"><?php esc_html_e("Disable Push Notification on Post Save", 'wp-firebase-push-notification');?></label> </td>
        <td><input id="post_disable" name="fcm_disable" type="checkbox" value="1" <?php checked( '1', get_option( 'fcm_disable' ) ); ?>  /></td>
    </tr>

    <tr  height="70">
        <td><label for="update_disable"><?php esc_html_e("Disable Push Notification on Post Update", 'wp-firebase-push-notification');?></label> </td>
        <td><input id="update_disable" name="fcm_update_disable" type="checkbox" value="1" <?php checked( '1', get_option( 'fcm_update_disable' ) ); ?>  /></td>
    </tr>

    <tr  height="70">
        <td><label for="page_disable"><?php esc_html_e("Disable Push Notification on Page Save", 'wp-firebase-push-notification');?></label> </td>
        <td><input id="page_disable" name="fcm_page_disable" type="checkbox" value="1" <?php checked( '1', get_option( 'fcm_page_disable' ) ); ?>  /></td>
    </tr>

    <tr  height="70">
        <td><label for="fcm_update_page_disable"><?php esc_html_e("Disable Push Notification on Page Update", 'wp-firebase-push-notification');?></label> </td>
        <td><input id="fcm_update_page_disable" name="fcm_update_page_disable" type="checkbox" value="1" <?php checked( '1', get_option( 'fcm_update_page_disable' ) ); ?>  /></td>
    </tr>


    <tr>
        <td> <div class="col-sm-10"><?php submit_button(); ?></td>

    </tr>

    </tbody>
    </table>

</form>

<?php if(get_option('stf_fcm_api')){ ?>
<div>
    <h3>Test Notification</h3>
    <p>Notification sent to device, have above setup Topic</p>
    <a href="<?php echo esc_url( admin_url('admin.php') ); ?>?page=test_notification">Test Notification</a>
</div>

<?php
}
?>
