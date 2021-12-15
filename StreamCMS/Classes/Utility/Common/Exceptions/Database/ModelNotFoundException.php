<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\Exceptions\Database;

class ModelNotFoundException extends \Exception
{
    public function __construct(string $entityName, array $criteria, int $code = 255, ?\Exception $previous = null)
    {
        $message = "{$entityName} Not Found";
        $newCriteria = [];
        foreach ($criteria as $key => $val) {
            $newCriteria[] = "{$key}: {$val}";
        }
        $message .= ' with ' . implode(' ', $newCriteria);
        parent::__construct($message, $code, $previous);
    }
}
