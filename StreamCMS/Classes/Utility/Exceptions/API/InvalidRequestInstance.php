<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Exceptions\API;

/**
 * Class InvalidRequestInstance
 * @package StreamCMS\Utility\Exceptions\API
 * Thrown when we try to pass anything but a StreamCMSRequest into our APIStrategy
 */
class InvalidRequestInstance extends \Exception
{
}