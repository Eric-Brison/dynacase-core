<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generation of PHP Document classes
 *
 * @author  Anakeen
 * @package FDL
 * @subpackage
 */
/**
 */

namespace Dcp;

class FamilyAbsoluteOrder
{
    const firstOrder = "::first";
    const autoOrder = "::auto";
    /**
     * @param array $relativeOrders
     * @param int   $familyId
     *
     * @brief input relativeOrders is a linear Array
     *        (
     *        [0] => Array
     *        (
     *        [id] => tst_bc500
     *        [parent] =>
     *        [family] => 52973
     *        [prev] => ::first
     *        )
     *        [1] => Array
     *        (
     *        [id] => tst_b550
     *        [parent] => tst_bc500
     *        [family] => 52973
     *        [prev] => ::first
     *        )
     *        ....
     * @return array
     */
    public static function getAbsoluteOrders($relativeOrders, $familyId)
    {
        // self::debug($relativeOrders, "");
        // First get all family ancestors
        $familyIds = self::getFamilyInherits($familyId);
        $tree = [];
        foreach ($familyIds as $familyId) {
            $subTree = [];
            foreach ($relativeOrders as $relativeOrder) {
                if ($relativeOrder["family"] == $familyId) {
                    $subTree[$relativeOrder["id"]] = $relativeOrder;
                }
            }
            self::updateAttributeTree($tree, $subTree);
        }
        self::checkTree($tree);
        
        $linear = self::linearOrderTree($tree);
        foreach ($relativeOrders as $relativeOrder) {
            if (array_search($relativeOrder["id"], $linear) === false) {
                $linear[] = $relativeOrder["id"];
            }
            if ($relativeOrder["parent"] && array_search($relativeOrder["parent"], $linear) === false) {
                $linear[] = $relativeOrder["parent"];
            }
        }
        return $linear;
    }
    /**
     * Complete information (prev) when absolute numeric order is done
     *
     * @param array $attributes
     * @param       $familyId
     */
    public static function completeForNumericOrder(array & $attributes, $familyId)
    {
        $familyIds = self::getFamilyInherits($familyId);
        $famAttribute = [];
        foreach ($familyIds as $familyId) {
            
            foreach ($attributes as $oneAttribute) {
                if ($oneAttribute["family"] == $familyId) {
                    $famAttribute[$oneAttribute["id"]] = $oneAttribute;
                }
            }
            self::completeForNumericOrderByFamilyLevel($famAttribute);
            foreach ($famAttribute as $oneAttribute) {
                $key = $oneAttribute["id"] . "/" . $oneAttribute["family"];
                $attributes[$key] = $oneAttribute;
            }
        }
        
        uasort($attributes, function ($a, $b)
        {
            return self::sortAttributeCallBackfunction($a, $b);
        });
    }
    /**
     * Complete information (previous) for a attribute set of a given family
     *
     * @param array $familyAttributes
     */
    protected static function completeForNumericOrderByFamilyLevel(array & $familyAttributes)
    {
        foreach ($familyAttributes as & $anAttribute) {
            $anAttribute["structLevel"] = self::getStructLevel($anAttribute["id"], $familyAttributes);
            if ($anAttribute["numOrder"] === 0) {
                $anAttribute["numOrder"] = self::getNumericOrder($anAttribute["id"], $familyAttributes);
            }
            $anAttribute["familyLevel"] = self::getFamilyLevel($anAttribute["family"]);
        }
        
        uasort($familyAttributes, function ($a, $b)
        {
            return self::sortAttributeCallBackfunction($a, $b);
        });
        foreach ($familyAttributes as & $anAttribute) {
            if (!$anAttribute["prev"]) {
                $anAttribute["prev"] = self::getPrevious($anAttribute["id"], $familyAttributes);
            }
        }
        uasort($familyAttributes, function ($a, $b)
        {
            if ($a["numOrder"] > $b["numOrder"]) {
                return 1;
            }
            if ($a["numOrder"] < $b["numOrder"]) {
                return -1;
            }
            return 0;
        });
    }
    /** @noinspection PhpUnusedPrivateMethodInspection
     * @param array  $r
     * @param string $text
     */
    private static function debug(array $r, $text = "")
    {
        printf("\n========= %s======== \n", $text);
        
        $first = current($r);
        printf("%20s|", "index");
        foreach (array_keys($first) as $h) {
            
            printf("%20s|", $h);
        }
        print "\n";
        
        foreach ($r as $k => $sr) {
            printf("%20s|", $k);
            foreach ($sr as $item) {
                printf("%20s|", $item);
            }
            printf("\n");
        }
    }
    
    protected static function sortAttributeCallBackfunction($a, $b)
    {
        if ($a["structLevel"] > $b["structLevel"]) {
            return 1;
        }
        if ($a["structLevel"] < $b["structLevel"]) {
            return -1;
        }
        if ($a["numOrder"] > $b["numOrder"]) {
            return 1;
        }
        if ($a["numOrder"] < $b["numOrder"]) {
            return -1;
        }
        return 0;
    }
    /**
     * Get family level of inheritance of family (0 means top family)
     *
     * @param int $famid
     *
     * @return int
     */
    protected static function getFamilyLevel($famid)
    {
        return count(self::getFamilyInherits($famid));
    }
    /**
     * Get family ancestors
     *
     * @param $familyId
     *
     * @return int[][]
     */
    protected static function getFamilyInherits($familyId)
    {
        static $inherits = [];
        
        if (empty($inherits[$familyId])) {
            $tfromid[] = $familyId;
            $childfamilyId = $familyId;
            while ($childfamilyId = getFamFromId("", $childfamilyId)) {
                $tfromid[] = $childfamilyId;
            }
            $inherits[$familyId] = array_reverse($tfromid);
        }
        return $inherits[$familyId];
    }

    /**
     * Get structure level for an attribute (0 means top level  - for tabs or frame)
     *
     * @param string $attrid
     * @param array  $attributes
     *
     * @return int
     * @throws \Dcp\Core\Exception
     */
    protected static function getStructLevel($attrid, array $attributes)
    {
        $level = 0;
        while (!empty($attributes[$attrid]["parent"])) {
            $attrid = $attributes[$attrid]["parent"];
            $level++;
            if ($level > 5) {
                throw new \Dcp\Core\Exception("ATTR0214", $attrid);
            }
        }
        return $level;
    }
    /**
     * Get previous attribute order when only numeric order is done
     *
     * @param string $attrid
     * @param array  $sortedAttributes
     *
     * @return string
     */
    protected static function getPrevious($attrid, array $sortedAttributes)
    {
        $attr = $sortedAttributes[$attrid];
        $familyLevel = $attr["familyLevel"];
        $structLevel = $attr["structLevel"];
        $parent = $attr["parent"];
        $previous = self::firstOrder;
        foreach ($sortedAttributes as $attribute) {
            if ($attribute["id"] === $attrid) {
                return $previous;
            }
            if ($attribute["familyLevel"] <= $familyLevel && $attribute["structLevel"] === $structLevel) {
                if ($attribute["parent"] === $parent) {
                    $previous = $attribute["id"];
                } else {
                    $previous = self::autoOrder;
                }
            }
        }
        return $previous;
    }
    /**
     * Compute numeric order when no order id done
     *
     * @param string $attrid
     * @param array  $attributes
     *
     * @return int|float
     */
    protected static function getNumericOrder($attrid, array $attributes)
    {
        $num = 0;
        foreach ($attributes as $attribute) {
            if ($attribute["parent"] === $attrid) {
                if ($attribute["numOrder"] === 0) {
                    $attribute["numOrder"] = self::getNumericOrder($attribute["id"], $attributes);
                }
                
                if ($num === 0) {
                    $num = $attribute["numOrder"] - 0.5;
                } else {
                    $num = min($num, $attribute["numOrder"] - 0.5);
                }
            }
        }
        
        return $num;
    }
    /**
     * Linearize tree to be a flat array
     *
     * @param array $tree
     *
     * @return array
     */
    protected static function linearOrderTree(array $tree)
    {
        $linearOrder = [];
        foreach ($tree as $node) {
            if ($node["id"]) {
                $linearOrder[] = $node["id"];
                $linearOrder = array_merge($linearOrder, self::linearOrderTree($node["content"]));
            }
        }
        return $linearOrder;
    }
    /**
     * Get childs for a node
     *
     * @param  array  $tree
     * @param  string $attrid
     * @param array   $default
     *
     * @return array
     */
    protected static function getTreeContent(array $tree, $attrid, $default = [])
    {
        foreach ($tree as $node) {
            if ($node["id"] === $attrid) {
                return $node["content"];
            }
            $content = self::getTreeContent($node["content"], $attrid, false);
            if ($content !== false) {
                return $content;
            }
        }
        return $default;
    }
    /**
     * Delete node : return deleted node, false if not found
     *
     * @param array $tree
     * @param       $attrid
     *
     * @return bool|array
     */
    protected static function deleteNode(array & $tree, $attrid)
    {
        foreach ($tree as $kNode => & $node) {
            if ($node["id"] === $attrid) {
                $dNode = $node;
                unset($tree[$kNode]);
                return $dNode;
            }
            $content = self::deleteNode($node["content"], $attrid);
            if ($content !== false) {
                return $content;
            }
        }
        return false;
    }
    /**
     * Add items to the tree
     *
     * @param array $tree
     * @param array $onlyFamilyTree
     */
    protected static function updateAttributeTree(array & $tree, array $onlyFamilyTree)
    {
        foreach ($onlyFamilyTree as $attrid => $order) {
            self::updateAttributeTreeItem($tree, $attrid, $onlyFamilyTree);
        }
    }
    /**
     * Verify that all attributes are well places in the tree
     *
     * @param array  $tree
     * @param string $parent
     *
     * @throws Exception
     *
     */
    protected static function checkTree(array & $tree, $parent = "")
    {
        
        foreach ($tree as $child) {
            if (!isset($child["parent"])) {
                $child["parent"] = "";
            }
            
            if ($child["parent"] !== $parent) {
                throw new Exception("ATTR0213", $child["id"], $parent, $child["parent"]);
            }
            
            if ($child["content"]) {
                self::checkTree($child["content"], $child["id"]);
            }
        }
    }
    /**
     * add single item to the tree
     *
     * @param array  $tree
     * @param        $attrid
     * @param  array $orders
     */
    protected static function updateAttributeTreeItem(array & $tree, $attrid, &$orders)
    {
        $parent = (!empty($orders[$attrid]["parent"])) ? $orders[$attrid]["parent"] : false;
        $prev = (!empty($orders[$attrid]["prev"])) ? $orders[$attrid]["prev"] : false;
        
        if (empty($orders[$attrid]["isInTree"])) {
            // $node = ["id" => $attrid, "content" => self::getTreeContent($tree, $attrid)];
            if (!empty($orders[$attrid]["id"])) {
                $node = self::deleteNode($tree, $attrid); // To Move it
                if ($node === false) {
                    $node = ["id" => $attrid, "content" => []];
                }
            } else {
                $node = ["id" => $attrid, "content" => self::getTreeContent($tree, $attrid) ];
            }
            
            if (!$parent) {
                if ($prev === self::autoOrder) {
                    $tree[] = $node;
                } elseif ($prev === self::firstOrder) {
                    array_unshift($tree, $node);
                } elseif ($prev) {
                    if (empty($orders[$prev]["isInTree"])) {
                        self::updateAttributeTreeItem($tree, $prev, $orders);
                    }
                    
                    self::insertAfter($tree, $prev, $node);
                }
            } else {
                $node["parent"] = $parent;
                if (empty($orders[$parent]["isInTree"])) {
                    self::updateAttributeTreeItem($tree, $parent, $orders);
                }
                if ($prev === self::autoOrder) {
                    self::appendNode($tree, $parent, $node);
                } elseif ($prev === self::firstOrder) {
                    self::prependNode($tree, $parent, $node);
                } elseif ($prev) {
                    if (empty($orders[$prev]["isInTree"])) {
                        self::updateAttributeTreeItem($tree, $prev, $orders);
                    }
                    self::insertAfter($tree, $prev, $node);
                }
            }
            $orders[$attrid]["isInTree"] = true;
        }
    }
    
    protected static function insertAfter(array & $array, $ref, array & $new)
    {
        foreach ($array as $k => & $v) {
            if ($v["id"] == $ref) {
                array_splice($array, $k + 1, 0, [$new]);
                return true;
            }
            if (self::insertAfter($v["content"], $ref, $new)) {
                return true;
            }
        }
        return false;
    }
    
    protected static function prependNode(array & $array, $parent, array & $new)
    {
        foreach ($array as $k => & $v) {
            if ($v["id"] == $parent) {
                array_unshift($v["content"], $new);
                return true;
            }
            if (self::prependNode($v["content"], $parent, $new)) {
                return true;
            }
        }
        return false;
    }
    
    protected static function appendNode(array & $array, $parent, array & $new)
    {
        foreach ($array as $k => & $v) {
            if ($v["id"] == $parent) {
                $v["content"][] = $new;
                return true;
            }
            if (self::appendNode($v["content"], $parent, $new)) {
                return true;
            }
        }
        return false;
    }
}
