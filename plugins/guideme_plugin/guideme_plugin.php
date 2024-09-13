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
    <script src="//geoip-js.com/js/apis/geoip2/v2.1/geoip2.js" type="text/javascript"></script>
    <script type="text/javascript">
        var city;
        var country;
        var country_code;
        var continent;
        var location;
        var ip_address;
        var time_zone;
        var autonomous_system_organization;
        var organization;
        var duration;
        var visitedPages;
        var sessionStartTime;
        var interactions;

        var onSuccess = function(geoipResponse) {
            city = geoipResponse.city.names.en;
            continent = geoipResponse.code;
            country_code = geoipResponse.country.iso_code;
            country = geoipResponse.country.names.en;
            time_zone = geoipResponse.location.time_zone;
            ip_address = geoipResponse.traits.ip_address;
            autonomous_system_organization = geoipResponse.traits.autonomous_system_organization;
            organization = geoipResponse.traits.organization;
        };

        // If we get an error, we will display an error message
        var onError = function(error) {
            console.log('an error!  Please try again..');
        };

        if (typeof geoip2 !== 'undefined') {
            geoip2.city(onSuccess, onError);
        } else {
            console.log('a browser that blocks GeoIP2 requests');
        }

        function calculateSessionDuration() {
            const sessionStartTime = parseInt(localStorage.getItem('sessionStartTime'), 10);
            const currentTime = new Date().getTime();
            const durationInMilliseconds = currentTime - sessionStartTime;
            return Math.floor(durationInMilliseconds / 1000);
        }

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
                "api_response": event.detail.apiResponse,
                "screen_sizes": {
                    "height": window.screen.height,
                    "width": window.screen.width,
                },
                "referrer": document.referrer,
                "userAgent": navigator.userAgent,
                "city": city,
                "continent": continent,
                "country_code": country_code,
                "country": country,
                "time_zone": time_zone,
                "ip_address": ip_address,
                "autonomous_system_organization": autonomous_system_organization,
                "organization": organization,
                "duration": calculateSessionDuration(),
                "visitedPages": localStorage.getItem("visitedPages"),
                "sessionStartTime": localStorage.getItem("sessionStartTime"),
                "interactions": localStorage.getItem("interactions"),
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

function tracking_hook_javascript()
{
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', () => {
            // Helper function to get current time
            function getCurrentTime() {
                return new Date().getTime();
            }

            // Initialize or update the session start time
            if (!localStorage.getItem('sessionStartTime')) {
                localStorage.setItem('sessionStartTime', getCurrentTime());
            }

            // Track the number of page clicks
            const currentPage = window.location.href;

            // Get the stored visited pages or initialize an empty array
            let visitedPages = JSON.parse(localStorage.getItem('visitedPages')) || [];

            // Check if the current page is already in the visitedPages array
            if (!visitedPages.includes(currentPage)) {
                visitedPages.push(currentPage);  // Add the current page to visitedPages
                localStorage.setItem('visitedPages', JSON.stringify(visitedPages));  // Store the updated list
            }

            // Count the number of unique URLs visited
            const pageVisitCount = visitedPages.length;
            console.log(`User has visited ${pageVisitCount} unique pages.`);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Helper function to store interaction data in localStorage
            function storeInteractionData(category, action, label) {
                const timestamp = new Date().toISOString();
                const interactionData = {
                    category: category,
                    action: action,
                    label: label,
                    timestamp: timestamp
                };

                // Retrieve existing data from localStorage
                let interactions = JSON.parse(localStorage.getItem('interactions')) || [];
                interactions.push(interactionData);

                // Store updated data back to localStorage
                localStorage.setItem('interactions', JSON.stringify(interactions));
            }

            // Button clicks
            document.querySelectorAll('button').forEach(button => {
                button.addEventListener('click', (event) => {
                    storeInteractionData('Button Click', event.target.textContent || 'Unknown Button', window.location.href);
                });
            });

            // Navigation clicks (e.g., menu, links)
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', (event) => {
                    storeInteractionData('Navigation Click', event.target.href, window.location.href);
                });
            });

            // Call-to-action (CTA) clicks
            document.querySelectorAll('.cta-button').forEach(button => {
                button.addEventListener('click', (event) => {
                    storeInteractionData('CTA Click', event.target.textContent || 'Unknown CTA', window.location.href);
                });
            });

            // Product or content clicks
            document.querySelectorAll('.product-item, .content-item').forEach(item => {
                item.addEventListener('click', (event) => {
                    storeInteractionData('Product/Content Click', event.target.dataset.itemId || 'Unknown Item', window.location.href);
                });
            });

            // Advertisement clicks
            document.querySelectorAll('.ad').forEach(ad => {
                ad.addEventListener('click', (event) => {
                    storeInteractionData('Advertisement Click', event.target.dataset.adId || 'Unknown Ad', window.location.href);
                });
            });

            // Social sharing button clicks
            document.querySelectorAll('.social-share').forEach(button => {
                button.addEventListener('click', (event) => {
                    storeInteractionData('Social Share Click', event.target.dataset.network || 'Unknown Network', window.location.href);
                });
            });

            // Dropdown or option clicks
            document.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', (event) => {
                    storeInteractionData('Dropdown Click', event.target.value, window.location.href);
                });
            });

            // Clicks on dynamic elements
            document.querySelectorAll('.dynamic-element').forEach(element => {
                element.addEventListener('click', (event) => {
                    storeInteractionData('Dynamic Element Click', event.target.dataset.elementId || 'Unknown Element', window.location.href);
                });
            });

            // Checkbox/radio button selections
            document.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(input => {
                input.addEventListener('change', (event) => {
                    storeInteractionData('Checkbox/Radio Click', event.target.name + ': ' + event.target.value, window.location.href);
                });
            });
        });
    </script>
    <?php
}
add_action('wp_head', 'tracking_hook_javascript');


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
