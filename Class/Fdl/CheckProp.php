<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Checking family properties parameters
 * @class CheckProp
 * @brief Check PROP import lines
 * @see ErrorCodePRFL
 */
class CheckProp extends CheckData
{
    /**
     * Property's name
     * @var string
     */
    public $propName = '';
    /**
     * Parameters' (pName, pValue) tuples
     * @var array
     */
    public $parameters = array();

    /**
     *
     */
    private static $parameterClassMap = array(
        'sort' => 'sortProperties'
    );
    /**
     * Check validity of a PROP import line
     *
     * @param array $data
     * @param \DocFam $docfam
     * @return CheckProp
     */
    public function check(array $data, &$docfam = null)
    {
        array_shift($data);

        if (count($data) < 1) {
            $this->addError(ErrorCode::getError('PROP0100'));
            return $this;
        }
        $propName = array_shift($data);
        if (!CheckAttr::checkAttrSyntax($propName)) {
            $this->addError(ErrorCode::getError('PROP0101', $propName));
            return $this;
        }

        if (count($data) < 1) {
            $this->addError(ErrorCode::getError('PROP0200'));
            return $this;
        }
        $parameters = array();
        while ($value = array_shift($data)) {
            $tuple = self::checkValueSyntax($value);
            if ($tuple === false) {
                $this->addError(ErrorCode::getError('PROP0201', $value));
                return $this;
            }
            if (!self::checkParameterClassKey($tuple['name'])) {
                $this->addError(ErrorCode::getError('PROP0202', $tuple['name']));
                return $this;
            }
            $parameters []= $tuple;
        }

        $this->propName = $propName;
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Check validity of a parameter's tuple string value ("<pName>=<pValue>")
     *
     * @static
     * @param string $value The parameter's tuple value
     * @return array|bool boolean false on error, or the parsed parameter tuple on success
     */
    public static function checkValueSyntax($value) {
        if(!preg_match('/^(?P<name>[a-z]{1,63})=(?P<value>.*)$/i', $value, $m)) {
            return false;
        }
        return array(
            'name' => $m['name'],
            'value' => $m['value']
        );
    }

    /**
     * Check that the parameter's name has a valid and known class name
     *
     * @static
     * @param string $pName The parameter's name
     * @return bool boolean false if not valid, or boolean true if valid
     */
    public static function checkParameterClassKey($pName) {
        return in_array($pName, array_keys(self::$parameterClassMap));
    }

    /**
     * Helper method to get the class name for a given parameter's name
     * @static
     * @param null $key
     * @return array
     */
    public static function getParameterClassMap($key = null) {
        if ($key === null) {
            return self::$parameterClassMap;
        }
        return self::$parameterClassMap[$key];
    }
}
