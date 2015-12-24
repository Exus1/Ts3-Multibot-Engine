<?php
// String $file - Plik który ma być zapisany
// Array $iniConetent - Zawartość pliku
function writeIniFIle($file, $iniContent)  {
  $file = fopen($sourceFile, "w");

  foreach($iniContent as $id => $value) {

    if(is_array($id) || is_bool($id)) {
      return print "Id nie może być tablicą ani wartością boolean";
    }

    if(is_array($value))  {
      fputs($file, "[".$id."]\n");
      foreach($value as $idVal => $vars)  {
        fputs($file, $idVal . " = " . $vars . "\n");
      }
    }else {
      fputs($file, $id . " = " . $value . "\n");
    }
  }
}



function getFilesList($folder)  {
  if(is_dir($folder))  {
    if($dir = opendir($folder)) {
      while(($file = readdir($dir)) !== false) {
        if(($file != ".") && ($file != "..") && is_file($folder.$file))
        $files[] = $file;
      }
      if(empty($files))  {
        return 0;
      }else {
        return $files;
      }
      closedir($dir);
    }else {
      return false;
    }
  }else {
    return false;
  }
}


function refreshVarsList() {
  global $multibot_config;
  global $vars;
  global $vars_list;
  global $functions_to_start;
  foreach($functions_to_start as $function_name) {
    if(isset($vars[$function_name]))
    $vars_list[$function_name] = Array();

    if(!empty($vars[$function_name]))

    $refresh_time = $multibot_config[$function_name]['general_config']['refresh'];
    foreach($vars[$function_name] as $valueTemp)  {
      if(isset($vars_list[$function_name][$valueTemp])) {
        if($vars_list[$function_name][$valueTemp] > $refresh_time) {
          $vars_list[$function_name][$valueTemp] = $refresh_time;
          continue;
        }
        continue;
      }
      $vars_list[$function_name][$valueTemp] = $refresh_time;
    }
  }
}



function socketRead($socket)  {
  return socket_read($socket, 4096);
}


/** getSmallerIndex($table)
  *
  * Funkca pomocniczna do tworzenia instancji.
  * W argumencie przyjmuje tablicę z instancjami a w rezultacie oddaje id instancji która ma najmniejszą wagę.
  */
function getSmallerIndex($table)  {
  $index = 0;
  for($i = 1; $i <= (count($table)-1); $i++) {
    if(!($table[$index]['weight'] <= $table[$i]['weight']))  {
        $index = $i;
    }
  }
  return $index;
}





function getBigestIndex($table) {
  $index = 0;
  for($i = 1; $i <= (count($table)-1); $i++) {
    if(!($table[$index]['weight'] >= $table[$i]['weight']))  {
        $index = $i;
    }
  }
  return $index;
}




/** sendCommand($command)
  *
  * Funkcja wysyłająca komendy do serwera ts3 poprzez socket ts3admin
  */
function sendCommand($command)  {
  global $multibotObject;
  global $tsAdminSocket;

  $splittedCommand = str_split($command, 1024);
  $splittedCommand[(count($splittedCommand) - 1)] .= "\n";
  foreach($splittedCommand as $commandPart) {
    fputs($tsAdminSocket, $commandPart);
  }
  return fgets($tsAdminSocket, 4096);
}




/** unEscapeText($text)
  *
  * Funkcja zamieniająca znaczniki w ciągach
  * Zapożyczona i zmodyffikowana z ts3admin
  */
function unEscapeText($text) {
  $escapedChars = array("\t", "\v", "\r", "\n", "\f", "\s", "\p", "\/");
  $unEscapedChars = array('', '', '', '', '', ' ', '|', '/');
  $text = str_replace($escapedChars, $unEscapedChars, $text);
  return $text;
}




/** getData()
  *
  * Pobiera dane z socketu ts3admin
  * Zapożyczona i zmodyfikowana z ts3admin
  */
function getData()  {

  global $tsAdminSocket;

  $data = fgets($tsAdminSocket, 4096);

  if(!empty($data)) {
    $datasets = explode(' ', $data);

    $output = array();

    foreach($datasets as $dataset) {
      $dataset = explode('=', $dataset);

      if(count($dataset) > 2) {
        for($i = 2; $i < count($dataset); $i++) {
          $dataset[1] .= '='.$dataset[$i];
        }
        $output[unEscapeText($dataset[0])] = unEscapeText($dataset[1]);
      }else{
        if(count($dataset) == 1) {
          $output[unEscapeText($dataset[0])] = '';
        }else{
          $output[unEscapeText($dataset[0])] = unEscapeText($dataset[1]);
        }
      }
    }
    return $output;
  }
}




?>
