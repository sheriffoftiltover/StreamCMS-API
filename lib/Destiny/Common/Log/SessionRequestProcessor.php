<?php
declare(strict_types=1);

namespace Destiny\Common\Log;

use ArrayAccess;
use Destiny\Common\Session;
use UnexpectedValueException;

class SessionRequestProcessor
{

    protected $credentials;
    protected $serverData;

    /**
     * @param mixed $serverData
     *          object w/ SessionCredentials that provides access to the session credentials
     */
    public function __construct($serverData = null)
    {
        if (null === $serverData) {
            $this->serverData =& $_SERVER;
        } elseif (is_array($serverData) || $serverData instanceof ArrayAccess) {
            $this->serverData = $serverData;
        } else {
            throw new UnexpectedValueException ('$serverData must be an array or object implementing ArrayAccess.');
        }
    }

    /**
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {

        // Real IP
        if (!empty ($this->serverData ['HTTP_CLIENT_IP'])) {
            // check ip from share internet
            $ipAddress = $this->serverData ['HTTP_CLIENT_IP'];
        } elseif (!empty ($this->serverData ['HTTP_X_FORWARDED_FOR'])) {
            // to check ip is pass from proxy
            $ipAddress = $this->serverData ['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty ($this->serverData ['REMOTE_ADDR'])) {
            $ipAddress = $this->serverData ['REMOTE_ADDR'];
        } else {
            $ipAddress = null;
        }

        $record ['extra'] = array_merge($record ['extra'], ['realIp' => $ipAddress]);

        $session = Session::instance();
        if (!empty ($session)) {

            $record ['extra'] = array_merge($record ['extra'], ['sessionId' => $session->getSessionId()]);

            $credentials = $session->getCredentials()->getData();
            if (!empty ($credentials)) {
                $record ['extra'] = array_merge($record ['extra'], ['credentials' => $credentials]);
            }
        }

        return $record;
    }
}