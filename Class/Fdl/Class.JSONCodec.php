<?php
/*
 * @author Anakeen
 * @package FDL
 */
/**
 * The JSONCodec class simplify JSON encoding/decoding errors handling
 * by throwing Dcp\Exceptions, with corresponding JSON error constant,
 * instead of returning null on errors (which could be mistaken with
 * valid JSON null values for example).
 */
class JSONCodec
{
    private $json_errors = null;
    /**
     * Decode JSON string
     *
     * @param string $json see $json from PHP's json_decode()
     * @param bool $assoc see $assoc from PHP's json_decode()
     * @param int $depth see $depth from PHP's json_decode()
     * @return mixed|null returns the resulting PHP structure
     * @throws Dcp\Exception Exception is throwned on error
     */
    public function decode($json, $assoc = false, $depth = 512)
    {
        /* Circumvent PHP bug 54484 <https://bugs.php.net/bug.php?id=54484> */
        if ($json === '' || $json === null) {
            return null;
        }
        $o = json_decode($json, $assoc, $depth);
        if ($o === null) {
            $errCode = json_last_error();
            if ($errCode == JSON_ERROR_NONE) {
                return null;
            }
            throw new Dcp\Exception($this->getErrorMsg($errCode));
        }
        return $o;
    }
    /**
     * Encode value into JSON string
     *
     * @param mixed $value see $value from PHP's json_encode()
     * @param int $options see $options from PHP's json_encode()
     * @return string returnes the resulting JSON string
     * @throws Dcp\Exception Exception is throwned on error
     */
    public function encode($value, $options = 0)
    {
        $str = json_encode($value, $options);
        if ($str === false) {
            $errCode = json_last_error();
            throw new Dcp\Exception($this->getErrorMsg($errCode));
        }
        return $str;
    }
    /**
     * Get the JSON error constant name for the given JSON error code.
     *
     * @param int $errCode The JSON last error code (from json_last_error())
     * @return string The JSON error constant name (e.g. "JSON_ERROR_SYNTAX")
     */
    private function getErrorMsg($errCode)
    {
        if ($this->json_errors === null) {
            $constants = get_defined_constants(true);
            foreach ($constants["json"] as $name => $value) {
                if (!strncmp($name, "JSON_ERROR_", 11)) {
                    $this->json_errors[$value] = $name;
                }
            }
        }
        return $this->json_errors[$errCode];
    }
}
