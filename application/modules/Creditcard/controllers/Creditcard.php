<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Creditcard extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        if (empty($_POST)) {
            exit;
        }

    }


    function index()
    {
        $settings = $this->Settings_model->get_settings_data();
        $this->load->helper('invoice');
        $this->load->model('Admin/Payments_model');

        $gateway = $this->input->post('paymethod');
        $bookid = $this->input->post('bookingid');
        $ref = $this->input->post('refno');
        $member = $this->input->post('member');
        $invoicdata = invoiceDetails($bookid, $ref, $member);

        require_once "./application/modules/Gateways/" . $gateway . ".php";
        $params = $this->Payments_model->getGatewayParams($gateway);

        $params['charset'] = "UTF-8";
        $params['invoiceid'] = $invoicdata->id;
        $params['userid'] = $invoicdata->bookingUser;
        $params['firstname'] = $this->input->post('firstname');
        $params['lastname'] = $this->input->post('lastname');
        $params['email'] = $invoicdata->accountEmail;
        $params['companyname'] = $settings[0]->site_title;
        $params['invoiceref'] = $invoicdata->code;
        //$params['amount'] = 10;
        $params['amount'] = $invoicdata->checkoutTotal;
        $params['currency'] = $invoicdata->currCode;
        $params['cccvv'] = $this->input->post('cvv');
        $params['cardexp'] = $this->input->post('expMonth') . "-" . $this->input->post('expYear');
        $params['cardnum'] = $this->input->post('cardnum');
        //for stripe expiry year and month
        $params['expMonth'] = $this->input->post('expMonth');
        $params['expYear'] = $this->input->post('expYear');


        if (function_exists($gateway . "_capture")) {
            $msg = call_user_func($gateway . "_capture", $params);

            if ($msg['status'] == 'Error'){
                foreach ($msg as $keys => $vals){
                    $token_status = $vals['status'];
                    $token_msg = $vals['message'];
                }
                $this->session->set_flashdata('invoiceerror', $token_msg);
                redirect("invoice?id=" . $invoicdata->id . "&sessid=" . $invoicdata->code . "&member=" . $invoicdata->member);
            }

            //die();

            foreach ($msg as $key => $val) {
                $object = $val['object'];
                $result = $val['result'];
                $status = $val['status'];
                $failure_code = $val['failure_code'];
                $failure_msg = $val['failure_message'];
                $transaction = $val['transaction'];
                $payment_gateway = $val['payment_gateway'];
                $fullname = $val['fullname'];
                $amount = $val['amountPay'];
                $currency = $val['currency'];
                $invoiceid = $val['invoiceid'];
                $invoiceref = $val['invoiceref'];
                $card = $val['cardnum'];
                $expiration_month = $val['expMonth'];
                $expiration_year = $val['expYear'];
                $brand = $val['brand'];
                $os = $val['os'];
                $browser = $val['browser'];
                $ip = $val['ip'];
                $datetime = $val['datetime'];
                $ref_transaction = $val['ref_transaction'];
            }

        }
        if ($status == "successful") { // success result.
            //echo "success";

            if ($invoicdata->checkoutAmount > 0) {
                $paidAmount = $invoicdata->checkoutAmount;
            } else {
                $paidAmount = $invoicdata->paidAmount;
            }


            //Update Pay Status   Call Func. on File   application/helpers/invoice_helper.php
            updateInvoiceStatus($invoicdata->id, $paidAmount, $msg['transaction'], $gateway, "paid", $invoicdata->module, $invoicdata->checkoutTotal);
            //End  Update Pay Status   Call Func. on File   application/helpers/invoice_helper.php

            paymentProcess($object, $result, $transaction, $payment_gateway, $fullname, $amount, $currency, $invoiceid, $invoiceref, $card, $expiration_month, $expiration_year, $brand, $os, $browser, $ip, $datetime, $ref_transaction);

            $this->load->model('Admin/Emails_model');

            $this->Emails_model->paid_sendEmail_customer($invoicdata);
            $this->Emails_model->paid_sendEmail_admin($invoicdata);
            $this->Emails_model->paid_sendEmail_supplier($invoicdata);
            $this->Emails_model->paid_sendEmail_owner($invoicdata);


        } else {
            $this->session->set_flashdata('invoiceerror', $failure_msg);

        }
        redirect("invoice?id=" . $invoicdata->id . "&sessid=" . $invoicdata->code . "&member=" . $invoicdata->member);

    }


}
