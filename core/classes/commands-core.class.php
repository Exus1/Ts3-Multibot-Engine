<?php
class commandsCore extends baseObject {


  //*******************************************************************************************
  //****************************************** Vars *******************************************
  //*******************************************************************************************

  // $lang;
  // $paths;
  // $tsAdmin;

  // $socket;

  // $general_config;
  // $command_list;
  // $multibot_config;
  // $permission_list;

  private $instance_list = Array();

  //*******************************************************************************************
  //************************************ Public Functions *************************************
  //******************************************************************************************



  function getInstanceList()  {
    return $this->instance_list;
  }


  public function executeCommand($command) {

    if($this->command_list == false) {
      return 4;
    }
    $command_info = Array(
      'command' => explode(" ", mb_strtolower($command['msg'], "UTF-8")),
      'clientId' => $command['invokerid'],
      'clientUID' => $command['invokeruid'],
      'clientName' => $command['invokername']
    );

    $tsAdmin = $this->tsAdmin;
    if(isset($this->command_list[$command_info['command'][0]]))  {

      $dbid = $this->tsAdmin->clientGetDbIdFromUid($command_info['clientUID']);

      if(!$dbid['success'])  {return false;}

      $client_groups = $this->tsAdmin->serverGroupsByClientID($dbid['data']['cldbid']);

      if(!$client_groups['success']) {return false;}

      if(!isset($this->permission_list["c_permission_".$command_info['command'][0]])) {
        $this->addError("Nie można odnaleźć permisji" . " c_permission_".$command_info['command'][0]);
        return 3;
      }else {
        $groups = $this->permission_list["c_permission_".$command_info['command'][0]];
      }

      if(strstr($groups, "all") !== false)  {
        include($this->paths['folders']['commands'] . $this->command_list[$command_info['command'][0]]);
        return 5;
      }

      foreach($client_groups['data'] as $group) {
        if(strstr($groups, $group['sgid'])) {

          include($this->paths['folders']['commands'] . $this->command_list[$command_info['command'][0]]);

          return 5;
        }
      }
      return 2;
    }else {
      return 4;
    }
  }




  /** getInstanceId($function)
    *
    * Zwraca id instancji w której uruchomiona jest podana funkcja
    */
  function getInstanceId($function)  {
    foreach($this->instance_list['instances'] as $instance_id => $instance_info) {
      if(in_array($function, $instance_info['functions']))  {
        return $instance_id;
      }
    }
    return false;
  }





  /** sendToInstance($id, $msg)
    *
    * Wysyła polecenie do instancji o id $id
    */
  function sendToInstance($id, $msg)  {
    if(is_int($id)) {
      $socket = $this->instance_list['instances'][$id]['socket'];
    }else {
      if($instance_id = $this->getInstanceId($id)) {
        $socket = $this->instance_list['instances'][$instance_id]['socket'];
      }else {
        return false;
      }
    }

    if(!socket_write($socket, $msg, strlen($msg))) {
      return false;
    }else {
      return true;
    }
  }




  /** readFromInstance($id)
    *
    * Odczytuje informacje otrzymane od instancji
    */
  public function readFromInstance($id)  {
    if(is_int($id)) {
      $socket = $this->instance_list['instances'][$id]['socket'];
    }else {
      if($instance_id = $this->getInstanceId($id)) {
        $socket = $this->instance_list['instances'][$instance_id]['socket'];
      }else {
        return false;
      }
    }

    sleep(1);

    if(!($buffer = socket_read($socket, 2048))) {
      return false;
    }else {
      return $buffer;
    }
  }




  /** killInstance($id)
    *
    * Wyłącza daną instancje
    */
  public function killInstance($id)  {
    if(!is_int($id))  {
      $id = $this->getInstanceId($id);
    }

    if(isset($this->instance_list['instances'][$id]))  {
      $return = shell_exec("screen -XS ". $this->instance_list['instances'][$id]['process'] ." quit");
    }else {
      return false;
    }

    if($return == "No screen session found.") {
      $this->addError("Nie można odlaźć screen'a ". $this->config['instances'][$id]['process']);
      return false;
    }elseif ($return = "") {
      return true;
    }else {
      return false;
    }
  }



  /** killAllInstances()
    *
    * Wyłącza wszystkie instancje
    */
  function killAllInstances() {
    foreach($this->instance_list['instances'] as $instance)  {
      $this->killInstance($instance['id']);
    }
    return true;
  }





  function createInstance($functions)  {

    if(empty($functions)) {
      $this->addError("Nie podano funkcji do utworzenia instancji");
      return false;
    }

    if(empty($this->instance_list))  {
      $this->instance_list['id'] = 0;
    }else {
      $this->instance_list['id']++;
    }

    print "\n";
    $this->addInfo("Uruchamianie instancji o id: " . $this->instance_list['id']);

    $result = shell_exec("screen -dmS ExusMultibotInstance php " . $this->paths['files']['multibot-core']);

    if($result == "[screen is terminated]") {
      $this->addError("Nie udało się uruchomić instancji o id " . $this->instance_list['id']);
      return false;
    }

    $this->addInfo("Oczekiwanie na połączenie od instacnji o id: " . $this->instance_list['id']);
    socket_listen($this->socket);

    if($this->instance_list['instances'][$this->instance_list['id']]['socket'] = socket_accept($this->socket)) {

      $this->addInfo("Pomyślnie połączono się z instacją o id ". $this->instance_list['id']);

      foreach($functions['functions'] as $function) {
        if(!isset($functions_instance) || empty($functions_instance)) {
          $functions_instance = $function;
        }else {
          $functions_instance .= "," . $function;
        }
      }

      if($this->instance_list['id'] == 0)  {
        $msg = $this->general_config['multibot_config']['instance_name'] . "," . $functions_instance;
      }else {
        $msg = $this->general_config['multibot_config']['instance_name'] . $this->instance_list['id'] . "," . $functions_instance;
      }

      if(!socket_write($this->instance_list['instances'][$this->instance_list['id']]['socket'], $msg, strlen($msg)))
      {
        $this->addError("Komunikacją z instancją (id " . $this->instance_list['id'] . ") nie powiodła się", true);
      }else {
        $this->addInfo("Pomyślnie wyłano instrukcje dla instancji id " . $this->instance_list['id']);
      }
      $this->addInfo("Oczekiwanie na odpowiedź od instancji (3s)");

      sleep(3);

      if($buffer = socket_read($this->instance_list['instances'][$this->instance_list['id']]['socket'], 2048))  {
        $this->addInfo("Informacje o instancji" . "\n");

        print_r(Array(
          'id' => $this->instance_list['id'],
          'Process Name' => 'ExusMultibotInstance',
          'Bot Name' => $buffer,
          'Functions' => $functions['functions'])
        );

        print "\n";

        $this->instance_list['instances'][$this->instance_list['id']] = Array(
          'id' => $this->instance_list['id'],
          'process' => "ExusMultibotInstance",
          'bot_name' => $buffer,
          'functions' => $functions['functions'],
          'weight' => $functions['weight'],
          'socket' => $this->instance_list['instances'][$this->instance_list['id']]['socket']
        );

        return $this->instance_list['id'];
      }
    }else {
      shell_exec("screen -XS ExusMultibotInstance quit");
      $this->addError("Nie udało połączyć się z instancją. Instancja zostanie wyłączona.", true);
    }
  }

  //*******************************************************************************************
  //*********************************** Internal Functions ************************************
  //*******************************************************************************************


}
?>
