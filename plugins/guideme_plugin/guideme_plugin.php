<?php
/**
 * Plugin Name:       MT CRM Plugin
 * Plugin URI:        https://maltalovers.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Camilo Lucena
 * Author URI:        https://www.linkedin.com/in/camilo-lucena/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       mt-crm
 * Domain Path:       /languages
 */
function wpb_hook_javascript()
{
    ?>
    <script type="text/javascript">
        function submit(event) {
            const req = new XMLHttpRequest();
            req.open("POST", "<?= get_option('backend_url'); ?>");
            req.setRequestHeader('Content-Type', 'application/json');
            req.setRequestHeader('API-Validation', "<?= get_option('api_token'); ?>");
            req.setRequestHeader('Accept', 'application/json, text/plain');
            const form = {
                "external_id": event.detail.contactFormId,
                "response": event.detail.inputs,
                "status": event.detail.apiResponse.status,
                "api_response": event.detail.apiResponse
            }
            req.send(JSON.stringify(form));
            req.onload = function () {
                if (req.status === 200) {
                    var data = JSON.parse(req.responseText);
                    console.log('POST',data)
                }
            };
        }
        document.addEventListener( 'wpcf7submit', function( event ) {
            submit(event);
        }, false );
    </script>
    <?php
}
add_action('wp_head', 'wpb_hook_javascript');

function mt_options() {
    $edit = add_menu_page(
        'MaltaLovers - Options',
        'MaltaLovers - Options',
        'administrator',
        'malta-options',
        'plugin_mt_options_page',
        'dashicons-admin-generic',
        false
    );
}




function plugin_mt_options_page() {
    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"></div>
        <h1>MaltaLovers Backend Options</h1>
        <?php settings_errors(); ?>
        <form method="POST" action="options.php">
        <?php
        do_settings_sections('malta-options');
        settings_fields('header_section');
        submit_button();
        ?>
        </form>
    </div>
<?php
}

function display_options()
{
    add_settings_section("backend_url", "Backend URL", "display_logo_form_element", "malta-options");
    add_settings_section("api_token", "API Token", "display_advertising_form_element", "malta-options");

    register_setting('header_section', 'backend_url');
    register_setting('header_section', 'api_token');
}

function display_logo_form_element() {
    ?>
    <input type="text" name="backend_url" id="backend_url" value="<?php echo get_option('backend_url'); ?>">
    <?php
}

function display_advertising_form_element() {
    ?>
    <input type="text" name="api_token" id="api_token" value="<?php echo get_option('api_token'); ?>">
    <?php
}

add_action('admin_init', 'display_options');
add_action( 'admin_menu', 'mt_options' );
