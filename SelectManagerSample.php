<?php
namespace Espo\Modules\ControlBoard\Controllers;

class ControlBoard extends \Espo\Core\Templates\Controllers\Base
{
    public function getActionListDataCards($params, $data, $request)
    {
        if (!$this -> getAcl() -> check($this -> name, 'read')) {
            throw new Forbidden("No read access for {$this -> name}.");
        }
        $params = [];
        // get the collection parameters from the front-end routing request
        $this -> fetchListParamsFromRequest($params, $request, $data);
        $maxSizeLimit = $this -> getConfig() -> get('recordListMaxSizeLimit', self::MAX_SIZE_LIMIT);
        if (empty($params['maxSize'])) {
            $params['maxSize'] = $maxSizeLimit;
        }
        if (!empty($params['maxSize']) & $params['maxSize']  >  $maxSizeLimit) {
            throw new Forbidden("Max size should should not exceed " . $maxSizeLimit . ". Use offset and limit.");
        }
        $result = $this -> getRecordService() -> getListDataCards($params);
        return (object) [
            'total' => $result->total,
            'list' => $result->collection->getValueMapList(),
            'additionalData' => $result->additionalData
        ];
    }
}