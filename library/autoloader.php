<?php
class SR_Autoloader
{
    public $namespace;
    public $basePath;

    public function __construct($basepath, $namespace)
    {
        $this->namespace = $namespace;
        $this->basePath = $basepath;

        spl_autoload_register(array(&$this, 'load'));
    }

    public function load($name)
    {
        if (strpos($name, $this->namespace) !== false) {
            $localName = substr($name, strlen($this->namespace) + 1);
            if (include_once(strtolower($localName).'.class.php')) {
                return true;
            }
        }

        return false;
    }

    protected function _addTrailing($string, $char)
    {
        return rtrim($string, $char).$char;
    }
}
