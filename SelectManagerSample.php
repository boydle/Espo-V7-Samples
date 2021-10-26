<?php
namespace Espo\Modules\ControlBoard\Controllers;

use Espo\Core\Api\Request;

class ControlBoard extends \Espo\Core\Templates\Controllers\Base
{
    public function getActionListDataCards(Request $request): object
    {
        if (!$this->getAcl()->check($this->name, 'read')) {
            throw new Forbidden("No read access for {$this->name}.");
        }
        // get the collection parameters from the front-end request
        $searchParams = $this->searchParamsFetcher->fetch($request);
        $result = $this->getRecordService()->getListDataCards($searchParams);
        return (object) [
            'total' => $result->total,
            'list' => $result->collection->getValueMapList(),
            'additionalData' => $result->additionalData
        ];
    }
}