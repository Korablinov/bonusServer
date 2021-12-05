<?php

namespace Rarus\Interns\BonusServer\Bonus;

use Money\Currencies\ISOCurrencies;
use Monolog\Logger;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\DecimalMoneyParser;

/**
 * Class Discount - выполняет расчет и выполнение скидки
 * @package Numbers
 */
class Discount
{
    private $dealPrice;
    private $rule;
    private $numberOfBonuses;
    private Logger $log;

    /**
     * Discount constructor - Конструктор класса
     * @param $dealPrice - стоимость всего заказа
     * @param $rule - правило - процент от стоимости заказа
     * @param $numberOfBonuses - количество имеющихся бонусов
     * @param $log - логи
     */
    public function __construct( $dealPrice, $rule, $numberOfBonuses, $log)
    {
        $this->dealPrice = $dealPrice;
        $this->rule = $rule;
        $this->numberOfBonuses = $numberOfBonuses;
        $this->log = $log;
    }

    /**
     *  Получить стоимость сделки
     *
     * @return mixed - стоимость сделки
     */
    public function getDealValue()
    {
        return $this->dealPrice;
    }

    /**
     * Задать стоимость сделки
     *
     * @param mixed $dealPrice - стоимость сделки
     */
    public function setDealValue($dealPrice): void
    {
        $this->dealPrice = $dealPrice;
    }

    /**
     * Получить правило начисления скидки
     *
     * @return mixed - процент от суммы сделки
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     *  Задать правило начисления скидки
     *
     * @param mixed $rule - процент от суммы сделки
     */
    public function setRule($rule): void
    {
        $this->rule = $rule;
    }

    /**
     * Получить количество имеющихся у пользователя бонусов
     *
     * @return mixed - количество имеющихся у пользователя бонусов
     */
    public function getNumberOfBonuses()
    {
        return $this->numberOfBonuses;
    }

    /**
     * Задать количество имеющихся у пользователя бонусов
     *
     * @param mixed $numberOfBonuses - количество имеющихся у пользователя бонусов
     */
    public function setNumberOfBonuses($numberOfBonuses): void
    {
        $this->numberOfBonuses = $numberOfBonuses;
    }

    /**
     * Получить лог
     *
     * @return Logger
     */
    public function getLog(): Logger
    {
        return $this->log;
    }

    /**
     * Задать лог
     *
     * @param Logger $log
     */
    public function setLog(Logger $log): void
    {
        $this->log = $log;
    }

    /**
     * Выполняет расчёт скидочной суммы
     * @return - скидочная сумма
     */
    public function accrualBonuses()
    {
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $newBonuses = $moneyParser->parse((string)$this->getDealValue(), new Currency('RUB'));
        $numberOfBonuses = $moneyParser->parse((string)$this->getNumberOfBonuses(), new Currency('RUB'));
        $newBonuses = $newBonuses->divide(100);
        $newBonuses = $newBonuses->multiply($this->getRule());
        $newBonuses = $newBonuses->add($numberOfBonuses);

        $moneyFormatter = new DecimalMoneyFormatter($currencies);
        return ($moneyFormatter->format($newBonuses));
    }
}
