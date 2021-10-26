<?php

namespace Espo\Modules\MyExt\Controllers;

use Espo\Core\Api\Request;

class MyController extends \Espo\Core\Controllers\Record
{
    public function postActionAbc(Request $request): bool
    {
		$data = $request->getParsedBody();
		$params = $request->getRouteParams();
		//do stuff
    }

}
