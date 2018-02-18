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
            } elseif (!preg_match('/[\(\)\+\.]/', $words[$is])) {
                $Word = mb_strtoupper($words[$is], 'UTF-8');
                $base_forms[$is] = $morphy->getBaseForm($Word);
                $partsOfSpeech[$is] = $morphy->getPartOfSpeech($Word);
                $f_word[$is] = $morphy->getGramInfo($Word);
                $base_forms[$is][]=$words[$is];
            } else {
                $base_forms[$is] = array($words[$is]);
            }
        }


for ($is = 0; $is < $totals; $is++) {

    if ($partsOfSpeech[$is][0]=='С') {
        if (($is+1)<$totals) {
            if ($partsOfSpeech[$is+1][0]=='С') {
                $product=$base_forms[$is][0];
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
				$srch = array();
				$srch['CODE'] = $product;
				$srch['IS_CODE'] = False;
				$this->search_products($srch);
				if (count($srch['RESULT']) > 0){
				 $this->addToList($srch['RESULT'][0]['ID']);
				 if($debugEnabled) debmes('Products produkt '.$product.' found, ID:'. $srch['RESULT'][0]['ID']);
				}
				Else 
				{
					if($debugEnabled) debmes('Products produkt '.$product.' not found, adding');
					 $sear = array();
					 $sortby = 'ID';
					 $title = "Неотсортированные";
					 // $sear['TITLE'] = "Неотсортированные";
					 $this->search_product_categories($sear);
					 if (count($sear) > 0){
							if($debugEnabled) debmes('Products category exiting uncnoun');
							$this->category_id = $sear['RESULT'][0]['ID'];
							//$srch['CATEGORY_ID'] = $sear['RESULT'][0]['ID'];
					 } 
					 Else 
					 {
							if($debugEnabled) debmes('Products creating uncnoun');
							$this->mode='update';
							$this->edit_product_categories($srch);
							$this->category_id = $srch['CATIDADDED'];
							//$srch['CATEGORY_ID'] = $srch['CATIDADDED'];
							if($debugEnabled) debmes('Products produkt '.$product.' adding to created uncnoun');
					 }
					 $this->mode='update';
					 $this->tab=='';
					 $title = $product;
					 global $qty;
					 $qty = 1;
					 $addpr = array();
					 $this->edit_products($addpr, 0);
					 $this->addToList($addpr['ID']);
					 if($debugEnabled) debmes('Products produkt '.$product.' not found, added to category id '.$srch['CATEGORY_ID']);
				}


} 
?>
