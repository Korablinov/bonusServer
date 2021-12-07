<?php
declare(strict_types=1);

namespace Rarus\Interns\BonusServer\Tests\Integration\BonusCalculated;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rarus\Interns\BonusServer\Bonus\Bonus;

class BonusTest extends TestCase
{
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testBalanceError(): void
    {
        $products = [
            'Product' => 'pizza'
        ];

        $bonus = new Bonus($this->logger, 0, '10000', $products);
        $this->logger->expects($this->once())->method('debug')->withAnyParameters();
        $productRows = $bonus->calculateDiscount();

        self::assertEquals($products, $productRows);
    }

    public function testProductRows(): void
    {
        $products = [
            ['Product' => 'pizza', 'PRICE' => '200', 'QUANTITY' => 2],
            ['Product' => 'cok', 'PRICE' => '50', 'QUANTITY' => 2]
        ];
        $productsResult = [
            ['Product' => 'pizza', 'PRICE' => '200', 'QUANTITY' => 2, 'DISCOUNT_SUM' => 20.0],
            ['Product' => 'cok', 'PRICE' => '50', 'QUANTITY' => 2, 'DISCOUNT_SUM' => 5.0]
        ];

        $bonus = new Bonus($this->logger, 10000, (string)$this->getDealOpportunity($products), $products);
        $productRows = $bonus->calculateDiscount();

        self::assertEquals($productsResult, $productRows);
    }

    private function getDealOpportunity(array $products): float
    {
        $opportunity = 0;
        foreach ($products as $productValue) {
            $opportunity += (int)$productValue['PRICE'] * (int)$productValue['QUANTITY'];
        }
        return (float)$opportunity;
    }
}
