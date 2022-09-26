<?php
/**
 * UniPaymentClientTest
 */

namespace UniPayment\Client;

require_once(__DIR__ . '/../vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use UniPayment\Client\Model\CreateInvoiceRequest;
use UniPayment\Client\Model\QueryInvoiceRequest;

/**
 * UniPaymentClientTest Class
 */
class UniPaymentClientTest extends TestCase
{
    private $uniPaymentClient;

    private $clientId='a91b5d49-ddad-4efc-8e73-7d42d8eb8c08';
    private $clientSecret='7acpzHzWe2GaAnCy2q6fwnugaN69Bc9Ku';
    private $appId='63de6f86-4d58-4403-afca-ffbaa787e227';
    private $isSandbox = true;
    private $invoiceId = 'PnC87CWRCsvPheYnHBuB4p';
    private $notify = '{"ipn_type":"invoice","event":"invoice_created","app_id":"63de6f86-4d58-4403-afca-ffbaa787e227","invoice_id":"PnC87CWRCsvPheYnHBuB4p","order_id":"6330ebcab3607","price_amount":100.0,"price_currency":"USD","network":null,"address":null,"pay_currency":"USDT","pay_amount":0.0,"exchange_rate":0.0,"paid_amount":0.0,"confirmed_amount":0.0,"refunded_price_amount":0.0,"create_time":"2022-09-26T00:01:15.4893061Z","expiration_time":"2022-09-26T00:31:15.4893749Z","status":"New","error_status":"None","ext_args":null,"transactions":null,"notify_id":"19f31b22-83f4-4f98-9956-ed8ecb0fe93a","notify_time":"0001-01-01T00:00:00"}';

    /**
     * Setup before running any test cases
     */
    public static function setUpBeforeClass(): void
    {
    }

    /**
     * Setup before running each test case
     */
    public function setUp(): void
    {
        $this->uniPaymentClient = new UniPaymentClient();

        $this->uniPaymentClient->getConfig()->setClientId($this->clientId);
        $this->uniPaymentClient->getConfig()->setClientSecret($this->clientSecret);
        $this->uniPaymentClient->getConfig()->setIsSandbox($this->isSandbox);
        $this->uniPaymentClient->getConfig()->setDebug(true);
    }

    /**
     * Clean up after running each test case
     */
    public function tearDown(): void
    {
    }

    /**
     * Clean up after running all test cases
     */
    public static function tearDownAfterClass(): void
    {
    }

    /**
     * Test case for createInvoice
     */
    public function testCreateInvoice()
    {
        $createInvoiceRequest = new CreateInvoiceRequest();
        $createInvoiceRequest->setAppId($this->appId);
        $createInvoiceRequest->setPriceAmount("100");
        $createInvoiceRequest->setPriceCurrency("USD");
        $createInvoiceRequest->setPayCurrency("USDT");
        $createInvoiceRequest->setOrderId(uniqid());
        $createInvoiceRequest->setConfirmSpeed("low");
        $createInvoiceRequest->setRedirectUrl("https://google.com");
        $createInvoiceRequest->setNotifyUrl("https://google.com");
        $createInvoiceRequest->setTitle("Test Invoice");
        $createInvoiceRequest->setDescription("Test Desc");
        $createInvoiceRequest->setLang("en-US");
        $response = $this->uniPaymentClient->createInvoice($createInvoiceRequest);
        $this->assertEquals('OK', $response->getCode());
        $this->assertNotNull($response->getData()->getInvoiceUrl());
    }

    /**
     * Test case for queryInvoices
     * @throws ApiException
     */
    public function testQueryInvoices()
    {
        $queryInvoiceRequest = new QueryInvoiceRequest();
        $queryInvoiceRequest->setOrderId("ORDER_123456");

        $response = $this->uniPaymentClient->queryInvoices($queryInvoiceRequest);
        $this->assertEquals('OK', $response->getCode());
    }

    /**
     * Test case for getInvoiceById
     * @throws ApiException
     */
    public function testGetInvoiceById()
    {
        $response = $this->uniPaymentClient->getInvoiceById($this->invoiceId);
        $this->assertEquals('OK', $response->getCode());
    }

    /**
     * Test case for getInvoiceById
     * @throws ApiException
     */
    public function testQueryIps()
    {
        $response = $this->uniPaymentClient->queryIps();
        $this->assertEquals('OK', $response->getCode());
    }

    /**
     * Test case for getCurrencies
     * @throws ApiException
     */
    public function testGetCurrencies()
    {
        $response = $this->uniPaymentClient->getCurrencies();
        $this->assertEquals('OK', $response->getCode());
    }

    /**
     * Test case for getExchangeRateByFiatCurrency
     * @throws ApiException
     */
    public function testGetExchangeRateByFiatCurrency()
    {
        $response = $this->uniPaymentClient->getExchangeRateByFiatCurrency('USD');
        $this->assertEquals('OK', $response->getCode());
    }

    /**
     * Test case for getExchangeRateByCurrencyPair
     * @throws ApiException
     */
    public function testGetExchangeRateByCurrencyPair()
    {
        $response = $this->uniPaymentClient->getExchangeRateByCurrencyPair('USD', 'BTC');
        $this->assertEquals('OK', $response->getCode());
    }

    /**
     * Test case for checkIpn
     * @throws ApiException
     */
    public function testcheckIpn()
    {
        $response = $this->uniPaymentClient->checkIpn($this->notify);
        $this->assertEquals('OK', $response->getCode());
    }
}
