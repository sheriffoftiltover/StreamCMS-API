<?php
declare(strict_types=1);

namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class PrivateKey {

    public $names = [];

    public function __construct(array $params = null) {
        if (!empty($params)) {
            $this->names = $params ['value'];
        }
    }

}