<?php

/**
 * ConfigrationStore handles (de)serialization of the
 * DocFam->configuration property from/to JSON into a array() struct
 * called "store".
 */
class ConfigurationStore
{
    private $store = array();

    /**
     * Load a configuration JSON string into the corresponding array() struct
     *
     * @param $str The configuration's JSON string
     * @return bool boolean true on success, or boolean false on error
     */
    public function load($str) {
        $codec = new JSONCodec();
        try {
            $store = $codec->decode($str, true);
        } catch (Exception $e) {
            return false;
        }
        $this->store = $store;
        return true;
    }

    /**
     * Reset the array() struct
     */
    public function reset() {
        $this->store = array();
    }

    /**
     * Add a configuration parameter into the store
     *
     * @param $class The parameter's class
     * @param $propName The property's name
     * @param $pName The parameter's name
     * @param $pValue The parameter's value
     * @return ConfigurationStore
     */
    public function add($class, $propName, $pName, $pValue) {
        if (!isset($this->store[$class])) {
            $this->store[$class] = array();
        }
        if (!isset($this->store[$class][$propName])) {
            $this->store[$class][$propName] = array();
        }
        $this->store[$class][$propName][$pName] = $pValue;
        return $this;
    }

    /**
     * Get a configuration parameter from the store
     *
     * @param $class The parameter's class
     * @param null $propName The property's name. If $propName is null, then it will lookup all parameters matching the $pName
     * @param null $pName The parameter's name. If $pName is null, then it will lookup all parameters matching the $propName
     * @return array|null null on error, or array() containing the queried properties' parameters
     */
    public function get($class, $propName = null, $pName = null) {
        if (!isset($this->store[$class])) {
            return null;
        }
        if ($propName === null) {
            if ($pName === null) {
                return $this->store[$class];
            } else {
                $res = array();
                foreach ($this->store[$class] as $propName => $elmt) {
                    if (isset($elmt[$pName])) {
                        $res[$propName] = $elmt;
                    }
                }
                return $res;
            }
        } else {
            if ($pName === null) {
                if (isset($this->store[$class][$propName])) {
                    return $this->store[$class][$propName];
                } else {
                    return null;
                }
            } else {
                if (isset($this->store[$class]) && isset($this->store[$class][$propName])) {
                    return $this->store[$class][$propName][$pName];
                } else {
                    return null;
                }
            }
        }
        return null;
    }

    /**
     * Returns the JSON text serialization of the store
     *
     * @return bool|string boolean false on error, or JSON string on success
     */
    public function getText() {
        $codec = new JSONCodec();
        try {
            $str = $codec->encode($this->store);
        } catch (Exception $e) {
            return false;
        }
        return $str;
    }
}
