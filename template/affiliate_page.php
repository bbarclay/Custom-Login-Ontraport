<?php

   $current_user = wp_get_current_user();
   $user_email   = $current_user->user_email;
   $id 			 = 45862;
   $referral_link       = olr_getMyreferrallink($id);
  $referrals            = olr_getReferrals($id);
  $getRefferedMembers  = olr_getRefferedMembers($id);


$output .= '<h2>People registered on your link</h2>';


if($referrals != false) {

	$data .= '<table class="table-view">';
	$data .= '<tr><th>#</th><th>First Name</th><th>Last Name</th><th>City</th><th>Date</th><th>Attended</th></tr>';

	$count = 1;
	foreach( $referrals as $row) {

		if( !empty($row['event_date'])) {
			$date =  date('d-m-Y', $row['event_date']);
		}
		else {

			$date = '';
		}

	    $data .= '<tr>';
	      $data .= '<td>' . $count . '</td><td>'. $row['firstname'] . '</td><td>'. $row['lastname'] . '</td><td>'. olr_getCity( $row['city'] )  . '</td><td>'. $date . '</td><td>' . olr_is_attended( $row['attended'] ) . '</td>';
	    $data .= '</tr>';


	    $count++;
	}


	$data .= '</table>';
}


if($getRefferedMembers != false) {

	$data2 .= '<table class="table-view">';
	$data2 .= '<tr><th>#</th><th>First Name</th><th>Last Name</th><th>Membership Level</th><th>Referral Fee</th><th>Start Date</th><th>Payment Due</th><th>Paid</th></tr>';

	$count = 1;
	foreach( $getRefferedMembers as $row) {

	    $data2 .= '<tr>';
	      $data2 .= '<td>' . $count . '</td><td>'. $row['firstname'] . '</td><td>'. $row['lastname'] . '</td><td>'.  $row['member_type']  . '</td><td>'.  $row['referral_fee'] . '</td><td>' .  $row['joined_date'] . '</td><td>'.   $row['payment_date'] .'</td><td>'.   olr_is_paid( $row['payment_date'] ) .'</td>';
	    $data2 .= '</tr>';


	    $count++;
	}


	$data2 .= '</table>';
}


$output .= $data;
$output .= '<h2>People you referred who joined the program</h2>';
$output .= $data2;

