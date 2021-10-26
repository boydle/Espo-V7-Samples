<?php
namespace Espo\Modules\ControlBoard\Services;

use Espo\Core\Utils\Util;
use Espo\Core\{
FieldProcessing\ListLoadProcessor,
FieldProcessing\Loader\Params as FieldLoaderParams
};

class ControlBoard extends \Espo\Services\Record
{

    public function getListDataCards($searchParams)
    {
        $disableCount = $this->metadata->get(['entityDefs', $this->entityType, 'collection', 'countDisabled']) ?? false;
        $maxSize = $searchParams->getMaxSize();
        if ($disableCount && $maxSize) {
            $searchParams = $searchParams->withMaxSize($maxSize + 1);
        }
        $recordService = $this->recordServiceContainer->get($this->entityType);

        $query = $this->selectBuilderFactory->create()->from($this->entityType)->withStrictAccessControl()->withSearchParams($searchParams)->build();

        $entityCollection = $this->entityManager->getCollectionFactory()->create($this->entityType);

        $repository = $this->entityManager->getRepository($this->entityType);

        $controlBoardCriteriaData = $this->getMetadata()->get(['entityDefs', $this->entityType,'controlBoardCriteriaData']);
        $controlBoardCriteriaField = $controlBoardCriteriaData['controlBoardCriteriaField'];
        $fieldSelectStatement = $this->getMetadata()->get(['entityDefs', $this->entityType, 'fields',$controlBoardCriteriaField, 'select']);
        if ($fieldSelectStatement) {
            $pdo = $this->getEntityManager()->getPDO();
            $sql = 'UPDATE '.Util::camelCaseToUnderscore($this->entityType).' SET `'.$controlBoardCriteriaField.'` = '.$fieldSelectStatement;
            $sth = $pdo->prepare($sql);
            $sth->execute();
        }
        $collection = new \Espo\ORM\EntityCollection([], $this->entityType);
        $criteriaConditionGroups = $controlBoardCriteriaData['criteriaConditionGroups'];

        $additionalData = (object) [
            'groupList' => []
        ];

        foreach ($criteriaConditionGroups as $group) {
            $type = $group['conditionGroupType'];
            $label = $group['conditionGroupLabel'];
            $conditionIndex = 0;
            foreach($group['conditionValues'] as $whereCondition) {
                $whereClause = [];
                $operator = '';
                if($whereCondition['operator'] && $whereCondition['operator'] !== '=') {
                    $operator = $whereCondition['operator'];
                }
                $groupValue = $whereCondition['value'];
                if($whereCondition['valueType'] && $whereCondition['valueType'] === 'int') {
                    $groupValue = intval($groupValue);
                }
                if($conditionIndex > 0 && $type === 'or') {
                    $whereClause[] = ['OR' => [$controlBoardCriteriaField.$operator => $groupValue]];
                } else {
                    $whereClause[] = [$controlBoardCriteriaField.$operator => $groupValue];
                }
                $conditionIndex++;
            }
            $itemSelectBuilder = $this->entityManager->getQueryBuilder()->select()->clone($query);
            $itemSelectBuilder->where($whereClause);
            $groupObject = (object) [
                'name' => $label
            ];
            $itemQuery = $itemSelectBuilder->build();
            $collectionSub = $repository->clone($itemQuery)->find();
            if (!$disableCount) {
                $totalSub = $repository->clone($itemQuery)->count();
            } else {
                if ($maxSize && count($collectionSub) > $maxSize) {
                    $totalSub = -1;
                    unset($collectionSub[count($collectionSub) - 1]);
                } else {
                    $totalSub = -2;
                }
            }
            $fieldLoader = new FieldLoaderParams();
            $loadProcessorParams = $fieldLoader->withSelect($searchParams->getSelect());
            foreach ($collectionSub as $e) {
                $this->listLoadProcessor->process($e, $loadProcessorParams);
                $recordService->prepareEntityForOutput($e);
                $collection[] = $e;
            }
            $groupObject->total = $totalSub;
            $groupObject->list = $collectionSub->getValueMapList();
            $additionalData->groupList[] = $groupObject;
        }
        if (!$disableCount) {
            $total = $repository->clone($query)->count();
        } else {
            if ($maxSize && count($collection) > $maxSize) {
                $total = -1;
                unset($collection[count($collection) - 1]);
            } else {
                $total = -2;
            }
        }
        $result = (object) [
            'total' => $total,
            'collection' => $collection,
            'additionalData' => $additionalData
        ];
        return $result;
    }
}