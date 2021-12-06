<?php
declare(strict_types=1);

namespace Rarus\Interns\BonusServer\Tests\Integration\BonusCalculated;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rarus\Interns\BonusServer\Bonus\Bonus;

class Bitrix24BonusCalculated extends TestCase
{
    private LoggerInterface $logger;

    public function setUp(): void
    {

        $this->logger = $this->createMock(LoggerInterface::class);

    }
    public function testBalanceError():void
    {
        $products = [
            'Product' => 'pizza'
        ];
        $bonus = new Bonus($this->logger,0,'10000',$products);
        $productRows= $bonus->calculateDiscount();
        self::assertEquals($products,$productRows);
    }

}