<?php
declare(strict_types=1);

namespace Destiny\Common;

class ViewModel
{

    /**
     * List of properties
     *
     * @var array
     */
    protected $vars = [];

    /**
     * Set the variables via the constructor
     *
     * @param array $params
     */
    public function __construct(array $params = null)
    {
        if (!empty ($params)) {
            foreach ($params as $name => $value) {
                $this->vars [$name] = $value;
            }
        }
    }

    /**
     * Return all the views data
     *
     * @return array
     */
    public function getData()
    {
        return $this->vars;
    }

    /**
     * get a variable value by name
     *
     * @param string $name
     * @param mix $value
     */
    public function __get($name)
    {
        return (isset ($this->vars [$name])) ? $this->vars [$name] : null;
    }

    /**
     * Set a variable value
     *
     * @param string $name
     * @param mix $value
     */
    public function __set($name, $value)
    {
        $this->vars [$name] = $value;
        return $value;
    }

    /**
     * Check if a var isset
     *
     * @param string $name
     */
    public function __isset($name)
    {
        return isset ($this->vars [$name]);
    }

}

