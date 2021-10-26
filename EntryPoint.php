<?php

namespace Espo\Modules\MyExt\EntryPoints;

use Espo\Core\Api\Request;
use Espo\Core\Api\Response;

class EpName extends \Espo\Core\EntryPoints\Base
{
    public static $authRequired = true;

    // default action
    public function run(Request $request, Response $response): void
    {
		//STUFF
    }
}
