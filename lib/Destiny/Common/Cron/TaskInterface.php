<?php
declare(strict_types=1);

namespace Destiny\Common\Cron;

use Exception;

interface TaskInterface {

    /**
     * @return mixed|void
     *
     * @throws Exception
     */
    function execute();

}