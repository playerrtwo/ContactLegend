<?php
include('library/function.php');
include('smtpmail/classes/class.phpmailer.php');

$where = "nCEmaiId = '".$_SESSION['nCEmaiId']."'";
$champaign_data = getAnyData('*','campaignEmails',$where,null,null);
$nCampaignid = $champaign_data[0]['nCampaignid'];

$where = "u.nUserID = '".$_SESSION['nUserID']."' AND bStatus = 1";
$table = " users as u Left Join user_smtpdetails as sd on sd.nUserId = u.nUserId";
$login_data = getAnyData('*',$table,$where,null,null);

$fromemail = $login_data[0]['sUsername'];
$smtphost = $login_data[0]['sServerName'];
$smptpport = $login_data[0]['sPorts'];
$smtpusername = $login_data[0]['sUsername'];
$smtppassword = $login_data[0]['sPassword'];
$subject = nl2br($champaign_data[0]['sEmailSubject']);
$message = nl2br($_REQUEST['sEmailScript_']);

$where = "nCampaignid = '".$nCampaignid."'";
$customer_data = getAnyData('*','campaigncustomers',$where,null,null);
$tot_customer = count($customer_data);

for($i=0;$i<$tot_customer;$i++){

	$url = $base_url.'smtpmail/email.php?c='.$customer_data[$i]['nCampaignCustId'];
	
	$body = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'><html xmlns='http://www.w3.org/1999/xhtml'>  <head>    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />    <meta name='viewport' content='width=device-width'/>       <style type='text/css'>            /* Ink styles go here in production */          </style>    <style type='text/css'>      /* Your custom styles go here */    </style>  </head>  <body>    <table class='body'>      <tr>        <td class='center' align='center' valign='top'>          <center>            <h1>Contact Legend</h1>".$message."  <!-- Email Content -->	  <img src= '".$url."' alt='' style='border:opx;' title='' />          </center>        </td>      </tr>    </table>  </body></html>";

	$mail = new PHPMailer(); // create a new object
	$mail->IsSMTP(); // enable SMTP
	$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
	$mail->SMTPAuth = true; // authentication enabled
	$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
	$mail->Host = $smtphost;
	$mail->Port = $smptpport; // or 587
	$mail->IsHTML(true);
	$mail->Username = $smtpusername;
	$mail->Password = $smtppassword;
	$mail->SetFrom($fromemail);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$toemail = $customer_data[$i]['sCustomerEmail'];	
	$mail->AddAddress($toemail);
	$mail->Send();
}

$where = "nCEmaiId = '".$_SESSION['nCEmaiId']."'";
$data['sEmailScript'] = $message;
$return = dbRowUpdate('campaignEmails', $data, $where);

$where = "nCampaignid = '".$nCampaignid."'";
$campaigns_data['nEmailsSent'] = $tot_customer;
$campaigns_data['nEmailsOpened'] = '0';
$campaigns_data['nDraft'] = '0';
$return = dbRowUpdate('campaigns', $campaigns_data, $where);
$_SESSION['success_msg'] = 'Campaign Started Successfully';
header('location: myCampaigns.php');
exit;
?>