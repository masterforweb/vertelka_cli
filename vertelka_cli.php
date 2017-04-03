<?php

  	// загрузка рекламных мест
    function vrt_load($domain = ''){

      if ($domain == '')
        $domain = $_SERVER['SERVER_NAME'];
      
      $url = 'http://vertelka.argumenti.ru/all/'.$domain;
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
      $result = curl_exec($ch);

      if ($result !== '')
        return json_decode($result, true);

      return null;

    }


    // вывести место
    function vrt_place($id, $domain = '') {

      static $items = array();

      if (isset($_SERVER['HTTP_X_MOBILE_DEVICE']))
          $device = $_SERVER['HTTP_X_MOBILE_DEVICE'];
      else
          $device = 'all';  

      if (count($items) == 0)
        $items = vrt_load('an-crimea.ru');
        
               
      if (!isset($items[$id]))
        return null; 
      
      $curritems = $items[$id];        

      foreach ($curritems as $item) {

          if ($item['adv_alias'] == '')  //сквозной элемент
            $add = True;
          else {
            $sarr = explode(';', $item['adv_alias']);
            foreach ($sarr as $find) {
              $find = trim($find);
              $first  = mb_substr($find, 0, 1); // служебные символы
              $find2 = substr($find, 1);
              if ($first == '-') { //исключения
                if ($find2 == '/' and trim($_SERVER['REQUEST_URI'], '/') == ''){
                    $add = False;
                    break; 
                }    
                elseif (mb_strpos($url, $find2) !== FALSE) {
                  $add = False;
                  break; //не подходит
                }  
                else
                  $add = True;
              }
              elseif ($firts == '+'){         
                if ($find2 == '/' and trim($_SERVER['REQUEST_URI'], '/') == ''){
                    $add = True;
                } 
              }
              else {
                if (mb_strpos($url, $find) !== FALSE){ //добавляем элемент
                    $add = True;
                    break;
                }
                else
                  $add = False;    
              }
            }
          }    
          
          if ($add)
            $arr[] = $item;

        }  
           

        if (is_array($arr)) { //получаем текущий элемент
          $count = sizeof($arr)-1;
          $curr = rand(0, $count);
          $result = $arr[$curr]; 
        } 

            
      if ($result['item_code'] !== '') {
        if ($result['type_code'] == 1) //php вставка
            eval($result['item_code']);
        else  
          return $result['item_code']; //возвращаем код
      }  
      elseif($result['adv_file'] == 'swf') 
          return '<!-- ex_adv '.$id.' -->'.vrt_swf($result);
      else
          return '<!-- ex_adv '.$id.' -->'.vrt_img($result); //возвращаем оформленный код графического файл


      return null;  
          
      
    }




    /**
    * Проверка соответствия фильтрам
    * -1 точно не подходит
    * 0 возможно не подходит
    * 1 точно подходит
    */
    function vrt_filter($find, $url = '') {
      
      $find = trim($find);
      $url = trim($url, '/');
      $first  = mb_substr($find, 0, 1); // служебные символы

      if ($first == '-'){ //исключение
        $find2 = substr($find, 1);
        echo $find2;
        if (mb_strpos($url, $find2) == FALSE)
          return 0;
        else
          return -1;  
      }
      else  
         if (mb_strpos($url, $find) == FALSE) // нет совпадений
            return 0;

      return 1;    

    }

    /**
    * Вывод картинки в форматах jpeg, gif, png
    */
    function vrt_img ($item) {
      return '<a href="'.$item['adv_link'].'" alt="'.$item['adv_alt'].'" target="_blank" rel="nofollow"><img src="'.ADVLINK.$item['item_id'].'.'.$item['adv_file'].'" width="'.$item['adv_width'].'" height="'.$item['adv_height'].'"></a>';
    }

    
    /**
    * Вывод флеш анимации
    */
    function vrt_swf ($item) {      
      $file = ADVLINK.$item['item_id'].'.'.$item['adv_file'];
      return "<object classid=clsid:D27CDB6E-AE6D-11cf-96B8-444553540000 codebase=http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,2,0 width=".$item['adv_width']." height=".$item['adv_height']."><param name=movie value='".$file."'><param name=quality value=high><embed src='".$file."' quality=high pluginspage=http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash type=application/x-shockwave-flash width=".$item['adv_width']." height=".$item['adv_height']."></embed></object>";    
    }
  