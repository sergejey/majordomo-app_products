<?php

//$command='череный молотый перец белого лука гель для душа';
require_once(ROOT . "lib/phpmorphy/common.php");
$opts = array(
 'storage' => PHPMORPHY_STORAGE_MEM,
 'predict_by_suffix' => true,
 'predict_by_db' => true,
 'graminfo_as_text' => true,
 );
$dir = ROOT . 'lib/phpmorphy/dicts';
$lang = 'ru_RU';
        try {
            $morphy = new phpMorphy($dir, $lang, $opts);
            $this->morphy =& $morphy;
        } catch (phpMorphy_Exception $e) {
            die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
        }
        $words = explode(' ', $command);
        $base_forms = array();
        $partsOfSpeech=array();
        $f_word=array();
        $totals = count($words);
        for ($is = 0; $is < $totals; $is++) {
            if (preg_match('/^(\d+)$/', $words[$is])) {
                $base_forms[$is] = array($words[$is]);
            } else {
                $Word = mb_strtoupper($words[$is], 'UTF-8');
                $base_forms[$is] = $morphy->getBaseForm($Word);
                $partsOfSpeech[$is] = $morphy->getPartOfSpeech($Word);
                $f_word[$is] = $morphy->getGramInfo($Word);
                $base_forms[$is][]=$words[$is];
            } 
        }


for ($is = 0; $is < $totals; $is++) {
    if ($base_forms[$is][0]=='ВОД') $base_forms[$is][0]='ВОДА';
    if ($partsOfSpeech[$is][0]=='С') {
    
        if (($is+1)<$totals) {
            if ($partsOfSpeech[$is+1][0]=='С') {
        if ($base_forms[$is+1][0]=='МАРКА') {
            $product=$base_forms[$is][0]. ' ' . $words[$is+1] . ' ' . $words[$is+2];
            $is=$is+2;
        }
        else {
            $product=$base_forms[$is][0];
        }
            }
            elseif ($partsOfSpeech[$is+1][0]=='ПРЕДЛ') {
                $product=$base_forms[$is][0] . ' ' . $words[$is+1] . ' ' . $words[$is+2];
                $is=$is+2;
            }
             elseif ($partsOfSpeech[$is+1][0]=='П') {
                $product=$base_forms[$is][0] ;

            }

        }
        else {
            $product=$base_forms[$is][0];
        }
    }
    elseif ($partsOfSpeech[$is][0]=='П') {
        if (($is+1)<$totals) {
            if ($partsOfSpeech[$is+1][0]=='С') {

                if (count($base_forms[$is+1])>2){
                 // выбираем форму согласованную по роду
                 for ($k= 0; $k < count($f_word[$is][0][0]['grammems']); $k++) {
                  $gram=$f_word[$is][0][0]['grammems'][$k];
                  if ($gram=='МР' or $gram=='СР' or $gram=='ЖР') {
                   break;
                  }
                 }
                 $kkk=0;
                 for ($kk= 0; $kk < count($base_forms[$is+1])-1; $kk++) {

                  for ($k= 0; $k < count($f_word[$is+1][$kk][0]['grammems']); $k++) {
                    if ($gram==$f_word[$is+1][$kk][0]['grammems'][$k]) {
                     $kkk=1;
                     break;
                    }
                  }
                  if ($kkk==1) break;
                 }
                $product=$base_forms[$is][0] .' ' . $base_forms[$is+1][$kk] ;
                $is=$is+1; 
                 
                }
                 else {
                $product=$base_forms[$is][0] .' ' . $base_forms[$is+1][0] ;
                $is=$is+1; 
                } 
            }
            elseif ($partsOfSpeech[$is+1][0]=='П') {
                if (($is+2)<$totals) {
                    if ($partsOfSpeech[$is+2][0]=='С') {

                        if (count($base_forms[$is+2])>2){
                            // выбираем форму согласованную по роду
                            for ($k= 0; $k < count($f_word[$is][0][0]['grammems']); $k++) {
                                $gram=$f_word[$is][0][0]['grammems'][$k];
                                if ($gram=='МР' or $gram=='СР' or $gram=='ЖР') break;
                   
                            }
                 
                            $kkk=0;
                            for ($kk= 0; $kk < count($base_forms[$is+2])-1; $kk++) {

                                for ($k= 0; $k < count($f_word[$is+2][$kk][0]['grammems']); $k++) {
                                    if ($gram==$f_word[$is+2][$kk][0]['grammems'][$k]) {
                                        $kkk=1;
                                        break;
                                    }
                                }
                                if ($kkk==1) break;
                            }
                            $product=$base_forms[$is][0] .' ' . $base_forms[$is+1][0] . ' ' . $base_forms[$is+2][$kk] ;
                            $is=$is+2; 
                 
                        }
                        else {
                            $product=$base_forms[$is][0] .' ' . $base_forms[$is+1][0] .' ' . $base_forms[$is+2][0];
                            $is=$is+2; 
                        }
                    }
                    else {
                        $product=$base_forms[$is][0] .' ' . $base_forms[$is+1][0];
                        $is=$is+1;
                    }    
                }
                else {
                    $product=$base_forms[$is][0] .' ' . $base_forms[$is+1][0];
                    $is=$is+1;
                }    
            }                
        }
        else {
            $product=$base_forms[$is][0];
        }    
    }        

    $product = strtolower($product);
     
    if($debugEnabled) debmes('Products produkt:'. $product);
                
    $id=Get_Product_ID( $product);
    if ($id > 0){
        $this->addToList($id);
        if($debugEnabled) debmes('Products produkt '.$product.' found, ID:'. $id);
    }
    Else {
        if($debugEnabled) debmes('Products produkt '.$product.' not found, adding');
        $category_id = Get_Category_ID("Неотсортированные");
        if ($category_id > 0){
            if($debugEnabled) debmes('Products category exiting unknown');
            $this->category_id = $category_id;
        } 
        Else {
            if($debugEnabled) debmes('Products creating unknown');
               $Record = Array();
               $Record['TITLE'] = "Неотсортированные";
               $Record['ID']=SQLInsert('product_categories', $Record);
            $category_id = $Record['ID'];
                            
            if($debugEnabled) debmes('Products produkt '.$product.' adding to created unknown');
        }

           $Record = Array();
           $Record['TITLE'] = $product;
          $Record['CATEGORY_ID'] = $category_id;
          $Record['QTY'] = 1;

           $Record['ID']=SQLInsert('products', $Record);
        $id = $Record['ID'];

        $this->addToList($id);
        if($debugEnabled) debmes('Products produkt '.$product.' not found, added to category id '.$category_id);
    }


} 

function Get_Product_ID($product) {
$res=SQLSelectOne("select ID from products where TITLE='" . $product . "'");

$id=0;
if ($res['ID']) {
 $id=$res['ID'];
}  

return $id;
}

function Get_Category_ID($category) {
$res=SQLSelectOne("select ID from product_categories where TITLE='" . $category . "'");
$id=0;
if ($res['ID']) {
 $id=$res['ID'];
}
return $id;

}

?>
