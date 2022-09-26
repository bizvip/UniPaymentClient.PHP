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

    private $clientId='74feb539-ba5a-4ae9-b901-4da4fb539574';
    private $clientSecret='BsoRhgqzhR1TYMtwTRYdPxBTvR5rxkW9K';
    private $appId='2a9bd90b-fe95-4659-83cb-04de662fbbac';
    private $isSandbox = true;
    private $invoiceId = 'SrAARgNrPgvveiBQtNc4gk';
    private $notify = '{"ipn_type":"invoice","event":"invoice_created","app_id":"2a9bd90b-fe95-4659-83cb-04de662fbbac","invoice_id":"SrAARgNrPgvveiBQtNc4gk","order_id":"6330f1f118df1","price_amount":100.0,"price_currency":"USD","network":null,"address":null,"pay_currency":"USDT","pay_amount":0.0,"exchange_rate":0.0,"paid_amount":0.0,"confirmed_amount":0.0,"refunded_price_amount":0.0,"create_time":"2022-09-26T00:27:29.6697063Z","expiration_time":"2022-09-26T00:32:29.6698139Z","status":"New","error_status":"None","ext_args":null,"transactions":null,"notify_id":"0443e623-492a-474a-bd22-b866d6b7beb9","notify_time":"0001-01-01T00:00:00"}';

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
