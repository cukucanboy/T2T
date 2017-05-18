<?php

require_once(dirname(__FILE__) . '/omise/lib/Omise.php');

function __construct()
{
    //get the CI instance
    $this->CI = &get_instance();


}

function omise_config()
{
    $configarray = array(
        "FriendlyName" => array(
            "Type" => "System",
            "Value" => "Omise Payment Gateway"),
        "publickey" => array(
            "FriendlyName" => "PUBLIC KEY",
            "Type" => "text",
            "Size" => "40"),
        "secretkey" => array(
            "FriendlyName" => "SECRET KEY",
            "Type" => "text",
            "Size" => "40"),
        "apiversion" => array(
            "FriendlyName" => "API Version",
            "Type" => "text",
            "Size" => "40"),
    );
    return $configarray;
}

function omise_capture($params, $auth = "")
{
    $CI =& get_instance();
    $CI->load->helper('invoice');

    define('OMISE_API_VERSION', $params['apiversion']);
    define('OMISE_PUBLIC_KEY', $params['publickey']);
    define('OMISE_SECRET_KEY', $params['secretkey']);

    $paymentvars = array();
    $paymentvars['payment_gateway'] = $params['name'];
    $paymentvars['invoiceid'] = $params['invoiceid'];
    $paymentvars['invoiceref'] = $params['invoiceref'];
    $paymentvars['currency'] = $params['currency'];
    $paymentvars['cardnum'] = $params['cardnum'];
    $paymentvars['expMonth'] = $params['expMonth'];
    $paymentvars['expYear'] = $params['expYear'];
    $paymentvars['fullname'] = $params['firstname'] . ' ' . $params['lastname'];
    $paymentvars['os'] = check_os();
    $paymentvars['browser'] = check_agent();
    $paymentvars['ip'] = $CI->input->ip_address();

    //echo "Creating Token...<br />";
    $refcode = generateRandomString();

    $token = create_token($params); // Request token

    if($token['status'] == "tokenfail"){ // if input card number incorrect. return error and save detail to db
        paymentLog('error', 'error', $token['location'], "Card Error", "Input incorrect card number.", $refcode, $params['firstname'] . ' ' . $params['lastname'], $params['invoiceid'], strtotime(date("Y-m-d H:i:s")), NULL, NULL, $paymentvars['os'], $paymentvars['browser'], $paymentvars['ip'], $paymentvars['payment_gateway']);
        return array("status" => "Error", "rawdata" => $token);
    }

    $tokenkey = $token['id'];
    $amount = $params['amount'] * 100; // convert to satang thai.

    $paymentvars['amount'] = $amount;
    $paymentvars['amountPay'] = $params['amount'];

    $charge = charge($tokenkey, $amount);

    if ($charge['status'] == 'failed') { // if charge failed return error and save detail to db
    paymentLog('error', $charge['object'], $charge['location'], $charge['failure_code'], $charge['failure_message'], $refcode, $params['firstname'] . ' ' . $params['lastname'], $params['invoiceid'], strtotime(date("Y-m-d H:i:s")), strtotime($token['created']), strtotime($charge['created']), $paymentvars['os'], $paymentvars['browser'], $paymentvars['ip'], $paymentvars['payment_gateway']);
        return array("status" => "failed", "rawdata" => $charge);
    }

    //$transactionid = $charge['transaction'];
    //$created = strtotime($charge['created']);
    //$status = $charge['status'];
    $paymentvars['brand'] = $charge['card'][1]['brand'];
    $paymentvars['ref_transaction'] = $refcode;
    $paymentvars['datetime'] = strtotime(date('Y-m-d H:i:s'));
    $paymentvars['transaction'] = $charge['transaction'];
    $paymentvars['result'] = $charge['status'];
    $paymentvars['object'] = $charge['object'];

    paymentLog($charge['status'], $charge['object'], $charge['location'], $charge['code'], $charge['message'], $refcode, $params['firstname'] . ' ' . $params['lastname'], $params['invoiceid'], strtotime(date("Y-m-d H:i:s")), strtotime($token['created']), strtotime($charge['created']), $paymentvars['os'], $paymentvars['browser'], $paymentvars['ip'], $paymentvars['payment_gateway']);

    $status = $charge['status'];

    if ($status == "successful") {
        return array("status" => "successful", "transid" => $charge['transaction'], "rawdata" => $charge);
    }

    return array("status" => "declined", "rawdata" => $charge);

}

function create_token($params)
{
    //$ci =& get_instance();
    try {
        $token = OmiseToken::create(array(
            'card' => array(
                'name' => $params['firstname'] . ' ' . $params['lastname'],
                'number' => $params['cardnum'],
                'expiration_month' => $params['expMonth'],
                'expiration_year' => $params['expYear'],
                'city' => ' ',
                'postal_code' => ' ',
                'security_code' => $params['cccvv']
            )
        ));
        return $token;
    } catch (Exception $e) {
        return array("status" => "tokenfail", "message" => "cannot requires process. please check you card.");
    }
}

function charge($tokenkey, $amount)
{
  try{
    $charge = OmiseCharge::create(array(
        'amount' => $amount, // it mean 400.00
        'currency' => 'thb',
        'card' => $tokenkey
    ));
    return $charge;
  } catch (Exception $e) {
      return array("status" => "tokenfail", "message" => "cannot requires process. please contact administrator.");
  }
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function check_agent()
{
    $CI =& get_instance();
    $CI->load->library('user_agent');

    if ($CI->agent->is_browser()) {
        $agent = $CI->agent->browser() . ' ' . $CI->agent->version();
    } elseif ($CI->agent->is_robot()) {
        $agent = $CI->agent->robot();
    } elseif ($CI->agent->is_mobile()) {
        $agent = $CI->agent->mobile();
    } else {
        $agent = 'Unidentified User Agent';
    }

    return $agent;

}

function check_os()
{
    $CI =& get_instance();
    $CI->load->library('user_agent');
    return $CI->agent->platform(); // Platform info (Windows, Linux, Mac, etc.)
}
