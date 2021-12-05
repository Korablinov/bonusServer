<?php
declare(strict_types=1);

namespace Rarus\Interns\BonusServer\Bonus;

use Bitrix24\SDK\Services\ServiceBuilder;
use Rarus\Interns\BonusServer\TrainingClassroom\Services\Bitrix24ApiClientServiceBuilder;
use Symfony\Component\HttpFoundation\Request;

class Bitrix24Deal
{
    private ServiceBuilder $serviceBuilder;

    public function __construct(ServiceBuilder $serviceBuilder)
    {
        $this->serviceBuilder = $serviceBuilder;
    }

    public function getDealId(Request $request): int
    {
        return (int)str_replace('DEAL_', '', $request->get('document_id')[2]);
    }

    public function getDealbyId( int $dealId)
    {
        return $this->serviceBuilder
            ->getCRMScope()
            ->deal()
            ->get($dealId)
            ->deal();
    }
    
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

    public function getDealOpportunity($dealId)
    {
        return (string)$this->serviceBuilder
            ->getCRMScope()
            ->dealProductRows()
            ->core
            ->call('crm.deal.productrows.get',['ID' => $dealId])
            ->getResponseData()
            ->getResult()
            ->getResultData()['OPPORTUNITY'];
    }
}