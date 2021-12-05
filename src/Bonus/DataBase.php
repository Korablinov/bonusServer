<?php


namespace Rarus\Interns\BonusServer\Bonus;

use Doctrine\DBAL\Query\QueryBuilder;


class DataBase
{
    private QueryBuilder $queryBuilder;
    public const  NEW_ORDER = 'new_order';

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function isUserExist(int $contactId)
    {
        $result = $this->queryBuilder
            ->select('*')
            ->from('contacts', 'c')
            ->where('contactID = :contactId')
            ->setParameter('contactId', $contactId)
            ->fetchAssociative();

        return is_array($result);
    }

    public function addUser($contactId)
    {
        $this->queryBuilder
            ->insert('contacts')
            ->values(
                array(
                    'contactID' => '?',
                    'bonusCount' => '50'
                )
            )
            ->setParameter(0, $contactId)
            ->executeQuery();
    }

    public function createLocalDeal($dealId, $contactId, $dealStage, $processing = null): void
    {
        $this->queryBuilder
            ->insert('deals')
            ->values(
                array(
                    'dealID' => '?',
                    'contactID' => '?',
                    'dealStage' => '?',
                    'processing' => '?'
                )
            )
            ->setParameter(0, $dealId)
            ->setParameter(1, $contactId)
            ->setParameter(2, $dealStage)
            ->setParameter(3, $processing)
            ->executeStatement();
    }

    public function addBonusesToProcessing(int $processing, $dealId)
    {
        return $this->queryBuilder
            ->update('deals', 'd')
            ->set('d.processing', '?')
            ->set('d.dealStage', '?')
            ->setParameter(0, $processing)
            ->setParameter(1, 'bonus_payment')
            ->where('dealID = :dealId')
            ->setParameter('dealId', $dealId)
            ->fetchAssociative();
    }

    public function getDealStage($dealId)
    {
        return $this->queryBuilder
            ->select('*')
            ->from('deals', 'd')
            ->where('dealID = :dealId')
            ->setParameter('dealId', $dealId)
            ->fetchAssociative();

    }
    public function setContactBalance($balance,$contactId)
    {
        $this->queryBuilder
            ->update('contacts','c')
            ->set('c.bonusCount','?')
            ->setParameter(0,$balance)
            ->where('contactID = :contactId')
            ->setParameter('contactId',$contactId)
            ->executeStatement();
    }

    public function getContactBalance($contactId)
    {
        return $this->queryBuilder
            ->select('bonusCount')
            ->from('contacts', 'c')
            ->where('contactID = :contactId')
            ->setParameter('contactId', $contactId)
            ->fetchAssociative()['bonusCount'];
    }

    public function bonusChanges($contactId, $dealId,$processing,$contactBalance)
    {
        $this->queryBuilder
            ->update('contacts', 'c')
            ->update('deals', 'd')
            ->set('c.bonusCount', '?')
            ->set('d.processing', '?')
            ->setParameter(0, $contactBalance- $processing)
            ->setParameter(1, 0)
            ->where('c.contactID = :contactId')
            ->where('d.dealID = :dealId')
            ->setParameter('contactId', $contactId)
            ->setParameter('dealId', $dealId)
            ->executeStatement();
    }
}
