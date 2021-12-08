<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Doctrine\ORM\EntityManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;
use Rarus\Interns\BonusServer\Bonus\Bitrix24Deal;
use Rarus\Interns\BonusServer\Bonus\Bonus;
use Rarus\Interns\BonusServer\Bonus\DataBase;
use Rarus\Interns\BonusServer\TrainingClassroom\Services\Bitrix24ApiClientServiceBuilder;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

$log = new Logger('bonusPayment');
$log->pushHandler(new StreamHandler(dirname(__DIR__) . '/logs/webhook.log', Logger::DEBUG));
$log->pushProcessor(new MemoryUsageProcessor(true, true));
$log->pushProcessor(new WebProcessor());
$log->pushProcessor(new IntrospectionProcessor());

try {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

    $request = Request::createFromGlobals();

    $b24Service = Bitrix24ApiClientServiceBuilder::getServiceBuilder();
    $bitrixDeal = new Bitrix24Deal($b24Service);

    $dealId = $bitrixDeal->getDealId($request);
    $dealOpportunity = $bitrixDeal->getDealOpportunity($dealId);
    $productRows = $bitrixDeal->getProductRowsByDealId($dealId);

    $log->debug('dealID: ', [$dealId]);
    $log->debug('Product', [$productRows]);

    $contactId = (int)$bitrixDeal->getDealById($dealId)->CONTACT_ID;

    /** @var EntityManager $entityManager */
    $entityManager = require_once '../tests/bootstrap.php';
    $qb = $entityManager->getConnection()->createQueryBuilder();
    $dataBase = new DataBase($qb);
    $contactIdInDataBase = $dataBase->isUserExist($contactId);
    $log->debug('result', [$contactIdInDataBase]);
    $contactBalance = $dataBase->getContactBalance($contactId);

    $bonus = new Bonus($log, (int)$contactBalance, $dealOpportunity, $productRows);
    $updateDeal = $bonus->calculateDiscount();

    if ($updateDeal === $productRows) {
        throw new DomainException('У клиента недостаточно бонусов!');
    }

    $bitrixDeal->updateDealProductRows($updateDeal, $dealId);
    $dataBase->addBonusesToProcessing((int)$bonus->bonusCount(), $dealId);
    $log->debug('countBonus and dealId', [
        'bonusCount' => $contactBalance,
        'dealId' => $dealId,
    ]);
} catch (Throwable $err) {
    $log->error($err->getMessage(), [$err->getCode(), $err->getFile(), $err->getTraceAsString()]);
}