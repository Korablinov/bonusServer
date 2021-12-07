<?php

namespace Rarus\Interns\BonusServer\Bonus;


use Money\Money;
use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(dirname(__DIR__,2) . '/.env');

/**
 * Class Bonus
 * @package Rarus\Interns\BonusServer\Entities
 *
 * Класс для распределения бонусов по табличной части сделки.
 */
class Bonus
{
    private LoggerInterface $logger;
    private int $contactCurrentBonuses;
    private string $dealOpportunity;

    /**
     * @var array Массив с табличной частью сделки.
     */
    private array $dealProductRows;
    private float $allowableDiscount;

    private Money $discount;

    /**
     * Bonus constructor.
     * @param LoggerInterface $logger
     * @param int $contactCurrentBonuses
     * @param string $dealOpportunity
     * @param array $dealProductRows
     */
    public function __construct(
        LoggerInterface $logger,
        int             $contactCurrentBonuses,
        string          $dealOpportunity,
        array           $dealProductRows
    )
    {
        $this->logger = $logger;
        $this->contactCurrentBonuses = $contactCurrentBonuses;
        $this->dealOpportunity = $dealOpportunity;
        $this->dealProductRows = $dealProductRows;
        $this->allowableDiscount = (float)$_ENV['BONUS_PAYMENT'];

        $this->discount = Money::RUB((int)ceil($this->dealOpportunity * ($this->allowableDiscount / 100)));
    }

    /**
     * @return array Ассоциативный массив с обновленной табличной частью сделки
     *
     * Функция равномерно распределяет бонусы по табличной части сделки.
     */
    public function calculateDiscount(): array
    {
        if ((int)$this->discount->getAmount() >= $this->contactCurrentBonuses) {
            $this->logger->debug('У пользователя недостаточно бонусов для оплаты!');
            return $this->dealProductRows;
        }

        $productsProportions = [];

        foreach ($this->dealProductRows as $productValues) {
            $productsProportions[] =
                (($productValues['PRICE'] * $productValues['QUANTITY']) * (100 / $this->dealOpportunity));
        }

        $allocatedDiscount = $this->discount->allocate($productsProportions);

        foreach ($productsProportions as $keyPart => $valuePart) {
            $this->dealProductRows[$keyPart]['DISCOUNT_SUM'] =
                floor($allocatedDiscount[$keyPart]->getAmount() / $this->dealProductRows[$keyPart]['QUANTITY']);
        }
        return $this->dealProductRows;
    }

    /**
     * @return string Количество потраченных бонусов.
     *
     * Функция возвращает количество бонусов, потраченных клиентом.
     */
    public function bonusCount(): string
    {
        return $this->discount->getAmount();
    }
}
