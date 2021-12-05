<?php

declare(strict_types=1);

require '../tests/bootstrap.php';

use Money\Currency;
use Money\Money;

use Rarus\Interns\BonusServer\TrainingClassroom\Services\Bitrix24ApiClientServiceBuilder;

$b24DealId = 34;
$b24Service = Bitrix24ApiClientServiceBuilder::getServiceBuilder();

//$b24Deal = $b24Service->getCRMScope()->dealProductRows()->get($b24DealId)->getProductRows();
$b24Deal = $b24Service->getCRMScope()->dealProductRows()->core->call('crm.deal.productrows.get',['ID'=>$b24DealId])
->getResponseData()
->getResult()
->getResultData();
//print_r($b24Deal);
foreach ($b24Deal as $rows){
    $rows['PRICE']=1232;
    print_r( $rows['PRICE']. PHP_EOL);
}
die();
$productsCount = 0;
$productsId = [];
$price = Money::RUB(0);
foreach ($b24Deal as $product) {
    $price = $price->add(Money::RUB($product['PRICE'] * 100 * $product['QUANTITY']));
    $productsCount += (int)$product['QUANTITY'];
    $productsId[] = $product['ID'];
}

var_dump('Общая стоимость заказа:' . $price->getAmount());
var_dump('Кол-во товаров: ' . $productsCount);
$discount = $price->multiply(10)->divide(100)->divide(100)->divide($productsCount);
var_dump('Скидка: ' . $discount->getAmount());
var_dump($productsId);

$result = ['ID' => $b24DealId,
            'PRODUCT_NAME'     => 'пицца',
            'QUANTITY'         => $productsCount,
            'PRICE_EXCLUSIVE'  => $price->getAmount() - $discount->getAmount(),          // цена без налога, но со скидкой
            'PRICE_ACCOUNT'    => (string)  ($price->getAmount() - $discount->getAmount()),     // цена отформатированная для вывода в отчётах
            'PRICE_BRUTTO'     => $price->getAmount(),          // цена с налогом, но без скидки
            'PRICE_NETTO'      => $price->getAmount(),          // цена без налога и без скидки
            'PRICE'            => $price->getAmount() - $discount->getAmount(),          // цена конечная с учётом налогов и скидок
            // указываем скидку
            'DISCOUNT_TYPE_ID' => 1,        // тип скидки - монетарная скидка
            'DISCOUNT_SUM'     => $discount->getAmount(),   // указываем абсолютная сумма
];
foreach ($b24Deal as $rows){
    print_r( $rows['PRICE']);
}
print_r($b24Deal);
die();
$b24Deal = $b24Service->getCRMScope()->dealProductRows()->core->call('crm.deal.update', $b24Deal);
die();
$b24Service->getCRMScope()->dealProductRows()->set($b24DealId, $result);

// $price = Money::RUB( '12,3');
// var_dump($price);
//formater_decimal