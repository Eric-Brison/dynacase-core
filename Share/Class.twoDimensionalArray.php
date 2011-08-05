<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * A two dim struct
 *
 * This struct gives some helpers to manipulate rows and columns (addRow, addColumn, insertColumn etc...)
 *
 * @author anakeen
 *
 */
class TwoDimensionStruct
{
    
    private $x = array();
    private $y = array();
    
    private $data = array();
    
    private $emptyValue = "";
    private $errorMessage = array();
    /**
     * Construct a two dim object
     *
     * @param array $data two dim array to init the object
     *
     * @return TwoDimensionStruct
     */
    public function __construct(Array $data = null)
    {
        if (!is_null($data)) {
            foreach ($data as $y => $column) {
                if (is_array($column)) {
                    foreach ($column as $x => $value) {
                        $this->setValue($x, $y, $value, true);
                    }
                } else {
                    $this->addErrorMessage(sprintf("The array have to be a two dimensional array for all the column"));
                }
            }
        }
        return $this;
    }
    /**
     * Get the x row
     *
     * @param int $x number of the row
     *
     * @return array|NULL
     */
    public function getRow($x)
    {
        if (isset($this->y[$x])) {
            $xUUID = $this->x[$x];
            $row = array();
            foreach ($this->y as $key => $yUUID) {
                if (isset($this->data[$xUUID]) && isset($this->data[$xUUID][$yUUID])) {
                    $row[$key] = $this->data[$xUUID][$yUUID];
                } else {
                    $row[$key] = $this->emptyValue;
                }
            }
            return $row;
        } else {
            $this->addErrorMessage(sprintf("Unable to get row $s out of the border", $x));
            return null;
        }
    }
    /**
     * Set the values for a row
     *
     * @param int $x number of the row
     * @param array $row values
     * @param boolean $force force to add if the row is bigger than the column size or $x > the row number
     *
     * @return TwoDimensionStruct|NULL
     */
    public function setRow($x, Array $row, $force = false)
    {
        if ((count($row) < count($this->y)) || $force == true) {
            foreach ($row as $key => $value) {
                if (is_null($this->setValue($x, $key, $value, $force))) {
                    return null;
                }
            }
            return $this;
        } else {
            $this->addErrorMessage(sprintf("Unable to set a row bigger than the column size (%s < %s)", count($this->y) , count($row)));
            return null;
        }
    }
    /**
     * Insert a row at $x
     *
     * @param int $x number of the row
     * @param array $row values
     * @param boolean $force force to add if the row is bigger than the column size
     *
     * @return TwoDimensionStruct|NULL
     */
    public function insertRow($x, Array $row = array() , $force = false)
    {
        if ($x < count($this->x) && ((count($row) < count($this->y)) || $force == true)) {
            array_splice($this->x, $x, 0, array(
                uniqid()
            ));
            $this->x = array_values($this->x);
            return $this->setRow($x, $row, $force);
        } else {
            $this->addErrorMessage(sprintf("Unable to set a row bigger than the column size (%s < %s)", count($this->y) , count($row)));
            return null;
        }
    }
    /**
     * Add a row at the end of the struct or
     *
     * @param array $row values
     * @param int $x number of the row
     *
     * @return TwoDimensionStruct|NULL
     */
    public function addRow(Array $row = array() , $x = null)
    {
        if (is_null($x)) {
            return $this->setRow(count($this->x) , $row, true);
        } elseif ($x >= count($this->x)) {
            return $this->setRow($x, $row, true);
        } else {
            $this->addErrorMessage(sprintf("Use insert row to insert a row"));
            return null;
        }
    }
    /**
     * delete a row
     *
     * @param int $x number of the row
     *
     * @return TwoDimensionStruct|NULL
     */
    public function deleteRow($x)
    {
        if (isset($this->x[$x])) {
            unset($this->x[$x]);
            $this->x = array_values($this->x);
            return $this;
        } else {
            $this->addErrorMessage(sprintf("Unable to delete row $x out of the border", $x));
            return null;
        }
    }
    /**
     * Set a column values
     *
     * @param int $y number of the column
     * @param array $column values
     * @param boolean $force force to add if the column is bigger than the row size
     *
     * @return TwoDimensionStruct|NULL
     */
    public function setColumn($y, Array $column, $force = false)
    {
        if ((count($column) < count($this->x)) || $force == true) {
            foreach ($column as $key => $value) {
                if (is_null($this->setValue($key, $y, $value, $force))) {
                    return null;
                }
            }
            return $this;
        } else {
            $this->addErrorMessage(sprintf("Unable to set a column bigger than the row size (%s < %s)", count($this->x) , count($column)));
            return null;
        }
    }
    /**
     * Insert a column
     *
     * @param int $y number of the column
     * @param array $column values
     * @param boolean $force force the add
     *
     * @return TwoDimensionStruct|NULL
     */
    public function insertColumn($y, Array $column = array() , $force = false)
    {
        if ($y < count($this->y)) {
            array_splice($this->y, $y, 0, array(
                uniqid()
            ));
            $this->y = array_values($this->y);
            return $this->setColumn($y, $column, $force);
        } else {
            $this->addErrorMessage(sprintf("Unable to set a row bigger than the column size (%s < %s)", count($this->y) , count($row)));
            return null;
        }
    }
    /**
     * Add a column
     *
     * @param array $column values
     * @param int $y number of the column
     *
     * @return TwoDimensionStruct|NULL
     */
    public function addColumn(Array $column = array() , $y = null)
    {
        if (is_null($y)) {
            return $this->setColumn(count($this->y) , $column, true);
        } elseif ($y >= count($this->y)) {
            return $this->setColumn($y, $column, true);
        } else {
            $this->addErrorMessage(sprintf("Use insert column to insert a column"));
            return null;
        }
    }
    /**
     * Delete the column
     *
     * @param int $y number of the column
     *
     * @return boolean|NULL
     */
    public function deleteColumn($y)
    {
        if (isset($this->y[$y])) {
            unset($this->y[$y]);
            $this->y = array_values($this->y);
            return true;
        } else {
            $this->addErrorMessage(sprintf("Unable to delete column $y out of the border", $y));
            return null;
        }
    }
    /**
     * get a column
     *
     * @param int $y number of the column
     *
     * @return array|NULL
     */
    public function getColumn($y)
    {
        if (isset($this->y[$y])) {
            $yUUID = $this->y[$y];
            $column = array();
            foreach ($this->x as $key => $xUUID) {
                if (isset($this->data[$xUUID]) && isset($this->data[$xUUID][$yUUID])) {
                    $column[$key] = $this->data[$xUUID][$yUUID];
                } else {
                    $column[$key] = $this->emptyValue;
                }
            }
            return $column;
        } else {
            $this->addErrorMessage(sprintf("Unable to get column $y out of the border", $y));
            return null;
        }
    }
    /**
     * Set a value
     *
     * @param int $x number of the row
     * @param int $y number of the column
     * @param string $value value to set
     * @param boolean $force force the add if x and y are outside the array
     *
     * @return NULL|TwoDimensionStruct
     */
    public function setValue($x, $y, $value, $force = false)
    {
        if (isset($this->y[$y]) && isset($this->x[$x])) {
            $this->data[$this->x[$x]][$this->y[$y]] = $value;
        } elseif ($force) {
            if (!isset($this->x[$x])) {
                for ($i = count($this->x); $i <= $x; $i++) {
                    $this->x[$i] = uniqid();
                }
            }
            if (!isset($this->y[$y])) {
                for ($i = count($this->y); $i <= $y; $i++) {
                    $this->y[$i] = uniqid();
                }
            }
            $this->data[$this->x[$x]][$this->y[$y]] = $value;
        } else {
            $this->addErrorMessage(sprintf("Unable to set x : $s, y :$s, value : $s", $x, $y, $value));
            return null;
        }
        return $this;
    }
    /**
     * Get a value
     *
     * @param int $x number of the row
     * @param int $y number of the column
     *
     * @return string|NULL
     */
    public function getValue($x, $y)
    {
        if (isset($this->y[$y]) && isset($this->x[$x])) {
            $value = $this->data[$this->x[$x]][$this->y[$y]];
            return is_null($value) ? $this->emptyValue : $value;
        } elseif (count($x) >= $x && count($y) >= $y) {
            return $this->emptyValue;
        } else {
            $this->addErrorMessage(sprintf("Unable to get x : $s, y :$s out of the border", $x, $y));
            return null;
        }
    }
    /**
     * Get the two dim array
     *
     * @return array
     */
    public function getArray()
    {
        $nbX = count($this->x);
        $nbY = count($this->y);
        
        $returnArray = array();
        for ($x = 0; $x < $nbX; $x++) {
            $returnArray[$x] = array();
            for ($y = 0; $y < $nbY; $y++) {
                $returnArray[$x][$y] = $this->getValue($x, $y);
            }
        }
        return $returnArray;
    }
    /**
     * Get the default empty value
     *
     * @return the $emptyValue
     */
    public function getEmptyValue()
    {
        return $this->emptyValue;
    }
    /**
     * Set a default emptyValue
     *
     * @param field_type $emptyValue
     *
     * @return TwoDimensionStruct
     */
    public function setEmptyValue($emptyValue)
    {
        $this->emptyValue = $emptyValue;
        return $this;
    }
    /**
     * Add an error message
     *
     * @param string $error
     */
    private function addErrorMessage($error)
    {
        $this->errorMessage[] = $error;
    }
    /**
     * Get the last error message
     *
     * @return string|NULL
     */
    public function getLastErrorMessage()
    {
        if (count($this->errorMessage)) {
            $lastMessage = end($this->errorMessage);
            reset($this->errorMessage);
            return $lastMessage;
        }
        return null;
    }
    /**
     * Get all the array message
     *
     * @return array
     */
    public function getAllErrorMessages()
    {
        return $this->errorMessage;
    }
}
?>