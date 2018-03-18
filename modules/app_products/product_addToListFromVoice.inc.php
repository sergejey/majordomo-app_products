<?php

//$command='черный молотый перец белого лука гель для душа хлеба белого мыло марки пальмалив 1 бутылку красного вина';

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
    if ($words[$is]=='один' or $words[$is]=='одну')  $words[$is]='1';
    if ($words[$is]=='два' or $words[$is]=='две')  $words[$is]='2';
    if ($words[$is]=='три')  $words[$is]='3';
    if ($words[$is]=='четыре')  $words[$is]='4';
    if ($words[$is]=='пять')  $words[$is]='5';
    if ($words[$is]=='шесть')  $words[$is]='6';
    if ($words[$is]=='семь')  $words[$is]='7';
    if ($words[$is]=='восемь')  $words[$is]='8';
    if ($words[$is]=='девять')  $words[$is]='9';
    if ($words[$is]=='десять')  $words[$is]='10';
    if (preg_match('/^(\d+)$/', $words[$is])) {
        $base_forms[$is] = array($words[$is]);
        $partsOfSpeech[$is]=array('ЧИСЛ');
        $f_word[$is]=array('');
    } 
    elseif (preg_match('/([a-zA-Z])/',$words[$is])) {
        $base_forms[$is] = array($words[$is]);
        $partsOfSpeech[$is]=array('С');
        $f_word[$is][0][0]['grammems'][0]='МР';
        $f_word[$is][0][0]['grammems'][1]='ЕД';
    }
    else {
        $Word = mb_strtoupper($words[$is], 'UTF-8');
        $base_forms[$is] = $morphy->getBaseForm($Word);
        $partsOfSpeech[$is] = $morphy->getPartOfSpeech($Word);
        $f_word[$is] = $morphy->getGramInfo($Word);
        if ($base_forms[$is][0]=='ВОД') {
            $base_forms[$is][0]='ВОДА';
            for ($kk= 0; $kk < count($f_word[$is][0][0]['grammems'])-1; $kk++) {
                if ($f_word[$is][0][0]['grammems'][$kk]=='МР') {
                    $f_word[$is][0][0]['grammems'][$kk]='ЖР';
                    break;
                }
            }
        }
        if ( count($partsOfSpeech[$is])==2) {
            if ($partsOfSpeech[$is][0]=="С" and $partsOfSpeech[$is][1]=="П") {
                // Если слово может быть и существительным и прилагательным, выбираем прилагательное. Пример - красный
                $partsOfSpeech[$is][0]="П";
                $chislo=array_intersect($f_word[$is][1][0]['grammems'],['ЕД', 'МН']);
                $chislo=reset($chislo);
                $rod=array_intersect($f_word[$is][1][0]['grammems'],['МР', 'ЖР', 'СР']);
                $rod=reset($rod);
                $f_word[$is][0][0]['grammems'][0]=$chislo;
                $f_word[$is][0][0]['grammems'][1]=$rod;
            } 
            elseif ($partsOfSpeech[$is][0]=="Г" and $partsOfSpeech[$is][1]=="С") {
                // Если слово может быть и глаголом и существительным, выбираем существительное. Пример - чай
                $partsOfSpeech[$is][0]="С";
                $base_forms[$is][0]=$base_forms[$is][1];
                $chislo=array_intersect($f_word[$is][1][0]['grammems'],['ЕД', 'МН']);
                $chislo=reset($chislo);
                $rod=array_intersect($f_word[$is][1][0]['grammems'],['МР', 'ЖР', 'СР']);
                $rod=reset($rod);
                $f_word[$is][0][0]['grammems'][0]=$chislo;
                $f_word[$is][0][0]['grammems'][1]=$rod;
            }
        }
        if ( count($base_forms[$is])>1){
            if ($f_word[$is][0][0]['pos']=="С" and $f_word[$is][1][0]['pos']=="С") {
                // Из нескольких форм существительного выбираем ту, которая в именительном или винительном падеже
                $padezh=array_intersect($f_word[$is][0][0]['grammems'],['ИМ', 'РД', 'ДТ', 'ВН', 'ТВ', 'ПР', 'ЗВ']);
                $padezh=reset($padezh);
                if ($padezh!='ИМ' and $padezh!='ВН') {
                    $base_forms[$is][0]=$base_forms[$is][1];
                    $chislo=array_intersect($f_word[$is][1][0]['grammems'],['ЕД', 'МН']);
                    $chislo=reset($chislo);
                    $rod=array_intersect($f_word[$is][1][0]['grammems'],['МР', 'ЖР', 'СР']);
                    $rod=reset($rod);
                    $padezh=array_intersect($f_word[$is][1][0]['grammems'],['ИМ', 'РД', 'ДТ', 'ВН', 'ТВ', 'ПР', 'ЗВ']);
                    $padezh=reset($padezh);
                    $f_word[$is][0][0]['grammems'][0]=$chislo;
                    $f_word[$is][0][0]['grammems'][1]=$rod;
                    $f_word[$is][0][0]['grammems'][2]=$padezh;
                }
            }
        }
                

    } 
}


$products='';
$qty=1;
$ed_izm='';



for ($is = 0; $is < $totals; $is++) {

    if (preg_match('/^(\d+)$/', $words[$is])) {
        
        $qty=(int)$words[$is];
        if (($is+1)<$totals) {
            if (in_array($base_forms[$is+1][0],array('БУТЫЛКА','ПАЧКА','ШТУКА','УПАКОВКА','ГРАММ','КИЛОГРАММ','РУЛОН'))) {         
                $ed_izm=$base_forms[$is+1][0];
                $is++;
            
            }
        }
    }
    elseif (in_array($base_forms[$is][0],array('БУТЫЛКА','ПАЧКА','ШТУКА','УПАКОВКА','ГРАММ','КИЛОГРАММ','РУЛОН'))) { 
        $ed_izm=$base_forms[$is][0];
            
    }
    else {
        if ($partsOfSpeech[$is][0]=='С') {
            $chislo=array_intersect($f_word[$is][0][0]['grammems'],['ЕД', 'МН']);
            $chislo=reset($chislo);
            $rod1=array_intersect($f_word[$is][0][0]['grammems'],['МР', 'ЖР', 'СР']);
            $rod1=reset($rod1);

            if (($is+1)<$totals) {
                if ($partsOfSpeech[$is+1][0]=='С') {
                    if ($base_forms[$is+1][0]=='МАРКА' or $base_forms[$is+1][0]=='ФИРМА') {
                        if (preg_match('/([a-zA-Z])/',$words[$is])) $product=$words[$is];
                        else {
                            if ($chislo=='ЕД') $product=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$rod1,'ЕД','ИМ']);
                            else $product=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$chislo,'ИМ']);
                            $product=$product[0]['form'];
                        }

                        $product=$product . ' ' . $words[$is+1] . ' ' . $words[$is+2];
                        $is=$is+2;
                    }
                    else {
                        if (preg_match('/([a-zA-Z])/',$words[$is])) $product=$words[$is];
                        else {
                            if ($chislo=='ЕД') $product=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$rod1,'ЕД','ИМ']);
                            else $product=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$chislo,'ИМ']);
                            $product=$product[0]['form'];
                        }
                    }
                }
                elseif ($partsOfSpeech[$is+1][0]=='ПРЕДЛ') {
                    if (preg_match('/([a-zA-Z])/',$words[$is])) $noun=$words[$is];
                    else {
                        if ($chislo=='ЕД') {
                            $noun=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$rod1,'ЕД','ИМ']);
                        }
                        else {
                            $noun=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$chislo,'ИМ']);
                        }
                        $noun=$noun[0]['form'];
                    }
                    $product=$noun . ' ' . $words[$is+1] . ' ' . $words[$is+2];
                    $is=$is+2;
                }
                elseif ($partsOfSpeech[$is+1][0]=='П') {
                    $rod=array_intersect($f_word[$is+1][0][0]['grammems'],['МР', 'ЖР', 'СР']);
                    $rod=reset($rod);
                    // Выбираем форму прилагательного правильного рода
                    if ($chislo=='ЕД') {
                        $adjective=$morphy->castFormByGramInfo($base_forms[$is+1][0],"П",[$rod,'ЕД','ИМ']);
                    }
                    else {
                        $adjective=$morphy->castFormByGramInfo($base_forms[$is+1][0],"П",['МН','ИМ']);
                    }
                    $adjective=$adjective[0]['form'];
                    if (preg_match('/([a-zA-Z])/',$words[$is])) $noun=$words[$is];
                    else {
                        if ($chislo=='ЕД') {
                            $noun=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$rod,'ЕД','ИМ']);
                        }
                        else {
                            $noun=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$chislo,'ИМ']);
                        }
                        $noun=$noun[0]['form'];
                    }
                    if (Get_Product_ID($adjective . " " . $noun)>0) {
                        $product=$adjective . " " . $noun;
                        $is=$is+1;
                    }
                    else {
                        if (preg_match('/([a-zA-Z])/',$words[$is])) $noun=$words[$is];
                        else {
                            if ($chislo=='ЕД') {
                                $noun=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$rod,'ЕД','ИМ']);
                            }
                            else {
                                $noun=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$chislo,'ИМ']);
                            }
                            $noun=$noun[0]['form'];
                        }
                        $product=$noun;
                    }
                }
            }

            else {
                if (preg_match('/([a-zA-Z])/',$words[$is])) $noun=$words[$is];
                else {
                    if ($chislo=='ЕД') {
                        $noun=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$chislo,$rod1,'ИМ']);
                    }
                    else {
                        $noun=$morphy->castFormByGramInfo($base_forms[$is][0],'С',[$chislo,'ИМ']);
                    }
                    $noun=$noun[0]['form'];
                }
                $product=$noun;
            }
        }
        elseif ($partsOfSpeech[$is][0]=='П') {
            $rod=array_intersect($f_word[$is][0][0]['grammems'],['МР', 'ЖР', 'СР']);
            $rod=reset($rod);
            $chislo=array_intersect($f_word[$is][0][0]['grammems'],['МН', 'ЕД']);
            $chislo=reset($chislo);

            // Выбираем форму прилагательного правильного рода
            if ($chislo=='ЕД') {
                $adjective=$morphy->castFormByGramInfo($base_forms[$is][0],"П",[$rod,'ЕД','ИМ']);
            }
            else {
                $adjective=$morphy->castFormByGramInfo($base_forms[$is][0],"П",['МН','ИМ']);
            }
            $adjective=$adjective[0]['form'];
            if (($is+1)<$totals) {
                if ($partsOfSpeech[$is+1][0]=='С') {
                    if (count($base_forms[$is+1])>1){
                        // выбираем форму согласованную по роду
                        for ($kk= 0; $kk < count($base_forms[$is+1])-1; $kk++) {
                            $rod1=array_intersect($f_word[$is+1][$kk][0]['grammems'],['МР', 'ЖР', 'СР']);
                            $rod1=reset($rod1);
                            if ($rod==$rod1) break;
                        }
                        if ($chislo=='ЕД') {
                            $noun=$morphy->castFormByGramInfo($base_forms[$is+1][$kk],'С',['ЕД',$rod,'ИМ']);
                        }
                        else {
                            $noun=$morphy->castFormByGramInfo($base_forms[$is+1][$kk],'С',[$chislo,'ИМ']);
                        }
                        $noun=$noun[0]['form'];

                        $product=$adjective .' ' . $noun ;
                        $is=$is+1; 
                    }
                    else {
                        if (preg_match('/([a-zA-Z])/',$words[$is+1])) $noun=$words[$is+1];
                        else {
                            $noun=$morphy->castFormByGramInfo($base_forms[$is+1][0],'С',[$chislo,'ИМ']);
                            $noun=$noun[0]['form'];
                        }
                        $product=$adjective .' ' . $noun ;
                        $is=$is+1; 
                    } 
                }
                elseif ($partsOfSpeech[$is+1][0]=='П') {
                    if ($chislo=='ЕД') {
                        $adjective1=$morphy->castFormByGramInfo($base_forms[$is+1][0],"П",[$rod,'ЕД','ИМ']);
                    }
                    else {
                        $adjective1=$morphy->castFormByGramInfo($base_forms[$is+1][0],"П",['МН','ИМ']);
                    }

                    $adjective1=$adjective1[0]['form'];
                    if (($is+2)<$totals) {
                        if ($partsOfSpeech[$is+2][0]=='С') {

                            if (count($base_forms[$is+2])>1){
                                // выбираем форму согласованную по роду
                                for ($kk= 0; $kk < count($base_forms[$is+2])-1; $kk++) {
                                    $rod1=array_intersect($f_word[$is+2][$kk][0]['grammems'],['МР', 'ЖР', 'СР']);
                                    $rod1=reset($rod1);
                                }
                                if ($chislo=='ЕД') {
                                    $noun=$morphy->castFormByGramInfo($base_forms[$is+2][$kk],'С',['ЕД',$rod,'ИМ']);
                                }
                                else {
                                    $noun=$morphy->castFormByGramInfo($base_forms[$is+2][$kk],'С',[$chislo,'ИМ']);
                                }
                                $noun=$noun[0]['form'];
 
                                $product=$adjective .' ' . $adjective1 . ' ' . $noun ;
                                $is=$is+2; 
                 
                            }
                            else {
                                if (preg_match('/([a-zA-Z])/',$words[$is+2])) $noun=$words[$is+2];
                                else {
                                    $noun=$morphy->castFormByGramInfo($base_forms[$is+2][0],'С',[$chislo,'ИМ']);
                                    $noun=$noun[0]['form'];
                                }
                                $product=$adjective .' ' . $adjective1 .' ' . $noun;
                                $is=$is+2; 
                            }
                        }
                    }
                    else {
                        $product=$adjective .' ' . $adjective1;
                        $is=$is+1;
                    }    
                }
                else {
                    $product=$adjective .' ' . $adjective1;
                    $is=$is+1;
                }    
            }                
        }
        else {
            $product=$adjective;
        }        

    $product = mb_strtolower($product, 'UTF-8');

    //say($product);
    if ($products=='') $products.=$product; else $products.='. ' . $product;
     
    if($debugEnabled) debmes('Products produkt:'. $product);
                
    $id=Get_Product_ID( $product);
    if ($id > 0){
        addToListQty($id,$qty,$ed_izm);
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

        addToListQty($id,$qty,$ed_izm);
        if($debugEnabled) debmes('Products produkt '.$product.' not found, added to category id '.$category_id);
    }
    $qty=1;
    $ed_izm='';
 }
} 

say('Я добавила в список покупок: ' . $products,2);

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

function addToListQty($id,$qty,$ed_izm) {
   $product=SQLSelectOne("SELECT * FROM products WHERE ID='".(int)$id."'");
   if ($product['ID']) {
    SQLExec("DELETE FROM shopping_list_items WHERE PRODUCT_ID='".(int)$id."'");
    $rec=array();
    $rec['PRODUCT_ID']=$product['ID'];
    $rec['TITLE']=$product['TITLE'];
    $rec['IN_CART']=0;
    $rec['List_Qty']=$qty;
    $rec['Ed_Izm']=$ed_izm;
    SQLInsert('shopping_list_items', $rec);
    if (defined('DROPBOX_SHOPPING_LIST')) {
     $data=LoadFile(DROPBOX_SHOPPING_LIST);
     $data=str_replace("\r", '', $data);
     $lines=explode("\n", $data);
     $total=count($lines);
     $found=0;
     for($i=0;$i<$total;$i++) {
      if ($found) {
       continue;
      }
      if (is_integer(strpos($lines[$i], $product['TITLE']))) {
       $found=1;
      }
     }
     if (!$found) {
      if (!$data) {
       $lines=array();
       $lines[]='SHOPPING LIST';
       $lines[]='';
      }
      $lines[]=$product['TITLE'];
      $data=implode("\n", $lines);
      SaveFile(DROPBOX_SHOPPING_LIST, $data);
     }
    }
   }
}
?>