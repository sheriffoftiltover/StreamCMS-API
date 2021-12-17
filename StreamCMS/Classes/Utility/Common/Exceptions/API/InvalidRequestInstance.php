<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\Exceptions\API;

/**
 * Class InvalidRequestInstance
 * @package StreamCMS\Utility\Common\Exceptions\API
 * Thrown when we try to pass anything but a StreamCMSRequest into our APIStrategy
 */
class InvalidRequestInstance extends \Exception
{
}
