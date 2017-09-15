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

add_action('admin_menu', 'olr_register_page');
add_action('admin_init', 'olr_ontralogin_options');
//add_action('wp_enqueue_scripts', 'olr_register_scripts');

/** --------------------
* 2.0 Shortcodes
*
---------------------- */

//2.0
add_shortcode( 'ontralogin', 'olr_ontralogin' );
add_shortcode( 'ontraresources', 'olr_resource_page' );
add_shortcode( 'ontralink', 'olr_ontralink' );
add_shortcode( 'ontraemail', 'olr_ontraemail' );



/** --------------------
* 3.0 Filters 
*
---------------------- */





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



//5.3
function olr_ontralogin( $atts ) 
{
   
   $current_user = wp_get_current_user();
   $user_email   = $current_user->user_email;

   $response = olr_getMembership($user_email);

   $accesLevel = olr_getMembershipLevel($response);

   $vaPage = olr_redirectPageLevel($accesLevel);

   if(is_user_logged_in()) {
        $output = '<style>.op-login-form-1, .moonray-form-lightbox-open-p2c1648f37 { display: none; }</style>';
    }
   if($vaPage != false) { 
     $output .= "<a href='" . $vaPage . "'> Go to Virtual Assistant Blueprint course</a>";

    }

    return $output;

}

//5.4
function olr_resource_page( $atts ) 
{
   
   $current_user = wp_get_current_user();
   $user_email   = $current_user->user_email;

   $response = olr_getMembership($user_email);

   $accesLevel = olr_getMembershipLevel($response);

   $vaPage = olr_redirectPageLevel($accesLevel);
   

   require_once( OLR_PLUGIN_DIR . 'template/affiliate_page.php');

   return $output;

}


//5.5
// Get ref link
function olr_ontralink() {
  $current_user = wp_get_current_user();
  $user_email   = $current_user->user_email;
  $id           = olr_getContactsID($user_email);
  $result   = olr_getContacts($id); 
  $aff_link = $result['data']['f1608'];
  $output   = str_replace('*****', $result['data']['id'], $aff_link );
  

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

function olr_getContactsID($email = '') {
    $appid = OLR_APP_ID;
    $key = OLR_APP_KEY;
    $args = '';
      
    
           $condition = '[{ "field":{"field":"email"},"op":"=","value":{"value": "'. $email .'"} }]';

              $args = "?condition=". $condition . "&listFields=id";
  

           
    //add API KEY AND ID
    $args .= "&Api-Appid=". $appid ."&Api-Key=" . $key;



    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/Contacts' . $args);
    //curl_setopt ($ch, CURLOPT_CAINFO, "/xampp/htdocs/ontraport/cacert.pem");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_HEADER, false);


    $output = curl_exec($ch);

    if( $output === FALSE) {
        return 'cURL Error: ' . curl_error($ch);
    }


    curl_close($ch);


    $result = json_decode($output, true);

    return $result['data'][0]['id'];
}

//6.1
function olr_getContacts( $id = '', $condition = false, $c_field = '', $c_value = '', $listFields = Null ) {

    $appid = OLR_APP_ID;
    $key = OLR_APP_KEY;
    $args = '';

    $content_type =  'application/json';

 
      
    if( $condition == true ) {
           $condition = '[{ "field":{"field":"'. $c_field . '"},"op":"=","value":{"value": "'. $c_value .'"} }]';

              $args = "?condition=". $condition;

              //If list fields is not empty
             if(!empty($listFields)) {
              $args .= "&listFields=" . (string)$listFields;
            }

           
    }
    else {

        $args .= "?id=" . $id;
            if(!empty($listFields)) {
              $args .= "&listFields=" . $listFields;
        }
    }

    //add API KEY AND ID
    $args .= "&Api-Appid=". $appid ."&Api-Key=" . $key;



    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/Contact' . $args);
    curl_setopt ($ch, CURLOPT_CAINFO, "/xampp/htdocs/ontraport/cacert.pem");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_HEADER, false);


    $output = curl_exec($ch);

    if( $output === FALSE) {
        return 'cURL Error: ' . curl_error($ch);
    }


    curl_close($ch);


    $result = json_decode($output, true);

    return $result;

}

//6.2
function olr_getMembership($username = '') {

    $appid = OLR_APP_ID;
    $key = OLR_APP_KEY;

    $content_type =  'application/json';

    $condition = '[{ "field":{"field":"username"},"op":"=","value":{"value": "'. $username .'"} }]';
    $listFields = "membership_level";

    $args = "?condition=". $condition . "&listFields=" . $listFields . "&Api-Appid=". $appid ."&Api-Key=" . $key;


    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/WordPressMemberships' . $args);
    //curl_setopt ($ch, CURLOPT_CAINFO, "/xampp/htdocs/ontraport/cacert.pem");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_HEADER, false);


    $output = curl_exec($ch);

    if( $output === FALSE) {
        return 'cURL Error: ' . curl_error($ch);
    }


    curl_close($ch);


    $result = json_decode($output, true);

    return $result;

}

function getMyReferrals($contact_id = '') {
  $appid = OLR_APP_ID;
  $key = OLR_APP_KEY;
  $content_type =  'application/json';

  $condition = '[{ "field":{"field":"freferrer"},"op":"=","value":{"value": "'. $contact_id .'"} }]';
  $listFields = "firstname,lastname,email,id,f1649,f1723,contact_cat,Date_232,JoinedBlue_174,f1634,f1770";


  $args = "?condition=". $condition . "&listFields=" . $listFields . "&Api-Appid=". $appid ."&Api-Key=" . $key;


  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/Contacts' . $args);
  //curl_setopt ($ch, CURLOPT_CAINFO, "/xampp/htdocs/ontraport/cacert.pem");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  curl_setopt($ch, CURLOPT_HEADER, false);


  $output = curl_exec($ch);

  if( $output === FALSE) {
    return 'cURL Error: ' . curl_error($ch);
  }


  curl_close($ch);


  $result = json_decode($output, true);

  return $result;



}


//6.3 
function olr_getMembershipLevel($res = '') {
    $output = '';

   for($x = 0; $x < count($res["data"]); $x++ ) {

     $output[] .= $res["data"][1]['membership_level'];

   }
   return $output;
}

//6.4 
function olr_redirectPageLevel($text) {

    if(!empty($text)) {
        foreach($text as $key => $row){
           

            if( $row === '52 Ways Promo Access' ) {


                $output = home_url() . '/virtual-assistants-blueprint';

                 return $output;
            }
            
        }
    }

}

//6.5
function olr_getReferrals($contact_id = '') {


  $res = getMyReferrals($contact_id);

  $count = 0;
  $output = array();

    if(!empty($res['data'])) {
        for($x = 0; $x < count($res["data"]); $x++ ) {

          $output[$x]['firstname']  = $res["data"][$x]['firstname'];
          $output[$x]['lastname']   = $res["data"][$x]['lastname'];
          $output[$x]['city']       = $res["data"][$x]['f1723'];
          $output[$x]['date']       = $res["data"][$x]['f1649'];
          $output[$x]['event_date'] = $res["data"][$x]['Date_232'];
          $output[$x]['attended']   = $res["data"][$x]['contact_cat'];
          $output[$x]['is_paid']   = $res["data"][$x]['f1770'];
        }
    } else {
       $output = false;
    }    
    
  return $output;
}

//6.6
// Get firstName
function olr_getFirstname($contact_id = '') {

  $result = olr_getContacts($contact_id); 

  $output = $result['data']['firstname'];
  
  
  return $output;
}
//6.6
// Get firstName
function olr_getEmail($contact_id = '') {

  $result = olr_getContacts($contact_id); 

  $output = $result['data']['email'];
  
  
  return $output;
}

//6.6
// Get firstName
function olr_getMyreferrallink($id = '') {

  $result   = olr_getContacts($id); 
  $aff_link = $result['data']['f1608'];
  $output   = str_replace('*****', $result['data']['id'], $aff_link );
  
  
  return $output;
}

//6.6
// Get my Members
function olr_getRefferedMembers( $contact_id = '') {


  $res = getMyReferrals($contact_id);

  $count = 0;
  $output = array();

    if(!empty($res['data'])) {
        for($x = 0; $x < count($res["data"]); $x++ ) {

              $id =  $res["data"][$x]['id'];

              $resx = olr_getContacts( $id, false, '','', 'firstname,lastname,BBCustomer_165,JoinedBlue_174' ); 
     
                  if ($resx['data']['BBCustomer_165'] == '802' ) {
                     $output[$x]['member_type'] = 'Platinum Member';
                     $output[$x]['referral_fee'] = '$800';
                  }
                  else if ( $resx['data']['BBCustomer_165'] == '800' ) {
                      $output[$x]['member_type'] = 'Gold Member';
                      $output[$x]['referral_fee'] = '$500';
                  }

                  if ($resx['data']['BBCustomer_165'] == '802' || $resx['data']['BBCustomer_165'] == '800' ) {
                      $output[$x]['firstname']  = $res["data"][$x]['firstname'];
                      $output[$x]['lastname']   = $res["data"][$x]['lastname'];

                      if( !empty($res["data"][$x]['JoinedBlue_174']) ) {
                        $output[$x]['joined_date'] = date('d-m-Y', $res["data"][$x]['JoinedBlue_174']);
                        $output[$x]['payment_date'] = date('d-m-Y', $res["data"][$x]['f1634']);
                      }
                }
        }
    } else {
       $output = false;
    }    
    

  // $res = getMyReferrals($contact_id);
  

  // $output .= '<h2>People your referred who joined the program</h2>';

  // if(!empty($res['data'])) {

  //     for($x = 0; $x < count($res["data"]); $x++ ) {

  //         $id =  $res["data"][$x]['id'];

  //         $resx = olr_getContacts( $id ); 

  //         if ($resx['data']['BBCustomer_165'] == '802' ) {
  //            $output .=  $resx['data']['firstname'] . " is a Platinum Member </br>";
  //         }
  //         else if ( $resx['data']['BBCustomer_165'] == '800' ) {
  //            $output .=  $resx['data']['firstname'] . " is a Gold Member </br>";
  //         }

  //     }

  // } else {
  //    $output .= "No one sign up yet";
  // }    

  
  return $output;
}


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
        # code...
        break;
    }

    return $output;
}


//6.8
// Get if attended
function olr_is_attended( $id = '') {


   $str = str_replace('*/*','', $id);
   $tags = str_split($str, 4);

  foreach($tags as $tag) {

            if( $tag == '1748' || $tag == '1749' || $tag == '1750' || $tag == '1751' || $tag == '1752' || $tag == '1753' || $tag == '1825' || $tag == '1826' || $tag == '1888' || $tag == '1890' )
            {

              $output = 'YES';

            }
            else {

              $output = 'NO';

            }

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