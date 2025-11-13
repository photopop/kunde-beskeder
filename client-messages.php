<?php
/**
 * Plugin Name:       Client Messages (Spjaet)
 * Plugin URI:        https://spjaet.dk
 * Description:       Displays targeted messages to customers in their WooCommerce "My Account" area.
 * Version:           1.3.7-EN
 * Author:            Runolfur / Spjaet
 * Author URI:        https://spjaet.dk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kunde-beskeder
 * Domain Path:       /languages
 */

// Stop direkte adgang
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ========================================================================
 * ADMIN FUNCTIONS (CPT, METABOX, COLUMNS)
 * ========================================================================
 */

/**
 * STEP 2: Register Custom Post Type 'kunde_besked'
 */
function kb_opret_cpt() {
    $labels = array(
        'name'                  => _x( 'Client Messages', 'Post Type General Name', 'kunde-beskeder' ),
        'singular_name'         => _x( 'Client Message', 'Post Type Singular Name', 'kunde-beskeder' ),
        'menu_name'             => __( 'Client Messages', 'kunde-beskeder' ),
        'name_admin_bar'        => __( 'Client Message', 'kunde-beskeder' ),
        'all_items'             => __( 'All Messages', 'kunde-beskeder' ),
        'add_new_item'          => __( 'Add New Message', 'kunde-beskeder' ),
        'add_new'               => __( 'Add New', 'kunde-beskeder' ),
        'new_item'              => __( 'New Message', 'kunde-beskeder' ),
        'edit_item'             => __( 'Edit Message', 'kunde-beskeder' ),
        'update_item'           => __( 'Update Message', 'kunde-beskeder' ),
    );
    $args = array(
        'label'                 => __( 'Client Message', 'kunde-beskeder' ),
        'description'           => __( 'Internal messages for customers', 'kunde-beskeder' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor' ),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true, 
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-email-alt',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    register_post_type( 'kunde_besked', $args );
}
add_action( 'init', 'kb_opret_cpt', 0 );


/**
 * STEP 3: Create Metabox for Targeting (SIMPLE VERSION)
 */

function kb_tilfoej_metabox() {
    add_meta_box(
        'kb_maalretning_metabox', __( 'Message Targeting', 'kunde-beskeder' ), 'kb_metabox_html_output', 'kunde_besked', 'side', 'high'
    );
}
add_action( 'add_meta_boxes', 'kb_tilfoej_metabox' );

// HTML for metabox
function kb_metabox_html_output( $post ) {
    $maal_type  = get_post_meta( $post->ID, '_kb_maal_type', true );
    $statusser  = get_post_meta( $post->ID, '_kb_statusser', true );
    $produkt_id = get_post_meta( $post->ID, '_kb_produkt_id', true );
    $kunde_id   = get_post_meta( $post->ID, '_kb_kunde_id', true );

    $statusser = ! is_array( $statusser ) ? array() : $statusser;
    
    wp_nonce_field( 'kb_gem_metabox_data', 'kb_metabox_nonce' );
    ?>
    <style>
        .kb-fields-wrapper { padding: 10px; }
        .kb-field-group { margin-top: 15px; padding-left: 20px; border-left: 3px solid #f0f0f1; display: none; }
        .kb-field-group p { margin-top: 5px; }
        .kb-fields-wrapper label { display: block; margin-bottom: 5px; font-weight: bold; }
        .kb-fields-wrapper input[type="radio"], .kb-fields-wrapper input[type="checkbox"] { margin-right: 5px; }
        .kb-field-group.kb-active { display: block; }
        .kb-fields-wrapper input[type="number"] { width: 100%; }
    </style>
    <div class="kb-fields-wrapper">
        <p><?php _e('Select how to target this message.', 'kunde-beskeder'); ?></p>
        <div>
            <label><input type="radio" class="kb_maal_type_radio" name="kb_maal_type" value="status" <?php checked( $maal_type, 'status' ); ?>> <?php _e('Target by Subscription Status', 'kunde-beskeder'); ?></label>
            <label><input type="radio" class="kb_maal_type_radio" name="kb_maal_type" value="produkt" <?php checked( $maal_type, 'produkt' ); ?>> <?php _e('Target by Product (Team) ID', 'kunde-beskeder'); ?></label>
            <label><input type="radio" class="kb_maal_type_radio" name="kb_maal_type" value="kunde" <?php checked( $maal_type, 'kunde' ); ?>> <?php _e('Target specific Customer ID', 'kunde-beskeder'); ?></label>
        </div>
        
        <div id="kb-status-group" class="kb-field-group">
            <p><?php _e('Show to customers with status:', 'kunde-beskeder'); ?></p>
            <label><input type="checkbox" name="kb_statusser[]" value="wc-active" <?php checked( in_array( 'wc-active', $statusser ) ); ?>> <?php _e('Active', 'kunde-beskeder'); ?></label>
            <label><input type="checkbox" name="kb_statusser[]" value="wc-on-hold" <?php checked( in_array( 'wc-on-hold', $statusser ) ); ?>> <?php _e('On-hold', 'kunde-beskeder'); ?></label>
            <label><input type="checkbox" name="kb_statusser[]" value="wc-pending" <?php checked( in_array( 'wc-pending', $statusser ) ); ?>> <?php _e('Pending', 'kunde-beskeder'); ?></label>
        </div>
        
        <div id="kb-produkt-group" class="kb-field-group">
            <p><?php _e('Show to customers on this team:', 'kunde-beskeder'); ?></p>
            <label for="kb_produkt_id_input"><?php _e('Product ID:', 'kunde-beskeder'); ?></label>
            <input type="number" id="kb_produkt_id_input" name="kb_produkt_id" value="<?php echo esc_attr( $produkt_id ); ?>" placeholder="<?php _e('Find product ID under \'Products\'...', 'kunde-beskeder'); ?>">
        </div>
        
        <div id="kb-kunde-group" class="kb-field-group">
            <p><?php _e('Show only to this customer:', 'kunde-beskeder'); ?></p>
            <label for="kb_kunde_id_input"><?php _e('Customer ID:', 'kunde-beskeder'); ?></label>
            <input type="number" id="kb_kunde_id_input" name="kb_kunde_id" value="<?php echo esc_attr( $kunde_id ); ?>" placeholder="<?php _e('Find customer ID under \'Users\'...', 'kunde-beskeder'); ?>">
        </div>
    </div>
    <?php
}

// Save metabox data
function kb_gem_metabox_data( $post_id ) {
    if ( ! isset( $_POST['kb_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['kb_metabox_nonce'], 'kb_gem_metabox_data' ) ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    }
    
    if ( isset( $_POST['kb_maal_type'] ) ) {
        $type = sanitize_text_field( $_POST['kb_maal_type'] );
        update_post_meta( $post_id, '_kb_maal_type', $type );

        if ( $type == 'status' && isset( $_POST['kb_statusser'] ) ) {
            $status_array = (array) $_POST['kb_statusser'];
            $clean_statusser = array_map( 'sanitize_text_field', $status_array );
            update_post_meta( $post_id, '_kb_statusser', $clean_statusser );
        } elseif ( $type == 'produkt' && isset( $_POST['kb_produkt_id'] ) ) {
            update_post_meta( $post_id, '_kb_produkt_id', absint( $_POST['kb_produkt_id'] ) );
        } elseif ( $type == 'kunde' && isset( $_POST['kb_kunde_id'] ) ) {
            update_post_meta( $post_id, '_kb_kunde_id', absint( $_POST['kb_kunde_id'] ) );
        }
    }
}
add_action( 'save_post_kunde_besked', 'kb_gem_metabox_data' ); 


/**
 * STEP 3b: Simple JS for show/hide
 */
function kb_indlaes_admin_scripts( $hook ) {
    global $post_type;
    if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && $post_type == 'kunde_besked' ) {
        add_action('admin_footer', 'kb_metabox_footer_js', 99);
    }
}
add_action( 'admin_enqueue_scripts', 'kb_indlaes_admin_scripts' );

function kb_metabox_footer_js() {
    global $post_type;
    if ($post_type != 'kunde_besked') return;
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            
            function toggleMaalretningGroups() {
                var valgtType = $('input[name="kb_maal_type"]:checked').val();
                $('.kb-field-group').removeClass('kb-active');
                if (valgtType === 'status') {
                    $('#kb-status-group').addClass('kb-active');
                } else if (valgtType === 'produkt') {
                    $('#kb-produkt-group').addClass('kb-active');
                } else if (valgtType === 'kunde') {
                    $('#kb-kunde-group').addClass('kb-active');
                }
            }
            toggleMaalretningGroups();
            $('input[name="kb_maal_type"]').on('change', toggleMaalretningGroups);
        });
    </script>
    <?php
}

/**
 * STEP 3.5: Add Admin Columns
 */

function kb_tilfoej_admin_kolonne_header( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $title ) {
        $new_columns[$key] = $title;
        if ( $key === 'title' ) {
            $new_columns['kb_maalretning'] = __( 'Targeting', 'kunde-beskeder' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_kunde_besked_posts_columns', 'kb_tilfoej_admin_kolonne_header' );

function kb_vis_admin_kolonne_indhold( $column, $post_id ) {
    
    if ( $column === 'kb_maalretning' ) {
        $maal_type = get_post_meta( $post_id, '_kb_maal_type', true );

        switch ( $maal_type ) {
            case 'status':
                $statusser = get_post_meta( $post_id, '_kb_statusser', true );
                if ( empty( $statusser ) || ! is_array( $statusser ) ) {
                    echo '<em>' . __('Status: None selected', 'kunde-beskeder') . '</em>';
                } else {
                    $pæne_navne = array();
                    foreach ( $statusser as $status ) {
                        if ( $status === 'wc-active' ) $pæne_navne[] = __('Active', 'kunde-beskeder');
                        elseif ( $status === 'wc-on-hold' ) $pæne_navne[] = __('On-hold', 'kunde-beskeder');
                        elseif ( $status === 'wc-pending' ) $pæne_navne[] = __('Pending', 'kunde-beskeder');
                        else $pæne_navne[] = ucfirst( $status );
                    }
                    echo '<strong>' . __('Status:', 'kunde-beskeder') . '</strong> ' . esc_html( implode( ', ', $pæne_navne ) );
                }
                break;
            
            case 'produkt':
                $produkt_id = absint( get_post_meta( $post_id, '_kb_produkt_id', true ) );
                $produkt_titel = get_the_title( $produkt_id );
                if ( empty($produkt_titel) ) $produkt_titel = __('Unknown Product', 'kunde-beskeder');
                echo '<strong>' . __('Product:', 'kunde-beskeder') . '</strong> ' . esc_html( $produkt_titel ) . ' (ID: ' . $produkt_id . ')';
                break;

            case 'kunde':
                $kunde_id = absint( get_post_meta( $post_id, '_kb_kunde_id', true ) );
                $kunde = get_user_by( 'id', $kunde_id );
                if ( $kunde ) {
                    echo '<strong>' . __('Customer:', 'kunde-beskeder') . '</strong> ' . esc_html( $kunde->display_name ) . ' (ID: ' . $kunde_id . ')';
                } else {
                    echo '<strong>' . __('Customer:', 'kunde-beskeder') . '</strong> <em>' . __('Unknown (ID: ', 'kunde-beskeder') . $kunde_id . ')</em>';
                }
                break;
            
            default:
                echo '<em>' . __('Not specified', 'kunde-beskeder') . '</em>';
                break;
        }
    }
}
add_action( 'manage_kunde_besked_posts_custom_column', 'kb_vis_admin_kolonne_indhold', 10, 2 );

/**
 * STEP 3.6: Add Opt-in field to Admin User Profile
 */
 
function kb_vis_admin_notits_felt( $user ) {
    $meta_value = get_user_meta( $user->ID, '_kb_email_notits', true );
    $er_checked = ( $meta_value === '0' ) ? 0 : 1; // Default to 'yes' (1)
    ?>
    <h3><?php _e('Client Message Notifications', 'kunde-beskeder'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="_kb_email_notits"><?php _e('Receive Emails', 'kunde-beskeder'); ?></label></th>
            <td>
                <input type="checkbox" name="_kb_email_notits" id="_kb_email_notits" value="1" <?php checked( $er_checked, 1 ); ?>>
                <span class="description"><?php _e('Send the user an email notification when there is a new message.', 'kunde-beskeder'); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'kb_vis_admin_notits_felt' );
add_action( 'edit_user_profile', 'kb_vis_admin_notits_felt' );

function kb_gem_admin_notits_felt( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }
    $value = isset( $_POST['_kb_email_notits'] ) ? 1 : 0;
    update_user_meta( $user_id, '_kb_email_notits', $value );
}
add_action( 'personal_options_update', 'kb_gem_admin_notits_felt' );
add_action( 'edit_user_profile_update', 'kb_gem_admin_notits_felt' );


/**
 * ========================================================================
 * FRONTEND FUNCTIONS (MY ACCOUNT)
 * ========================================================================
 */

/**
 * STEP 4.1: Add 'messages' endpoint.
 */
function kb_tilfoej_endpoint() {
    add_rewrite_endpoint( 'messages', EP_PAGES ); // 'beskeder' -> 'messages'
}
add_action( 'init', 'kb_tilfoej_endpoint' );

/**
 * STEP 4.2: Add new menu item "Messages".
 */
function kb_tilfoej_menu_link( $items ) {
    if ( isset( $items['customer-logout'] ) ) {
        $logout_link = $items['customer-logout'];
        unset( $items['customer-logout'] );
        $items['messages'] = __( 'Messages', 'kunde-beskeder' ); // 'beskeder' -> 'messages'
        $items['customer-logout'] = $logout_link;
    } else {
        $items['messages'] = __( 'Messages', 'kunde-beskeder' );
    }
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'kb_tilfoej_menu_link', 40 );

/**
 * STEP 4.3: Load and display content for the "Messages" tab.
 */
function kb_vis_beskeder_indhold() {
    
    echo '<h2>' . esc_html__( 'Your Messages', 'kunde-beskeder' ) . '</h2>';

    $matchende_beskeder = kb_hent_matchende_beskeder();

    if ( empty( $matchende_beskeder ) ) {
        echo '<p>' . esc_html__( 'You have no new messages.', 'kunde-beskeder' ) . '</p>';
    } else {
        echo '<ul class="kb-besked-liste">';
        
        $count = 0; 
        foreach ( $matchende_beskeder as $besked_post ) {
            $count++;
            $besked_dato = get_the_date( 'd. F Y', $besked_post );
            
            $indhold = apply_filters( 'the_content', $besked_post->post_content );
            $skal_foldes_ud = false;
            $limit_ord = 55; 
            
            $kort_indhold = wp_trim_words( $indhold, $limit_ord, '' ); 
            
            if ( $kort_indhold !== $indhold ) {
                 $skal_foldes_ud = true;
                 $kort_indhold .= '...'; 
            }

            echo '<li class="kb-besked-item">';
            echo '<div class="kb-besked-header">';
            echo '<h3>' . esc_html( $besked_post->post_title ) . '</h3>';
            echo '<span class="kb-besked-dato">' . esc_html( $besked_dato ) . '</span>';
            echo '</div>'; 
            
            if ( $skal_foldes_ud ) {
                echo '<div class="kb-besked-indhold kb-besked-kort" id="kb-besked-kort-' . $count . '">';
                echo wp_kses_post( $kort_indhold );
                echo '<br><a href="#kb-besked-fuld-' . $count . '" class="kb-toggle-link" data-target-kort="kb-besked-kort-' . $count . '" data-target-fuld="kb-besked-fuld-' . $count . '">' . __('Read more...', 'kunde-beskeder') . '</a>';
                echo '</div>';
                
                echo '<div class="kb-besked-indhold kb-besked-fuld" id="kb-besked-fuld-' . $count . '" style="display:none;">';
                echo wp_kses_post( $indhold );
                echo '<br><a href="#kb-besked-kort-' . $count . '" class="kb-toggle-link" data-target-kort="kb-besked-kort-' . $count . '" data-target-fuld="kb-besked-fuld-' . $count . '">' . __('Show less', 'kunde-beskeder') . '</a>';
                echo '</div>';
            } else {
                echo '<div class="kb-besked-indhold">';
                echo wp_kses_post( $indhold );
                echo '</div>'; 
            }
            echo '</li>';
        }
        
        echo '</ul>';
        
        echo '
        <style>
            .kb-besked-liste { list-style: none; margin-left: 0; padding-left: 0; }
            .kb-besked-item { background: #f8f8f8; border: 1px solid #e0e0e0; border-radius: 5px; margin-bottom: 20px; padding: 20px; }
            .kb-besked-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;}
            .kb-besked-header h3 { margin: 0; font-size: 1.2em; }
            .kb-besked-header .kb-besked-dato { font-size: 0.9em; color: #666; font-style: italic; }
            .kb-besked-indhold p:last-child { margin-bottom: 0; }
            .kb-toggle-link { display: inline-block; margin-top: 10px; font-weight: bold; cursor: pointer; }
        </style>
        
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                if (typeof kb_toggle_handler_sat === "undefined") {
                    kb_toggle_handler_sat = true; 
                    $(document.body).on("click", ".kb-toggle-link", function(e) {
                        e.preventDefault();
                        var targetKort = $("#" + $(this).data("target-kort"));
                        var targetFuld = $("#" + $(this).data("target-fuld"));
                        
                        targetKort.toggle();
                        targetFuld.toggle();
                    });
                }
            });
        </script>
        ';
    }
}
// Hook into the new 'messages' endpoint
add_action( 'woocommerce_account_messages_endpoint', 'kb_vis_beskeder_indhold' );


/**
 * 4.4: CORE LOGIC: Get messages matching the current user.
 */
function kb_hent_matchende_beskeder() {
    
    if ( ! is_user_logged_in() || ! function_exists('wcs_get_users_subscriptions') ) {
        return array(); 
    }

    $kunde_id = get_current_user_id();
    $matchende_beskeder = array(); 
    $bruger_statusser = array();
    $bruger_produkt_ids = array();

    $bruger_abonnementer = wcs_get_users_subscriptions( $kunde_id );
    if ( ! empty( $bruger_abonnementer ) ) {
        foreach ( $bruger_abonnementer as $abonnement ) {
            $bruger_statusser[] = 'wc-' . $abonnement->get_status();
            foreach ( $abonnement->get_items() as $item ) {
                $bruger_produkt_ids[] = $item->get_product_id();
                if ( $item->get_variation_id() > 0 ) {
                    $bruger_produkt_ids[] = $item->get_variation_id();
                }
            }
        }
    }
    $bruger_statusser = array_unique( $bruger_statusser );
    $bruger_produkt_ids = array_unique( $bruger_produkt_ids );

    
    $args = array(
        'post_type'      => 'kunde_besked',
        'post_status'    => 'publish',
        'posts_per_page' => -1, 
        'orderby'        => 'date',
        'order'          => 'DESC' 
    );
    $alle_beskeder = get_posts( $args );

    if ( empty( $alle_beskeder ) ) {
        return array(); 
    }

    
    foreach ( $alle_beskeder as $besked ) {
        $skal_vises = false; 
        $maal_type  = get_post_meta( $besked->ID, '_kb_maal_type', true );
        
        switch ( $maal_type ) {
            
            case 'kunde':
                $target_kunde_id = absint( get_post_meta( $besked->ID, '_kb_kunde_id', true ) );
                if ( $target_kunde_id > 0 && $target_kunde_id === $kunde_id ) {
                    $skal_vises = true;
                }
                break;
            
            case 'status':
                $target_statusser = get_post_meta( $besked->ID, '_kb_statusser', true );
                if ( ! empty( $target_statusser ) && is_array( $target_statusser ) ) {
                    if ( ! empty( array_intersect( $bruger_statusser, $target_statusser ) ) ) {
                        $skal_vises = true;
                    }
                }
                break;
                
            case 'produkt':
                $target_produkt_id = absint( get_post_meta( $besked->ID, '_kb_produkt_id', true ) );
                if ( $target_produkt_id > 0 ) {
                    if ( in_array( $target_produkt_id, $bruger_produkt_ids ) ) {
                        if( !empty(array_intersect(array('wc-active', 'wc-on-hold'), $bruger_statusser)) ) {
                            $skal_vises = true;
                        }
                    }
                }
                break;
        }

        if ( $skal_vises ) {
            $matchende_beskeder[] = $besked;
        }
    }
    
    return $matchende_beskeder;
}


/**
 * ========================================================================
 * EMAIL NOTIFICATIONS
 * ========================================================================
 */

/**
 * 5.1: Add "Opt-in" checkbox to "Account Details" page
 */
function kb_tilfoej_notits_felt() {
    
    $meta_value = get_user_meta( get_current_user_id(), '_kb_email_notits', true );
    $er_checked = ( $meta_value === '0' ) ? 0 : 1; // Default to 'yes' (1)
    
    woocommerce_form_field(
        '_kb_email_notits',
        array(
            'type'        => 'checkbox',
            'class'       => array('form-row-wide'),
            'label'       => __( 'Email Notifications', 'kunde-beskeder' ),
            'description' => __( 'Send me an email notification when there is a new message.', 'kunde-beskeder' ),
        ),
        $er_checked 
    );
}
add_action( 'woocommerce_edit_account_form', 'kb_tilfoej_notits_felt', 20 );

/**
 * 5.2: Save "Opt-in" field
 */
function kb_gem_notits_felt( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }
    $value = isset( $_POST['_kb_email_notits'] ) ? 1 : 0;
    update_user_meta( $user_id, '_kb_email_notits', $value );
}
add_action( 'woocommerce_save_account_details', 'kb_gem_notits_felt', 20 );


/**
 * 5.3: Check if emails should be sent when a post is saved
 */
function kb_tjek_for_ny_besked( $post_id, $post, $update, $post_before ) {
    
    if ( $post->post_type !== 'kunde_besked' ) {
        return;
    }
    
    if ( $post->post_status !== 'publish' ) {
        return;
    }
    
    if ( $update === true ) {
        if ( $post_before->post_status === 'publish' ) {
            return;
        }
    }

    // Schedule the async task
    wp_schedule_single_event( time() + 5, 'kb_async_send_notitser', array( $post_id ) );
}
add_action( 'wp_after_insert_post', 'kb_tjek_for_ny_besked', 20, 4 );


/**
 * 5.4: Main function to find recipients and send emails
 */
add_action( 'kb_async_send_notitser', 'kb_koer_notits_udsendelse' );

function kb_koer_notits_udsendelse( $post_id ) {
    
    $post = get_post( $post_id );
    if ( ! $post ) {
        return; 
    }
    
    $maal_type = get_post_meta( $post_id, '_kb_maal_type', true );
    $modtager_ids = array(); 

    // 1. FIND ALL MATCHING CUSTOMERS
    if ( $maal_type === 'kunde' ) {
        $kunde_id = absint( get_post_meta( $post_id, '_kb_kunde_id', true ) );
        if ( $kunde_id > 0 ) {
            $modtager_ids[] = $kunde_id;
        }
    } else {
        $alle_kunder = get_users( array(
            'role__in' => array( 'customer', 'subscriber' ),
            'fields'   => 'ID',
        ) );
        
        $target_statusser = ( $maal_type === 'status' ) ? get_post_meta( $post_id, '_kb_statusser', true ) : array();
        $target_produkt_id = ( $maal_type === 'produkt' ) ? absint( get_post_meta( $post_id, '_kb_produkt_id', true ) ) : 0;

        if( !is_array($target_statusser) ) $target_statusser = array();

        foreach ( $alle_kunder as $kunde_id ) {
            $bruger_data = kb_hent_bruger_data_for_tjek( $kunde_id );
            $skal_modtage = false;
            
            if ( $maal_type === 'status' ) {
                if ( ! empty( $target_statusser ) && ! empty( array_intersect( $bruger_data['statusser'], $target_statusser ) ) ) {
                    $skal_modtage = true;
                }
            } elseif ( $maal_type === 'produkt' ) {
                if ( $target_produkt_id > 0 && in_array( $target_produkt_id, $bruger_data['produkt_ids'] ) ) {
                    if( !empty(array_intersect(array('wc-active', 'wc-on-hold'), $bruger_data['statusser'])) ) {
                        $skal_modtage = true;
                    }
                }
            }
            if ( $skal_modtage ) {
                $modtager_ids[] = $kunde_id;
            }
        }
    }
    
    // 2. FILTER RECIPIENTS AND SEND EMAILS
    if ( ! empty( $modtager_ids ) ) {
        
        $emne = get_bloginfo( 'name' ) . ': ' . __('You have a new message', 'kunde-beskeder');
        $besked_titel = $post->post_title;
        $login_url = wc_get_page_permalink( 'myaccount' );
        
        foreach ( array_unique( $modtager_ids ) as $kunde_id ) {
            
            $opt_in_value = get_user_meta( $kunde_id, '_kb_email_notits', true );
            $vil_modtage = ( $opt_in_value === '0' ) ? false : true; // Default to 'yes'
            
            if ( $vil_modtage ) {
                $kunde = get_user_by( 'id', $kunde_id );
                if ( $kunde ) {
                    $modtager_email = $kunde->user_email;
                    $modtager_navn = $kunde->first_name;
                    if( empty($modtager_navn) ) $modtager_navn = $kunde->display_name;
                    
                    $email_body  = "Hi " . $modtager_navn . ",\n\n";
                    $email_body .= "You have received a new message on " . get_bloginfo( 'name' ) . ".\n\n";
                    $email_body .= "Subject: \"" . $besked_titel . "\"\n\n";
                    $email_body .= "Please log in to your 'My Account' page to read the message:\n";
                    $email_body .= $login_url . "\n\n";
                    $email_body .= "Best regards,\n";
                    $email_body .= "Spjæt";
                    
                    wp_mail( $modtager_email, $emne, $email_body );
                }
            }
        }
    }
}

/**
 * 5.5: Helper-function to get a user's subscription data
 */
function kb_hent_bruger_data_for_tjek( $kunde_id ) {
    $data = array(
        'statusser'   => array(),
        'produkt_ids' => array(),
    );
    
    if ( ! function_exists('wcs_get_users_subscriptions') ) {
        return $data;
    }
    
    $bruger_abonnementer = wcs_get_users_subscriptions( $kunde_id );
    
    if ( ! empty( $bruger_abonnementer ) ) {
        foreach ( $bruger_abonnementer as $abonnement ) {
            $data['statusser'][] = 'wc-' . $abonnement->get_status();
            foreach ( $abonnement->get_items() as $item ) {
                $data['produkt_ids'][] = $item->get_product_id();
                if ( $item->get_variation_id() > 0 ) {
                    $data['produkt_ids'][] = $item->get_variation_id();
                }
            }
        }
    }
    
    $data['statusser'] = array_unique( $data['statusser'] );
    $data['produkt_ids'] = array_unique( $data['produkt_ids'] );
    
    return $data;
}


/**
 * ========================================================================
 * PLUGIN ACTIVATION / DEACTIVATION
 * ========================================================================
 */

function kb_aktiver_plugin() {
    kb_opret_cpt();
    kb_tilfoej_endpoint();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'kb_aktiver_plugin' );

function kb_deaktiver_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'kb_deaktiver_plugin' );

?>
