<?php
namespace Payum\Server\Tests\Functional\Api\Controller;

use Makasim\Yadm\Storage;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GatewayControllerTest extends ClientTestCase
{
    use ResponseHelper;
    
    public function setUp()
    {
        parent::setUp();

        /** @var Storage $gatewayConfigStorage */
        $gatewayConfigStorage = $this->app['payum.gateway_config_storage'];

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setGatewayName('paypal_express_checkout');
        $gatewayConfig->setFactoryName('paypal_express_checkout');
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setGatewayName('stripe_js');
        $gatewayConfig->setFactoryName('stripe_js');
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setGatewayName('stripe_checkout');
        $gatewayConfig->setFactoryName('stripe_checkout');
        $gatewayConfigStorage->insert($gatewayConfig);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $this->getClient()->postJson('/gateways/', [
            'gatewayName' => 'aGateway',
            'factoryName' => 'offline',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateway', $content);

        $this->assertObjectHasAttribute('gatewayName', $content->gateway);
        $this->assertEquals('aGateway', $content->gateway->gatewayName);

        $this->assertObjectHasAttribute('factoryName', $content->gateway);
        $this->assertEquals('offline', $content->gateway->factoryName);

        $this->assertStringStartsWith('http://localhost/gateways/', $this->getClient()->getResponse()->headers->get('Location'));
    }

    /**
     * @test
     */
    public function shouldNotAllowCreateGatewayIfOneWithSameNameAlreadyExists()
    {
        $this->getClient()->postJson('/gateways/', [
            'gatewayName' => 'aUniqueGateway',
            'factoryName' => 'offline',
        ]);

        $this->assertClientResponseStatus(201);

        $this->getClient()->postJson('/gateways/', [
            'gatewayName' => 'aUniqueGateway',
            'factoryName' => 'offline',
        ]);

        $this->assertClientResponseStatus(400);
    }

    /**
     * @test
     */
    public function shouldAllowGetAllGateways()
    {
        $this->getClient()->request('GET', '/gateways/');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateways', $content);

        $this->assertObjectHasAttribute('paypal_express_checkout', $content->gateways);
        $this->assertObjectHasAttribute('stripe_js', $content->gateways);
        $this->assertObjectHasAttribute('stripe_checkout', $content->gateways);
    }

    /**
     * @test
     */
    public function shouldAllowGetPaypalExpressCheckoutGateway()
    {
        $this->getClient()->request('GET', '/gateways/paypal_express_checkout');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateway', $content);

        $this->assertObjectHasAttribute('factoryName', $content->gateway);
        $this->assertEquals('paypal_express_checkout', $content->gateway->factoryName);

        $this->assertObjectHasAttribute('gatewayName', $content->gateway);
        $this->assertEquals('paypal_express_checkout', $content->gateway->gatewayName);

        $this->assertObjectHasAttribute('config', $content->gateway);
    }

    /**
     * @test
     */
    public function shouldAllowGetStripeJsGateway()
    {
        $this->getClient()->request('GET', '/gateways/stripe_js');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateway', $content);

        $this->assertObjectHasAttribute('factoryName', $content->gateway);
        $this->assertEquals('stripe_js', $content->gateway->factoryName);

        $this->assertObjectHasAttribute('gatewayName', $content->gateway);
        $this->assertEquals('stripe_js', $content->gateway->gatewayName);

        $this->assertObjectHasAttribute('config', $content->gateway);
    }

    /**
     * @test
     */
    public function shouldAllowGetStripeCheckoutGateway()
    {
        $this->getClient()->request('GET', '/gateways/stripe_checkout');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateway', $content);

        $this->assertObjectHasAttribute('factoryName', $content->gateway);
        $this->assertEquals('stripe_checkout', $content->gateway->factoryName);

        $this->assertObjectHasAttribute('gatewayName', $content->gateway);
        $this->assertEquals('stripe_checkout', $content->gateway->gatewayName);

        $this->assertObjectHasAttribute('config', $content->gateway);
    }

    /**
     * @test
     */
    public function shouldAllowDeleteGateway()
    {
        $this->getClient()->request('DELETE', '/gateways/stripe_checkout');
        $this->assertClientResponseStatus(204);


        $this->setExpectedException(NotFoundHttpException::class);
        $this->getClient()->request('GET', '/gateways/stripe_checkout');
    }
}
