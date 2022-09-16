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

        $this->uniPaymentClient->getConfig()->setAppId("cee1b9e2-d90c-4b63-9824-d621edb38012");
        $this->uniPaymentClient->getConfig()->setApiKey("9G62Fd7fCQGyznVvatk4SAfGsHDEt819E");
        $this->uniPaymentClient->getConfig()->setIsSandbox(true);
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
        $response = $this->uniPaymentClient->getInvoiceById('9EfHVGLDjQssJv7xnBsDSM');
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
        $notify='{"ipn_type":"invoice","event":"invoice_created","app_id":"cee1b9e2-d90c-4b63-9824-d621edb38012","invoice_id":"12wQquUmeCPUx3qmp3aHnd","order_id":"ORDER_123456","price_amount":2.0,"price_currency":"USD","network":null,"address":null,"pay_currency":null,"pay_amount":0.0,"exchange_rate":0.0,"paid_amount":0.0,"confirmed_amount":0.0,"refunded_price_amount":0.0,"create_time":"2022-09-14T04:57:54.5599307Z","expiration_time":"2022-09-14T05:02:54.559933Z","status":"New","error_status":"None","ext_args":"Merchant Pass Through Data","transactions":null,"notify_id":"fd58cedd-67c6-4053-ae65-2f6fb09a7d2c","notify_time":"0001-01-01T00:00:00"}';
        $response = $this->uniPaymentClient->checkIpn($notify);
        $this->assertEquals('OK', $response->getCode());
    }
}
