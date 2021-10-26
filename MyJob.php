<?php

namespace Espo\Modules\MyExt\Jobs;

use Espo\Core\Job\JobDataLess;

class MyJob implements JobDataLess
{
    public function run(): void
    {
        //do stuff
    }
}
