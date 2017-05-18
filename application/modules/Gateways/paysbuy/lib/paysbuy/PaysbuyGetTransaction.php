<?php

require_once dirname(__FILE__).'/service/PaysbuyService.php';

class PaysbuyGetTransaction extends PaysbuyService {

	const ENDPOINT_URL = 'psb_ws/getTransaction.asmx';
	const OP_GET_TR_BY_INVOICE = 'getTransactionByInvoice';
	const OP_GET_TR_DETAIL_BY_INVOICE = 'getTransactionDetailByInvoice';

	public static function getTransactionByInvoice($data = array()) {
		$reqdFields = array(
			'psbID',
			'biz',
			'secureCode',
			'invoice',
		);
		$allFields = array_merge($reqdFields, array(
		));

		$userParams = array(
			'psbID' => PaysbuyService::$psbID,
			'biz' => PaysbuyService::$username,
			'secureCode' => PaysbuyService::$secureCode
		);

		$res = parent::post(
			self::_getURL(self::OP_GET_TR_BY_INVOICE),
			parent::buildParams($userParams + $data, $allFields, $reqdFields)
		);

		$res = simplexml_load_string($res);
		$res = $res[0];
		if ($res->getTransactionByInvoiceReturn) {
			return (array) $res->getTransactionByInvoiceReturn;
		} else {
			throw new Exception("Error Processing Request");
		}
	}

	public static function getTransactionDetailByInvoice($data = array()) {
		$reqdFields = array(
			'psbID',
			'biz',
			'secureCode',
			'invoice',
		);
		$allFields = array_merge($reqdFields, array(
		));

		$userParams = array(
			'psbID' => PaysbuyService::$psbID,
			'biz' => PaysbuyService::$username,
			'secureCode' => PaysbuyService::$secureCode
		);

		$res = parent::post(
			self::_getURL(self::OP_GET_TR_DETAIL_BY_INVOICE),
			parent::buildParams($userParams + $data, $allFields, $reqdFields)
		);

		$res = simplexml_load_string($res);
		$res = $res[0];
		if ($res->getTransactionDetailByInvoiceReturn) {
			return (array) $res->getTransactionDetailByInvoiceReturn;
		} else {
			throw new Exception("Error Processing Request");
		}
	}

	private static function _getURL($operation = "") {
		return parent::getURL($operation);
	}

}