<?php

// include the library
include dirname(__FILE__) . 'paysbuy/lib/Paysbuy.php';

function paysbuy_config()
{
    $configarray = array(
        "FriendlyName" => array(
            "Type" => "System",
            "Value" => "Paysbuy"),
        "psbID" => array(
            "FriendlyName" => "PSB ID",
            "Type" => "text",
            "Size" => "40"),
        "username" => array(
            "FriendlyName" => "Paysbuy Username",
            "Type" => "text",
            "Size" => "40"),
        "securecode" => array(
            "FriendlyName" => "SecureCode",
            "Type" => "text",
            "Size" => "40"),
    );
    return $configarray;
}

function paysbuy_slink($params)
{
    $youraccount = "touchtothailand@hotmail.com";
    $psbid = "0125770723";
    $invoice = $params['invoiceid'];
    $description = $params['description'];
    $price = $params['paidAmount'];
    $postURL = "http://www.yourdomain.com/payment.php";
    $code = "<div class=\"col-sm-12 paysbuy text-center\">
                        <form name=\"myform\" id=\"myform\" method=\"post\" action=\"https://www.paysbuy.com/paynow.aspx\">
                            <input type=\"Hidden\" Name=\"psb\" value=\"$psbid\"/>
                            <input Type=\"Hidden\" Name=\"biz\" value=\"$youraccount\"/>
                            <input Type=\"Hidden\" Name=\"inv\" value=\"$invoice\"/>
                            <input Type=\"Hidden\" Name=\"itm\" value=\"$description\"/>
                            <input Type=\"Hidden\" Name=\"amt\" value=\"$price\"/>
                            <input Type=\"Hidden\" Name=\"postURL\" value=\"$postURL\"/>
                            <input type=\"image\" src=\"https://www.paysbuy.com/imgs/powerby1.jpg\" border=\"0\" name=\"submit\" alt=\"Make it easier,PaySbuy - it's fast,free and secure!\"/>
                        </form >
                    </div>
                    <div class=\"clearfix\"></div>";

    return $code;
}

function paysbuy_capture($params, $auth = "")
{
    $CI =& get_instance();
    $CI->load->helper('invoice');

    define('PSBID', $params['psbID']);
    define('USERNAME', $params['username']);
    define('SECURECODE', $params['securecode']);

    // set up Paysbuy account details
    \PaysbuyService::setup(array(
        'psbID' => '1234567890',
        'username' => 'email@mysite.com',
        'secureCode' => '3281DEAD9647CAFE4096DEADBEEF0312'
    ));

    // build the URL that can be redirected to to make the payment
    $paymentURL = \PaysbuyPaynow::authenticate(array(
        'method' => '1',
        'language' => 'E',
        'inv' => '201607211',
        'itm' => 'An item being paid for!',
        'amt' => 3,
        'curr_type' => 'TH',
        'resp_front_url' => 'http://blah.com/front.php',
        'resp_back_url' => 'http://blah.com/back.php'
    ));

// show the URL (on a real site, you would redirect to the payment URL)
    var_dump($paymentURL);

}

function paysbuy_token()
{

}

function paysbuy_charge()
{

}

function _getPostbackDetails()
{
    $p = $this->request->getPost();
    $result = $p['result'];
    return [
        'status' => substr($result, 0, 2),
        'ref' => trim(substr($result, 2)),
        'apCode' => $p['apCode'],
        'amt' => $p['amt'],
        'fee' => $p['fee'],
        'method' => $p['method']
    ];
}

function getTransactionByInvoiceCheckPost()
{
    include dirname(__FILE__) . 'paysbuy/lib/nusoap.php';
    $url = "https://www.paysbuy.com/psb_ws/getTransaction.asmx?WSDL";
    $client = new nusoap_client($url, true);

    $psbID = "";

    $biz = "";

    $secureCode = "";

    $invoice = "";

    $flag = "F";

    $params = array("psbID" => $psbID, "biz" => $biz, "secureCode" => $secureCode, "invoice" => $invoice, "flag" => $flag);

    $result = $client->call('getTransactionByInvoiceCheckPost', array('parameters' => $params), 'http://tempuri.org/', 'http://tempuri.org/getTransactionByInvoiceCheckPost', false, true);

    echo $result;

    if ($client->getError()) {
        echo "<h2>Constructor error</h2><pre>" . $client->getError() . "</pre>";
    } else {
        $result = $result["getTransactionByInvoiceCheckPostResult"];
    }

    $result_a = $result["getTransactionByInvoiceReturn"]["result"] . "<br>";
    $result_b = $result["getTransactionByInvoiceReturn"]["Invoice"] . "<br>";
    $result_c = $result["getTransactionByInvoiceReturn"]["apCode"] . "<br>";
    $result_d = $result["getTransactionByInvoiceReturn"]["amt"] . "<br>";
    $result_e = $result["getTransactionByInvoiceReturn"]["fee"] . "<br>";
    $result_f = $result["getTransactionByInvoiceReturn"]["method"] . "<br>";
    echo $result_a . $result_b . $result_c . $result_d . $result_e . $result_f;
}

function generateRandom($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function check_user_agent()
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

function check_user_os()
{
    $CI =& get_instance();
    $CI->load->library('user_agent');
    return $CI->agent->platform(); // Platform info (Windows, Linux, Mac, etc.)
}