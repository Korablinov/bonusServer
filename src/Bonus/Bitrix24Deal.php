<?php
declare(strict_types=1);

namespace Rarus\Interns\BonusServer\Bonus;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult;
use Bitrix24\SDK\Services\ServiceBuilder;
use Symfony\Component\HttpFoundation\Request;

class Bitrix24Deal
{
    private ServiceBuilder $serviceBuilder;

    public function __construct(ServiceBuilder $serviceBuilder)
    {
        $this->serviceBuilder = $serviceBuilder;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function getDealId(Request $request): int
    {
        return (int)str_replace('DEAL_', '', $request->get('document_id')[2]);
    }

    /**
     * @throws TransportException
     * @throws BaseException
     */
    public function getDealById(int $dealId): DealItemResult
    {
        return $this->serviceBuilder
            ->getCRMScope()
            ->deal()
            ->get($dealId)
            ->deal();
    }

    /**
     * @throws TransportException
     * @throws BaseException
     */
    public function getProductRowsByDealId(int $dealId): array
    {
        return $this->serviceBuilder
            ->getCRMScope()
            ->dealProductRows()
            ->core
            ->call('crm.deal.productrows.get', ['ID' => $dealId])
            ->getResponseData()
            ->getResult()
            ->getResultData();
    }

    /**
     * @throws TransportException
     * @throws BaseException
     */
    public function updateDealProductRows(array $dealProductRows, int $dealId): void
    {
        $this->serviceBuilder
            ->getCRMScope()
            ->dealProductRows()
            ->core
            ->call(
            'crm.deal.productrows.set',
            [
                'ID' => $dealId,
                'rows' => $dealProductRows
            ]
        )
            ->getResponseData()
            ->getResult()
            ->getResultData();
    }

    /**
     * @throws TransportException
     * @throws BaseException
     */
    public function getDealOpportunity($dealId): string
    {
        return $this->serviceBuilder
            ->getCRMScope()
            ->deal()
            ->get($dealId)
            ->deal()
            ->OPPORTUNITY;
    }
}