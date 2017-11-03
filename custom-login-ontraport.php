<?php
/*
Plugin Name: Ontraport Custom Login
Plugin URI: 
Description: Customize Login for Ontraport User.
Version: 1.0.0
Author: July Cabigas
License: GPLv2 or later
Text Domain: zero
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


if ( ! defined( 'OLR_PLUGIN_DIR' ) )     
  define( 'OLR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'OLR_PLUGIN_URL' ) )     
  define( 'OLR_PLUGIN_URL', plugins_url( '', __FILE__ ) );

if ( ! defined( 'OLR_APP_ID' ) )     
  define( 'OLR_APP_ID', wp_strip_all_tags( get_option('ontralogin_appid') ) );

if ( ! defined( 'OLR_APP_KEY' ) )     
  define( 'OLR_APP_KEY', wp_strip_all_tags( get_option('ontralogin_appkey') ) );

/*

Table of content

1. Hook
2. Shortcodes
3. Filters
4. External Scripts
5. Actions
6. Helpers
7. Custom Post Types
8. Admin Pages
9. Settings

*/


/** --------------------
* 1.0 Hook
*
---------------------- */



register_activation_hook( __FILE__, 'zeroservices_activation' );
register_deactivation_hook( __FILE__, 'zeroservices_deactivation' );


//Add Base PHP
require_once( OLR_PLUGIN_DIR . 'class/apiconnect.php');

//Declaring API KEY AND ID
$appid  = get_option( 'ontralogin_appid' );
$appkey = get_option( 'ontralogin_appkey' );

$ontraDetails = array(
    'app_id' => $appid,
    'app_key' => $appkey
);

$instance  = ontraconnect::connect($ontraDetails);
$client = $instance->getData();



add_action('admin_menu', 'olr_register_page');
add_action('admin_init', 'olr_ontralogin_options');
//add_action('wp_enqueue_scripts', 'olr_register_scripts');

/** --------------------
* 2.0 Shortcodes
*
---------------------- */

//2.0
add_shortcode( 'ontraresources', 'olr_resource_page' );
add_shortcode( 'ontralink', 'olr_ontralink' );
add_shortcode( 'ontraemail', 'olr_ontraemail' );




/** --------------------
* 4.0 External Scripts
*
---------------------- */
function olr_register_scripts() {

  wp_register_script( 'ontrastrap', plugins_url( '/assets/bootstrap.css', __FILE__ ) );
  wp_register_script( 'ontralogin-css', plugins_url( '/assets/ontralogin.css', __FILE__ ) );

  wp_enqueue_script( 'ontrastrap' );
  wp_enqueue_script( 'ontralogin-css' );
}



/** --------------------
* 5.0 Actions 
*
---------------------- */

//5.1
function olr_register_page() {
    add_options_page(
        'Settings Admin',
        'Ontralogin',
        'manage_options',
        'ontralogin-settings',
        'olr_settings_page'
    );
 
}

//5.2
function olr_settings_page()
{
    

        $appID  = get_option( 'ontralogin_appid' );
        $appKey = get_option( 'ontralogin_appkey' );
        ?>
        <div class="wrap">
            <h1>Ontraport Login</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'ontralogin-group' );
                do_settings_sections( 'ontralogin-settings' );
                submit_button();
            ?>
            </form>
        </div>
        <?php   
}
    //5.2.1
    function olr_ontralogin_options() 
    {
        register_setting( 'ontralogin-group', 'ontralogin_appid', 'sanitize_field' );
        register_setting( 'ontralogin-group', 'ontralogin_appkey', 'sanitize_field' );

          add_settings_section(
              'ontralogin_section',
              'Ontraport Api', 
              'olr_print_section_info',
              'ontralogin-settings' 
          );  

          add_settings_field(
              'ontralogin_appid', 
              'APP ID',
              'olr_appid_callback',
              'ontralogin-settings',
              'ontralogin_section'           
          );      

          add_settings_field(
              'ontralogin_appkey', 
              'APP KEY', 
              'olr_appkey_callback', 
              'ontralogin-settings', 
              'ontralogin_section'
          ); 


    }
    //5.2.2
    function olr_sanitize_field( $input ) 
    {
         $new_input = array();
         
        if( isset( $input['ontralogin_appid'] ) )
            $new_input['ontralogin_appid'] = sanitize_text_field( $input['ontralogin_appid'] );

        if( isset( $input['ontralogin_appkey'] ) )
            $new_input['ontralogin_appkey'] = sanitize_text_field( $input['ontralogin_appkey'] );

        return $new_input;     
    }
    //5.2.3
    function olr_print_section_info() 
    {
        print 'Please provide settings api from Ontraport';
    }
    //5.2.4
    function olr_appid_callback() {
      $input = get_option( 'ontralogin_appid');

      echo '<input type="text" id="ontralogin_appid" name="ontralogin_appid" value="'. (!empty($input) ? $input : '' ) .'" />';
    }
    //5.2.5
    function olr_appkey_callback() {
      $input = get_option( 'ontralogin_appkey');

      echo '<input type="text" id="ontralogin_appkey" name="ontralogin_appkey" value="'. (!empty($input) ? $input : '' ) .'" />';
      
    }





//5.4
function olr_resource_page( $atts ) 
{

  global $client;

   $current_user = wp_get_current_user();
   $user_email   = $current_user->user_email;


    $queryParams = array(
          "condition"     => 
                             '[{
                              "field":{"field":"email"},
                              "op":"=",
                              "value":{"value":"'. $user_email .'"}
                            }]',

          "listFields" => "id"                   
    );
 


  $response = $client->contact()->retrieveMultiple($queryParams);
  $response = json_decode($response, true);
  $id = (int)$response['data'][0]['id'];

  $referrals = olr_getRef($id);
  $members = olr_getJoinReff($id);

   require_once( OLR_PLUGIN_DIR . 'template/affiliate_page.php');

   return $output;

}


function olr_getRef($contact_id = '') {
  global $client;

      $queryParams = array(
          "condition"     => '[{
                              "field":{"field":"freferrer"},
                              "op":"=",
                              "value":{"value":"'. $contact_id .'"}
                            }]',

          "listFields" => "id,firstname,lastname,email,id,f1649,contact_cat,Date_232,JoinedBlue_174,f1634,f1770,BBCustomer_165,f1723,f1559"                   
    );
 


  $response = $client->contact()->retrieveMultiple($queryParams);
  $res = json_decode($response, true);


  $count = 0;
  $output = array();

    if(!empty($res['data'])) {
        for($x = 0; $x < count($res["data"]); $x++ ) {

          $output[$x]['firstname']  = $res["data"][$x]['firstname'];
          $output[$x]['lastname']   = $res["data"][$x]['lastname'];
          $output[$x]['city']       = $res["data"][$x]['f1723'];
          $output[$x]['date']       = $res["data"][$x]['f1649'];
          $output[$x]['event_date'] = $res["data"][$x]['Date_232'];
          $output[$x]['attended']   = $res["data"][$x]['f1559'];
    

        }

    } else {
       $output = false;
    }    
    


  return $output;


}





//5.5
// Get ref link
function olr_ontralink() {
  global $client;


  $current_user = wp_get_current_user();
  $user_email   = $current_user->user_email;



    $queryParams = array(
          "condition"     => "[{
                                 \"field\":{\"field\":\"email\"},
                                 \"op\":\"=\",
                                 \"value\":{\"value\":\"$user_email\"}
                             }]",

          "listFields" => "f1608, id"                   
    );
 


  $response = $client->contact()->retrieveMultiple($queryParams);
  $response = json_decode($response, true);

  $aff_link = $response['data'][0]['f1608'];

   $affiliate_link  = str_replace('*****', $response['data'][0]['id'], $aff_link );


  return $affiliate_link;
}


// Get my Members
function olr_getJoinReff( $contact_id = '') {
  global $client;

      $queryParams = array(
          "condition"     => '[{
                              "field":{"field":"freferrer"},
                              "op":"=",
                              "value":{"value":"'. $contact_id .'"}
                               }]',

          "listFields" => "id,firstname,lastname,f1770,BBCustomer_165,f1634,JoinedBlue_174"                   
    );
 


  $response = $client->contact()->retrieveMultiple($queryParams);
  $resx = json_decode($response, true);


  $count = 0;
  $output = array();

    if(!empty($resx['data'])) {
        for($x = 0; $x < count($resx["data"]); $x++ ) {

            if($resx['data'][$x]['BBCustomer_165'] == "800" || $resx['data'][$x]['BBCustomer_165'] == "802" ) {

                  $output[$x]['is_paid']   = $resx["data"][$x]['f1770'];
                  $output[$x]['firstname']  = $resx["data"][$x]['firstname'];
                  $output[$x]['lastname']   = $resx["data"][$x]['lastname'];

               
                        if ($resx['data'][$x]['BBCustomer_165'] == '802' ) {
                           $output[$x]['member_type'] = 'Platinum Member';
                           $output[$x]['referral_fee'] = '$800';
                        }
                        else if ( $resx['data'][$x]['BBCustomer_165'] == '800' ) {
                            $output[$x]['member_type'] = 'Gold Member';
                            $output[$x]['referral_fee'] = '$500';

                        }

                        if( $resx["data"][$x]['JoinedBlue_174'] ) {
                          $output[$x]['joined_date'] = date('d-m-Y', $resx["data"][$x]['JoinedBlue_174']);
                        }
                        if( $resx["data"][$x]['f1634'] ) {
                          $output[$x]['payment_date'] = date('d-m-Y', $resx["data"][$x]['f1634']);
                        }

              }
        }

    } else {
       $output = false;
    }    
    


  return $output;


  }



//5.6
// Get email
function olr_ontraemail() {
  $current_user = wp_get_current_user();
  $user_email   = $current_user->user_email;

  return  $user_email;
}


/** --------------------
* 6.0 Helper
*
---------------------- */

  

//6.7
// Get Nearest City
function olr_getCity( $id = '') {

    switch ($id) {
      case '1672':
        $output = 'Sydney';
        break;
      case '1673':
        $output = 'Perth';
        break;
      case '1674':
        $output = 'Parramata';
        break;
      case '1675':
        $output = 'Newcastle';
        break;
      case '1676':
        $output = 'Melbourne';
        break;
      case '1677':
        $output = 'Auckland';
        break;
      case '1678':
        $output = 'Adelaide';
        break;
      case '1692':
        $output = 'Brisbane';
        break;

      default:
 
        break;
    }

    return $output;
}


//6.8
// Get if attended
function olr_is_attended( $id = '') {


    if($id >= 1) {
      $output = 'YES';
    }
    else {
      $output = 'NO';
    }


    return $output;
}

//6.9
// Is Paid
function olr_is_paid( $value = '') {
   if($value == true) {
      $output = 'YES';
   }
   else {
      $output = 'NO';
   }
   return $output;
}