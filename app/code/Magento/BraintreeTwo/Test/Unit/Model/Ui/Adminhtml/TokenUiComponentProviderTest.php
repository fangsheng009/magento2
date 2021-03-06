<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Model\Ui\Adminhtml;

use Magento\BraintreeTwo\Model\Ui\Adminhtml\TokenUiComponentProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;

/**
 * Class TokenUiComponentProviderTest
 */
class TokenUiComponentProviderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var TokenUiComponentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentFactory;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var TokenUiComponentProvider
     */
    private $tokenUiComponentProvider;

    protected function setUp()
    {
        $this->componentFactory = $this->getMockBuilder(TokenUiComponentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->urlBuilder = $this->getMock(UrlInterface::class);

        $this->tokenUiComponentProvider = new TokenUiComponentProvider(
            $this->componentFactory,
            $this->urlBuilder
        );
    }

    /**
     * @covers \Magento\BraintreeTwo\Model\Ui\Adminhtml\TokenUiComponentProvider::getComponentForToken
     */
    public function testGetComponentForToken()
    {
        $nonceUrl = 'https://payment/adminhtml/nonce/url';
        $type = 'VI';
        $maskedCC = '1111';
        $expirationDate = '12/2015';

        $expected = [
            'nonceUrl' => $nonceUrl,
            'details' => [
                'type' => $type,
                'maskedCC' => $maskedCC,
                'expirationDate' => $expirationDate
            ],
            'template' => 'vault.phtml'
        ];

        $paymentToken = $this->getMock(PaymentTokenInterface::class);
        $paymentToken->expects(static::once())
            ->method('getTokenDetails')
            ->willReturn('{"type":"VI","maskedCC":"1111","expirationDate":"12\/2015"}');
        $paymentToken->expects(static::once())
            ->method('getPublicHash')
            ->willReturn('37du7ir5ed');

        $this->urlBuilder->expects(static::once())
            ->method('getUrl')
            ->willReturn($nonceUrl);

        $tokenComponent = $this->getMock(TokenUiComponentInterface::class);
        $tokenComponent->expects(static::once())
            ->method('getConfig')
            ->willReturn($expected);

        $this->componentFactory->expects(static::once())
            ->method('create')
            ->willReturn($tokenComponent);

        $component = $this->tokenUiComponentProvider->getComponentForToken($paymentToken);
        static::assertEquals($tokenComponent, $component);
        static::assertEquals($expected, $component->getConfig());
    }
}
