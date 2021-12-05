<?php
namespace Destiny\Common\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Secure {

    /**
     * @var string[]
     */
    public $roles;

    public function __construct(array $params = null) {
        if (!empty($params)) {
            $this->roles = $params ['value'];
        }
    }

}