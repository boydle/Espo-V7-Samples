<?php
namespace Espo\Modules\ControlBoard\Services;

use Espo\Core\Utils\Util;

class ControlBoard extends \Espo\Services\Record
{
    public function getListDataCards($params)
    {
        $disableCount = $this->metadata->get(['entityDefs', $this->entityType, 'collection', 'countDisabled']) ?? false;
        if ($this->listCountQueryDisabled || $this->getMetadata()->get(['entityDefs', $this->entityType, 'collection', 'countDisabled'])) {
            $disableCount = true;
        }
        $maxSize = 0;
        if ($disableCount) {
            if (!empty($params['maxSize'])) {
                $maxSize = $params['maxSize'];
                $params['maxSize'] = $params['maxSize'] + 1;
            }
        }
        $selectParams = $this->getSelectParams($params);
        $selectParams['maxTextColumnsLength'] = $this->getMaxSelectTextAttributeLength();
        $selectAttributeList = $this->getSelectManager()->getSelectAttributeList($params);
        if ($selectAttributeList) {
            $selectParams['select'] = $selectAttributeList;
        } else {
            $selectParams['skipTextColumns'] = $this->isSkipSelectTextAttributes();
        }
        $controlBoardCriteriaData = $this->getMetadata()->get(['entityDefs', $this->entityType, 'controlBoardCriteriaData']);
        $controlBoardCriteriaField = $controlBoardCriteriaData['controlBoardCriteriaField'];
        $fieldSelectStatement = $this->getMetadata()->get(['entityDefs', $this->entityType, 'fields', $controlBoardCriteriaField, 'select']);
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
            $selectParamsSub = $selectParams;
            $type = $group['conditionGroupType'];
            $label = $group['conditionGroupLabel'];
            $conditionIndex = 0;
            foreach ($group['conditionValues'] as $whereCondition) {
                $operator = '';
                if ($whereCondition['operator'] && $whereCondition['operator'] !== '=') {
                    $operator = $whereCondition['operator'];
                }
                $groupValue = $whereCondition['value'];
                if ($whereCondition['valueType'] && $whereCondition['valueType'] === 'int') {
                    $groupValue = intval($groupValue);
                }
                if ($conditionIndex > 0 && $type === 'or') {
                    $selectParamsSub['whereClause'][] = ['OR' => [$controlBoardCriteriaField.$operator => $groupValue]];
                } else {
                    $selectParamsSub['whereClause'][] = [$controlBoardCriteriaField.$operator => $groupValue];
                }
                $conditionIndex++;
            }
            $o = (object) [
                'name' => $label
            ];
            $collectionSub = $this->getRepository()->find($selectParamsSub);
            if (!$disableCount) {
                $totalSub = $this->getRepository()->count($selectParamsSub);
            } else {
                if ($maxSize && count($collectionSub) > $maxSize) {
                    $totalSub = -1;
                    unset($collectionSub[count($collectionSub) - 1]);
                } else {
                    $totalSub = -2;
                }
            }
            foreach ($collectionSub as $e) {
                $this->loadAdditionalFieldsForList($e);
                if (!empty($params['loadAdditionalFields'])) {
                    $this->loadAdditionalFields($e);
                }
                if (!empty($selectAttributeList)) {
                    $this->loadLinkMultipleFieldsForList($e, $selectAttributeList);
                }
                $this->prepareEntityForOutput($e);
                $collection[] = $e;
            }
            $o->total = $totalSub;
            $o->list = $collectionSub->getValueMapList();
            $additionalData->groupList[] = $o;
        }
        if (!$disableCount) {
            $total = $this->getRepository()->count($selectParams);
        } else {
            if ($maxSize && count($collection) > $maxSize) {
                $total = -1;
                unset($collection[count($collection) - 1]);
            } else {
                $total = -2;
            }
        }
        return (object) [
            'total' => $total,
            'collection' => $collection,
            'additionalData' => $additionalData
        ];
    }
}
