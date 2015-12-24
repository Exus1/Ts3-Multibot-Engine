<?php

class baseObject {

  //*******************************************************************************************
  //****************************************** Vars *******************************************
  //*******************************************************************************************

  protected $lang;
  protected $paths;
  protected $tsAdmin;

  protected $socket;

  protected $general_config;
  protected $command_list;
  protected $multibot_config;
  protected $permission_list;

  //*******************************************************************************************
  //************************************ Public Functions *************************************
  //******************************************************************************************








  function getLang()  {
    return $this->lang;
  }

  function getConfig($type = true) {
    if($type == "general")  {
      return $this->general_config;
    }elseif($type == "multibot")  {
      return $this->multibot_config;
    }else {
      return Array("general_config" => $this->general_config, "multibot_config" => $this->multibot_config, "permission_list" => $this->permission_list, "command_list" => $this->command_list);
    }
  }

  function getCommandList() {
    return $this->command_list;
  }

  function getPermissionList()  {
    return $this->permission_list;
  }

  function getInternalSocket()  {
    return $this->socket;
  }

  function getPaths() {
    return $this->paths;
  }

  function getTsAdmin() {
    return $this->tsAdmin;
  }

  function getTsAdminSocket() {
    return $this->tsAdmin->runtime['socket'];
  }




  function setName($name) {
    if(!$this->tsAdmin->getElement('success', $this->tsAdmin->setName($name)))  {
      $this->addError($this->lang['base']['change_name_error'], false, true);
      return false;
    }else {
      $this->addInfo($this->lang['base']['change_name_success'] . " " . "(" . $name . ")");
      return true;
    }
  }




  function setConfig($object_type)  {
    if($object_type = "commands") {
      $this->setCommandList();
      $this->setPermissionList();
      $this->setMultibotConfig();
    }elseif($object_type = "multibot")  {
      $this->setMultibotConfig();
    }
  }





  /** addError($name, $tsAdmin = false, $critical = flase)
    *
    * Wyświetla błąd w konsoli
    *
    * Parametry:
    * name - nazwa błędu
    * tsAdmin - wyświetlanie błędu ts3admin true/false
    * critical - zakończenie wykonywania skryptu true/false
    */
  function addError($name, $critical = false, $tsAdmin = false) {
    // critical == true - Błąd krytyczny (kończy wykonywanie)
    // critical == false - Błąd umożliwiający dalsze wykoananie
    if(empty($name))  {
      print red . 'UNKNOWN ERROR'. resetColor ."\n";
    }

    if(is_bool($critical))  {
      if($critical)  {

        //$this->killAllInstances();

        if($tsAdmin)  {
          $error = $this->tsAdmin->getDebugLog();
          print red . "CRITICAL ERROR: ". resetColor .$name . "\n";
          print_r($error);
          die();
        }else {
          die(red . "CRITICAL ERROR: ". resetColor . $name . "\n");
        }
      }

      if(!$critical)  {
        if($tsAdmin)  {
          $error = $this->tsAdmin->getDebugLog();
          print red . "ERROR: ". resetColor .$name . "\n";
          print_r($error);
          print "\n";
        } else {
          print red . 'ERROR: '. resetColor . $name. "\n";
        }
      }
    }
  }









  /** addInfo($name)
    *
    * Wyświetla informacje w konsoli
    *
    * Parametry:
    * name - nazwa informacji
    */
  function addInfo($name) {
    print green . 'INFO: '. resetColor . $name . "\n";
  }



  //*******************************************************************************************
  //*********************************** Internal Functions ************************************
  //*******************************************************************************************


  function __construct($object_type)  {
    global $lang;
    global $paths;

    $this->paths = $paths;
    $this->lang = $lang;

    // General Config load
    $general_config = parse_ini_file($paths['files']['general-config'], true);
    $this->general_config = $general_config;

    if(empty($general_config))  {
      $this->addError($lang['base']['general_config_load_error'], true);
    }else {
      $this->addInfo($lang['base']['general_config_load_success']);
    }

    require($paths['files']['ts3admin']);
    $this->tsAdmin = new ts3Admin($general_config['server_config']['adress'], $general_config['server_config']['query_port']);

    if(!is_object($this->tsAdmin))  {
      $this->addError($lang['base']['ts3admin_object_create_error'], true);
    }else{
      $this->addInfo($lang['base']['ts3admin_object_create_success']);
    }

    if(!$this->tsAdmin->getElement('success', $this->tsAdmin->connect()))  {
      $this->addError($lang['base']['server_connect_error'], true, true);
    }else{
      $this->addInfo($lang['base']['server_connect_success']);
    }


    if(!$this->tsAdmin->getElement('success', $this->tsAdmin->login($general_config['server_config']['login'], $general_config['server_config']['password'])))  {
      $this->addError($lang['base']['server_login_error'], true, true);
    }else {
      $this->addInfo($lang['base']['server_login_success']);
    }

    if(!$this->tsAdmin->getElement('success', $this->tsAdmin->selectServer($general_config['server_config']['server_port'])))  {
      $this->addError($lang['base']['server_select_error'], true, true);
    }else {
      $this->addInfo($lang['base']['server_select_success']);
    }

    $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    if(!$this->socket)  {
      $this->addError("/n". $lang['base']['socket_create_error'], true);
    }else {
      $this->addInfo($lang['base']['socket_create_success']);
    }



    if(isset($object_type) && ($object_type == "commands"))  {

      if(!$this->tsAdmin->getElement('success', $this->tsAdmin->setName($general_config['multibot_config']['command_bot_name'])))  {
        $this->addError($lang['base']['change_name_error'], false, true);
      }else {
        $this->addInfo($lang['base']['change_name_success']);
      }

      if(!socket_bind($this->socket, 'localhost', 12345)) {
        $this->addError($lang['base']['socket_bind_error'], true);
      }else {
        $this->addInfo($lang['base']['socket_bind_success']);
      }

      $this->setConfig($object_type);
    }elseif(isset($object_type) && ($object_type == "multibot")) {

      if(!socket_connect($this->socket, 'localhost', 12345)) {
        $this->addError($lang['base']['instance_connect_error'], false);
      }else {
        $this->addInfo($lang['base']['instance_connect_success']);
      }

      if(!socket_set_nonblock($this->socket)) {
        $this->addError($lang['base']['socket_noblock_error']);
      }else {
        $this->addInfo($lang['base']['socket_noblock_success']);
      }

      $this->setConfig($object_type);
    }else {
      $this->addError($lang['base']['unknown_instance_type'] ." " . $object_type, true);
    }


  }


  private function setCommandList()  {
    if(is_dir($this->paths['folders']['commands']))  {
      if($dh = opendir($this->paths['folders']['commands'])) {
        while(($file = readdir($dh)) !== false) {
          if(strstr($file, ".php") !== false) {
            $command_name = substr($file, 0, strpos($file, ".php"));
            $command_list[mb_strtolower($command_name, "UTF-8")] = $file;
          }
        }
        if(empty($command_list))  {
          $this->addError($this->lang['base']['command_list_error']);
          $this->commandList  = false;
          return false;
        } else {
          $this->command_list = $command_list;
          $this->addInfo($this->lang['base']['command_list_success']);
          return true;
        }
        closedir($dh);
      }else {
        $this->addError($this->lang['bas']['command_folder_open_error']);
        $this->commandList = false;
        return false;
      }
    }else {
      $this->addError($this->lang['bas']['command_folder_open_error']);
      $this->commandList = false;
      return false;
    }
  }








  private function setPermissionList() {
    $permissionsLoad = parse_ini_file($this->paths['files']['permissions']);

    if(empty($permissionsLoad)) {
      $this->addError($this->lang['base']['permission_file_open_error']);
    }else {
      $this->addInfo($this->lang['base']['permission_file_load_success']);
    }

    $permissions = Array();

    foreach($permissionsLoad as $dbid => $perms) {
      $perms = preg_replace('/\s+/', '', $perms);
      $perm = explode(",", $perms);
      foreach($perm as $permTemp) {
        $permTemp = mb_strtolower($permTemp, "UTF-8");
        if(isset($permissions[$permTemp]) && !empty($permissions[$permTemp])) {
          $permissions[$permTemp] .= ",".$dbid;
        }else {
          $permissions[$permTemp] = $dbid;
        }
      }
    }
    $this->permission_list = $permissions;
    return true;
  }



  private function setMultibotConfig() {
    $config_files = getFilesList($this->paths['folders']['functions-configs']);

    if(empty($config_files))  {
      $this->addError($this->lang['base']['config_foler_open_error'], true);
    }

    foreach($config_files as $config_file) {
      if(substr($config_file, 0, strpos($config_file, ".conf")))
      $functions_configs[substr($config_file, 0, strpos($config_file, ".conf"))] = parse_ini_file($this->paths['folders']['functions-configs']. $config_file, true);
    }

    if(!empty($functions_configs))  {
      foreach($functions_configs as $function_name => $function_config) {
        foreach($function_config as $function_var => $var_value)  {
          if(is_array($var_value))  {
            foreach($var_value as $var_name => $var_value1) {
              $var_value_end[mb_strtolower($var_name, "UTF-8")] = mb_strtolower($var_value1, "UTF-8");
            }
            $function_var_end[mb_strtolower($function_var, "UTF-8")] = $var_value_end;
          }else {
            $function_var_end[mb_strtolower($function_var, "UTF-8")] = mb_strtolower($var_value, "UTF-8");
          }
        }
        $functions_configs_end[mb_strtolower($function_name, "UTF-8")] =  $function_var_end;
      }
      $this->multibot_config = $functions_configs_end;
      $this->addInfo($this->lang['base']['config_load_success']);
      return true;
    }else {
      $this->addError($this->lang['base']['config_load_error'], true);
      return false;
    }
  }

}

?>
