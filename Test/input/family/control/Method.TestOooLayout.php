<?php
/**
 * Ooo Layout test
 *
 * @author Anakeen 2010
 * @version $Id: Method.Animal.php,v 1.9 2011/02/01 16:40:08 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package freedom-zoo
 */
/**
 */
/**
 */

/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class __TST_OOOLAYOUT extends Doc
{
    /**
     * @end-method-ignore
     */
    
    function testRepeatMulti($target)
    {
        
        $this->lay->set("TODAY", $this->getTimeDate());
        
        $repeatColor[] = array(
            "COLOR" => "jaune",
            "VEGETABLE" => array(
                "pomme",
                "banane"
            ),
            "VARIETY" => array(
                array(
                    "granny",
                    "fuji",
                    "golden"
                ),
                array(
                    "plantain"
                )
            )
        );
        $repeatColor[] = array(
            "COLOR" => "rouge",
            "VEGETABLE" => array(
                "cerise",
                "fraise"
            ),
            "VARIETY" => array(
                array(
                    "bigarreau",
                    "griotte"
                ),
                array(
                    "mara des bois",
                    "agate",
                    "anabelle"
                )
            )
        );
        $repeatColor[] = array(
            "COLOR" => "vert",
            "VEGETABLE" => array(
                "concombre",
                "cornichon",
                "poivron"
            ),
            "VARIETY" => array(
                array(
                    "gynial",
                    "Cucumis sativus"
                ),
                array(
                    "vert de paris"
                ),
                array(
                    "doux sonar",
                    "jericho"
                )
            )
        );
        
        $this->lay->setRepeatable($repeatColor);
    
    }
    
    function testIfOooLayout($target)
    {
        if (!$this->lay->file) return;
        if (strtolower(get_class($this->lay)) != 'ooolayout') return;
        if ($target !== 'ooo') return;
        $this->lay->eset("TODAY", $this->getTimeDate() );
        
        $t = array();
        for($i = 1; $i < 6; $i++) {
 
            $t[] = array(
                "V_N1" => "Valeur $i",
                "V_T1" => "Titre $i",
                "ODD" => (($i % 2) == 0)
            );
        }
        
        $this->lay->set("HEADER", false);
        $this->lay->set("FOOTER", true);
        $this->lay->setRepeatable($t);
        
        //$this->viewDefaultCard($target);
        //$this->lay->set("V_OOO_IMAGE_ENTETE", $this->lay->get("V_OOO_IMAGE"));
    }
    function testRowOooLayout($target)
    {
        
        $this->lay->set("TODAY", $this->getTimeDate());
        $this->lay->setColumn("NOTE1", array(
            "Do",
            "Ré",
            "Mi"
        ));
        $this->lay->setColumn("NOTE2", array(
            "Fa",
            "Sol"
        ));
       
        $this->viewDefaultCard($target);
    }
   
    
    function testRepeatOooLayout($target)
    {
        
        $this->lay->set("TODAY", $this->getTimeDate());
        
        $t = array();
        for($i = 1; $i < 5; $i++) {
            $sub = array();
            $subBis = array();
            $sub3 = array();
            $sub3Bis = array();
            $sub4 = array();
            $sub4Bis = array();
            for($j = 1; $j < (2 + $i); $j++) {
                $sub[] = ("Sous Valeur $i/$j");
                $subBis[] = ("Bis  $i/$j");
                for($k = 1; $k < ($i + $j + 1); $k++) {
                    $sub3[$j - 1][] = ("Niveau 3 :  $i/$j/$k");
                    $sub3Bis[$j - 1][] = ("Ter $i/$j/$k");
                    for($l = 1; $l < (($i + $j + 1)); $l++) {
                        $sub4[$j - 1][$k - 1][] = ("Niveau 4 :  $i/$j/$k/$l");
                        $sub4Bis[$j - 1][$k - 1][] = ("Quater $i/$j/$k/$l");
                    }
                }
            
            }
            $t[] = array(
                "V_N1" => "Valeur $i",
                "V_T1" => "Titre $i",
                "S_N2bis" => $subBis,
                "S_N2" => $sub,
                "S_N3" => $sub3,
                "S_N3bis" => $sub3Bis,
                "S_N4" => $sub4,
                "S_N4bis" => $sub4Bis
            );
        }
        
        $this->lay->setRepeatable($t);
        
        $this->viewDefaultCard($target);
    }
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
}

/**
 * @end-method-ignore
 */
?>