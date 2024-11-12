<?php
/**
 * Sunpay Invoice
 * Sunpay電子發票SDK
 * author Ren
 */

declare(strict_types=1);

namespace J7\R2SunpayInvoice\ApiHandler;

// 禁用該文件的SnakeCase檢查
// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase


if (! class_exists( 'J7\R2SunpayInvoice\ApiHandler\SunpayInvoiceSDK' ) ) {
	/**
	 * Class SunpayInvoice
	 */
	final class SunpayInvoiceSDK {
		/** @var array<string,mixed> $Send 要送出的資料 */
		public $send = [];

		/**所需參數以及定義*/
		/** @var string $CompanyID 統一編號 */
		public $CompanyID = '';
		/** @var string $merchantID 商店代號 */
		public $merchantID ='';
		/** @var string $HashKey 金鑰 */
		public $HashKey ='';
		/** @var string $HashIV 向量 */
		public $HashIV ='';
		/** @var string $api_url API網址 B2C 與 B2B網址不一樣*/
		public $api_url ='';
		/** @var string $orderNo 商店自訂訂單編號 */
		public $orderNo ='';
		/** @var string $buyerIdentifier 買受人統一編號，純數字。 */
		public $buyerIdentifier ='';
		/** @var string $buyerName 買受人名稱 */
		public $buyerName ='';
		/** @var string $buyerEmailAddress 買受人電子信箱
		 * 2. carrierType=3 時，此參數必填。
		 * 3. carrierType=0, 1, 2 時，此參數非必填。
		 */
		public $buyerEmailAddress ='';
		/** @var Integer $IsSendMessage 發票簡訊通知
		 * 【0 = 不需寄送簡訊通知】
		 * 【1 = 寄送發票簡訊通知】
		 */
		public $IsSendMessage = 0;
		/** @var string $buyerTelephoneNumber 買受人電話
		 * 2. IsSendMessage=0 時，此參數非必填。
		 * 3. IsSendMessage=1 時，此參數必填。
		 */
		public $buyerTelephoneNumber ='';
		/** @var Integer $IsSendPaper 紅陽代印發票
		 * 2. carrierType=0 時，
		 * 【0 = 不需代印】
		 * 【1 = 由紅陽代印發票】
		 * 3. carrierType=0 且donateMark=1 時，
		 * 則此欄為【0 = 不需代印】。
		 * 4. carrierType=1, 2, 3 時，則此欄為
		 * 【0 = 不需代印】。
		 */
		public $IsSendPaper = 0;
		/** @var string $buyerAddress 買受人地址
		 * 2. IsSendPaper=0 時，此參數非必填。
		 * 3. IsSendPaper=1 時，此參數必填。
		 */
		public $buyerAddress ='';
		/** @var Integer $invoiceType 發票類別 */
		public $invoiceType = 7;
		/** @var Integer $donateMark 捐贈 */
		public $donateMark = 0;
		/** @var string $poban 捐贈碼
		 * 1.當donateMark=0 時，此參數不需傳
		 * 2.當donateMark=1 時，此參數必填，限3~7 碼純數字。*/
		public $poban ='';
		/** @var Integer $carrierType 載具類型
		 * 【0 = 無載具】
		 * 【1 = 手機條碼載具】
		 * 【2 = 自然人憑證條碼載具】
		 * 【3 = 紅陽會員載具】
		 */
		public $carrierType = 0;
		/** @var string $carrierId1 載具編號
		 * 1. carrierType=0, 3 時，則此參數不需傳值。
		 * 2. carrierType=1 時，需驗證手機條碼的載具號碼格式：第1 碼必為【/】， 後7碼為【0~9】【A~Z】【+】【-】【.】，英文限大寫，共8碼所組成。
		 * 3. carrierType=2 時，需驗證自然人憑證條碼的載具號碼格式：第1、2 碼必為【A~Z】， 後14碼為【0~9】，英文限大寫，共16碼所組成。
		 */
		public $carrierId1 ='';
		/** @var Integer $taxType 課稅別
		 * 【1 = 應稅】
		 * 【2 = 零稅率】
		 * 【3 = 免稅】
		 * 【4 = 應稅(特種)】
		 * 【9 = 混合應稅與免稅或零稅率】
		 */
		public $taxType = 1;
		/** @var Decimal $taxRate 稅率
		 * 1. taxType=1 時，稅率請帶【0.05】。
		 * 2. taxType=2, 3 時，稅率請帶【0】。
		 * 3. taxType=4 時，請帶入規定之稅率，例如：規定之稅率為18%，稅率請帶入【0.18】。
		 * 4. taxType=9 時，此參數不需傳值。
		 */
		public $taxRate = 0.05;
		/** @var Decimal $taxAmount 稅額 純數字，為發票稅額。*/
		public $taxAmount = 0;
		/** @var Decimal $salesAmount 應稅銷售額 純數字，為發票銷售額(未稅)。*/
		public $salesAmount = 0;
		/** @var Decimal $zeroTaxSalesAmount 零稅率銷售額 純數字，純數字，為零稅率之銷售額。*/
		public $zeroTaxSalesAmount = 0;
		/** @var Decimal $freeTaxSalesAmount 免稅銷售額 純數字，為免稅之銷售額。*/
		public $freeTaxSalesAmount = 0;
		/** @var Decimal $totalAmount 發票總金額 純數字，為發票總金額(含稅)。*/
		public $totalAmount = 0;
		/** @var string $mem  發票備註，字數限200 字，如有難字則再縮短。*/
		public $mem ='';
		/** @var Integer $customsClearanceMark 通關方式註記
		 * 1. taxType=2 時，此參數必填，需傳海關報關出口方式：
		 * 【1 = 非經海關出口】
		 * 【2 = 經海關出口】
		 * 2. taxType=1, 3, 4 時，此參數請填【0】。
		 * 3. taxType=9 時，商品明細若有包含零稅率，此參數必填。
		 */
		public $customsClearanceMark = 0;
		/** @var Integer $isprint 紙本發票列印狀態
		 * 【0 = 未列印】
		 * 【1 = 列印】
		 */
		public $isprint = 0;
		/**
		 * @var array{
		 * description: string ,
		 * quantity: int,
		 * unit: string,
		 * unitPrice: float,
		 * amount: float,
		 * remark: string,
		 * taxType: int
		 * } $productItems 發票明細
		 * description 商品名稱
		 * quantity 數量
		 */
		public $productItems = [
			'description' =>'',
			'quantity'    =>0,
			'unit'        =>'0',
			'unitPrice'   =>0,
			'amount'      =>0,
			'remark'      =>'',
			'taxType'     =>1,
		];
		/** @var String $Token API交易檢查碼*/
		public $Token = '';
		/**
		 * Construct
		 */
		public function __construct() {
			// Default Value
			$this->send =[
				'orderNo'              =>$this->orderNo,
				'buyerIdentifier'      =>$this->buyerIdentifier,
				'buyerName'            =>$this->buyerName,
				'buyerEmailAddress'    =>$this->buyerEmailAddress,
				'IsSendMessage'        =>$this->IsSendMessage,
				'buyerTelephoneNumber' =>$this->buyerTelephoneNumber,
				'IsSendPaper'          =>$this->IsSendPaper,
				'buyerAddress'         =>$this->buyerAddress,
				'invoiceType'          =>$this->invoiceType,
				'donateMark'           =>$this->donateMark,
				'poban'                =>$this->poban,
				'carrierType'          =>$this->carrierType,
				'carrierId1'           =>$this->carrierId1,
				'taxType'              =>$this->taxType,
				'taxRate'              =>$this->taxRate,
				'taxAmount'            =>$this->taxAmount,
				'salesAmount'          =>$this->salesAmount,
				'zeroTaxSalesAmount'   =>$this->zeroTaxSalesAmount,
				'freeTaxSalesAmount'   =>$this->freeTaxSalesAmount,
				'totalAmount'          =>$this->totalAmount,
				'mem'                  =>$this->mem,
				'customsClearanceMark' =>$this->customsClearanceMark,
				'isprint'              =>$this->isprint,
				'productItems'         =>$this->productItems,
			];
		}
		/**
		 * 送出發票function
		 *
		 * @return array{
		 * status:string,
		 * message:string,
		 * result:array{
		 * merchantID:string,
		 * tradeNumber:string,
		 * orderNo:string,
		 * totalAmount:Integer,
		 * invoiceNumber:string,
		 * randomNumber:string,
		 * CRT_DAT:DateTime,
		 * barcode:string,
		 * leftQrCode:string,
		 * rightQrCode:string
		 * }
		 * } 發票回傳資訊
		 */
		public function invoice_send() {
			$time_stamp               = time()+( 8*3600 );// 台灣時區
			$data                     = [
				'CompanyID' => $this->CompanyID,
				'TimeStamp' => (string) $time_stamp,
			];
			$this->send['Token']      = $this->aes_encrypt( json_encode( $data ), $this->HashKey, $this->HashIV );
			$this->send['MerchantID'] = $this->merchantID;
			$response                 = $this->send();
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				return "Something went wrong: $error_message";
			} else {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				// 處理 $data 變量
				return $data;
			}
		}

		/**
		 * 作廢發票function
		 *
		 * @return array{
		 * status:string,
		 * message:string,
		 * result:array{
		 * merchantID:string,
		 * invoiceNumber":string,
		 * cancelDateTime":string,
		 * }
		 * } 發票回傳資訊
		 */
		public function invoice_invalid() {
			$time_stamp               = time()+( 8*3600 );// 台灣時區
			$data                     = [
				'CompanyID' => $this->CompanyID,
				'TimeStamp' => (string) $time_stamp,
			];
			$this->send['Token']      = $this->aes_encrypt( json_encode( $data ), $this->HashKey, $this->HashIV );
			$this->send['MerchantID'] = $this->merchantID;
			$response                 = $this->send();
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				return "Something went wrong: $error_message";
			} else {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				// 處理 $data 變量
				return $data;
			}
		}

		/**
		 * 取得發票列表function
		 *
		 * @return array{
		 * status:string,
		 * message:string,
		 * result:array{
		 * merchantID:string,
		 * orderNo:string,
		 * b2B:string,
		 * buyerIdentifier:string,
		 * buyerName:string,
		 * buyerEmailAddress:string,
		 * buyerTelephoneNumber:string,
		 * buyerAddress:string,
		 * invoiceType:Integer,
		 * orintFlag:string,
		 * isPrint:Integer,
		 * printCount:Integer,
		 * donateMark:Integer,
		 * poban:string,
		 * carrierType:Integer,
		 * carrierId1:string,
		 * taxType:Integer,
		 * taxRate:Decimal,
		 * taxAmount:Decimal,
		 * salesAmount:Decimal,
		 * zeroTaxSalesAmount:Decimal,
		 * freeTaxSalesAmount:Decimal,
		 * totalAmount:Decimal,
		 * productItems:productItems[],
		 * hasAllowance:Boolean,
		 * allowanceBalance:Integer,
		 * flagA:String,
		 * flagCancel:Integer,
		 * invoiceNumber:string,
		 * randomNumber:string,
		 * CRT_DAT:DateTime,
		 * barcode:string,
		 * leftQrCode:string,
		 * rightQrCode:string
		 * }}
		 */
		public function invoice_list() {
			$time_stamp               = time()+( 8*3600 );// 台灣時區
			$data                     = [
				'CompanyID' => $this->CompanyID,
				'TimeStamp' => (string) $time_stamp,
			];
			$this->send['Token']      = $this->aes_encrypt( json_encode( $data ), $this->HashKey, $this->HashIV );
			$this->send['merchantID'] = $this->merchantID;
			ob_start();
			var_dump($this->send);
			\J7\WpUtils\Classes\log::info('' . ob_get_clean());
			$response                 = $this->send();
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				return "Something went wrong: $error_message";
			} else {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				// 處理 $data 變量
				return $data;
			}
		}
		/**
		 * Send Function
		 */
		public function send() {
			return wp_remote_post(
				$this->api_url,
				[
					'body' => json_encode(
					$this->send
					),
					'headers' => [
						'Content-Type' => 'application/json; charset=utf-8',
						'Accept'       => 'application/json',
					],
				]
				);
		}
		/**
		 * Token加密
		 *
		 * @param string $data 要加密的資料 Ex:{"CompanyID":"12345678","TimeStamp":"12345678"}
		 * @param string $key 金鑰
		 * @param string $iv 向量
		 * @return string
		 */
		public function aes_encrypt( $data, $key, $iv ) {
			$cipher    = 'AES-128-CBC';
			$options   = OPENSSL_RAW_DATA;
			$encrypted = openssl_encrypt(URLEncode($data), $cipher, $key, $options, $iv);

			return base64_encode($encrypted);
		}
	}
}
