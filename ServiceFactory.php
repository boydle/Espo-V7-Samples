<?php

namespace Espo\Modules\ListPlus\Controllers;

use Espo\Core\{
Container,
Api\Request
};

class ListPlusAdmin
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function postActionCreateScopeBackEndChanges(Request $request) {
        // extract the payload from the request object
        $dataObj = $request->getParsedBody();
        // invoke service class to execute the instructions passing the payload as object
        $result = $this->container->get('serviceFactory')->create('ListPlusAdmin')->createScopeBackEndChanges($dataObj);
        return $result;
    }
}