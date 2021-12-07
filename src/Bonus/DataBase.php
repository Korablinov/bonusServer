<?php
declare(strict_types=1);

namespace Rarus\Interns\BonusServer\Bonus;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;


class DataBase
{
    private QueryBuilder $queryBuilder;
    public const  NEW_ORDER = 'new_order';

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @throws Exception
     */
    public function isUserExist(int $contactId): bool
    {
        $result = $this->queryBuilder
            ->select('*')
            ->from('contacts', 'c')
            ->where('contactID = :contactId')
            ->setParameter('contactId', $contactId)
            ->fetchAssociative();

        return is_array($result);
    }

    /**
     * @throws Exception
     */
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

    /**
     * @throws Exception
     */
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
            ->executeQuery();
    }

    /**
     * @throws Exception
     */
    public function addBonusesToProcessing(int $processing, $dealId)
    {
        $this->queryBuilder
            ->update('deals')
            ->set('processing', '?')
            ->set('dealStage', '?')
            ->setParameter(0, $processing)
            ->setParameter(1, 'bonus_payment')
            ->where('dealID = :dealId')
            ->setParameter('dealId', $dealId)
            ->executeQuery();
    }

    /**
     * @throws Exception
     */
    public function getDealStage($dealId)
    {
        return $this->queryBuilder
            ->select('*')
            ->from('deals', 'd')
            ->where('dealID = :dealId')
            ->setParameter('dealId', $dealId)
            ->fetchAssociative()['dealStage'];

    }

    /**
     * @throws Exception
     */
    public function setContactBalance($balance, $contactId)
    {
        $this->queryBuilder
            ->update('contacts')
            ->set('bonusCount', '?')
            ->setParameter(0, $balance)
            ->where('contactID = :contactId')
            ->setParameter('contactId', $contactId)
            ->executeStatement();
    }

    /**
     * @throws Exception
     */
    public function getContactBalance($contactId)
    {
        return $this->queryBuilder
            ->getConnection()
            ->executeQuery(sprintf('SELECT bonusCount FROM contacts WHERE contactID=%d',$contactId))
            ->fetchAssociative()['bonusCount'];
    }

    /**
     * @throws Exception
     */
    public function bonusChanges($contactId, $processing, $contactBalance)
    {
        $this->queryBuilder
            ->update('contacts')
            ->set('bonusCount', '?')
            ->setParameter(0, $contactBalance - $processing)
            ->where('contactID = :contactId')
            ->setParameter('contactId', $contactId)
            ->executeStatement();
    }
}
