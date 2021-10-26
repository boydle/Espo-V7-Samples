<?php

namespace Espo\Modules\MyExt\Controllers;

use Espo\Core\Api\Request;

class MyController extends \Espo\Core\Controllers\Record
{
    public function postActionAbc(Request $request): bool //return type is optional and can be bool, array, StdClass, other?
    {
		$data = $request->getParsedBody();
		$params = $request->getRouteParams();
		//do stuff
    }

}
