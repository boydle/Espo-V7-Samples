<?php

namespace Espo\Modules\ListPlus\Controllers;

use Espo\Core\{
Di,
Api\Request
};

class ListPlusAdmin implements Di\ServiceFactoryAware
{
    use Di\ServiceFactorySetter;

    public function postActionCreateScopeBackEndChanges(Request $request) {
        // extract the payload from the request object
        $dataObj = $request->getParsedBody();
        // invoke service class to execute the instructions passing the payload as object
        $result = $this->serviceFactory->create('ListPlusAdmin')->createScopeBackEndChanges($dataObj);
        return $result;
    }
}