<?php
class multibotCore extends baseObject {


  //*******************************************************************************************
  //****************************************** Vars *******************************************
  //*******************************************************************************************

  // $lang;
  // $paths;
  // $tsAdmin;

  // $socket;

  // $general_config;
  // $multibot_config;

  private $client_list = Array();
  private $channel_list = Array();
  private $server_group_names = Array();
  private $server_info = Array();

  private $refresh_function = Array();

  //*******************************************************************************************
  //************************************ Public Functions *************************************
  //******************************************************************************************

  function getClientList($query = false) {
    if(empty($this->client_list)) {
      $this->refreshClientList();
      $this->addError($this->lang['multibot_core']['refresh_var_error1'] . " " . str_replace(__CLASS__."::", "", __METHOD__) . " " . $this->lang['multibot_core']['refresh_var_error2'] . " " . str_replace(__CLASS__."::"."get", "", __METHOD__) . " " . $this->lang['multibot_core']['refresh_var_error3']);
    }

    if($query)  {
      return $this->client_list;
    }else {
      $client_list = Array();
      foreach($this->client_list as $clients)  {
        if($clients['client_type'] == 0)  {
          $client_list[] = $clients;
        }
      }
      return $client_list;
    }
  }



  function getChannelList() {
    if(empty($this->channel_list)) {
      $this->refreshChannelList();
      $this->addError($this->lang['multibot_core']['refresh_var_error1'] . " " . str_replace(__CLASS__."::", "", __METHOD__) . " " . $this->lang['multibot_core']['refresh_var_error2'] . " " . str_replace(__CLASS__."::"."get", "", __METHOD__) . " " . $this->lang['multibot_core']['refresh_var_error3']);
    }

    return $this->channel_list;
  }



  function getServerGroupNames()  {
    if(empty($this->server_group_names)) {
      $this->refreshServerGroupNames();
      $this->addError($this->lang['multibot_core']['refresh_var_error1'] . " " . str_replace(__CLASS__."::", "", __METHOD__) . " " . $this->lang['multibot_core']['refresh_var_error2'] . " " . str_replace(__CLASS__."::"."get", "", __METHOD__) . " " . $this->lang['multibot_core']['refresh_var_error3']);
    }

    return $this->server_group_names;
  }

  function getServerInfo($query_clients = false) {
    if(empty($this->server_info)) {
      $this->refreshServerInfo();
      $this->addError($this->lang['multibot_core']['refresh_var_error1'] . " " . str_replace(__CLASS__."::", "", __METHOD__) . " " . $this->lang['multibot_core']['refresh_var_error2'] . " " . str_replace(__CLASS__."::"."get", "", __METHOD__) . " " . $this->lang['multibot_core']['refresh_var_error3']);
    }

    if($query_clients)  {
      return $this->server_info;
    }else {
      $server_info = $this->server_info;

      $clients_count = count($this->getClientList());

      $server_info['virtualserver_clientsonline'] = $clients_count;

      return $server_info;
    }
  }



  /** refres($var, $time)
    *
    * $var - Nazwa zmiennej do odświeżenia lub utworzenia
    * $time - Odstęp czasu między kolejnymi odświeżeniami
    *
    * Typ: Publiczna
    *
    * odświeża lub przypisuje wartość do zmiennej w multibotCore
    *
    */
  function refresh($var, $time = 0)
  {
    $var = str_replace("_", "", $var);
    if(empty($this->refresh_function[$var]))
    {
      $this->refresh_function[$var] = date("r");
      //$function = "refresh".$var;
    }

    if($this->refresh_function[$var] <= date("r"))
    {
      $function = "refresh".$var;
      $this->$function();

      $this->refresh_function[$var] = date("r", time() + $time);
      return true;
    }
  }




  //*******************************************************************************************
  //*********************************** Internal Functions ************************************
  //*******************************************************************************************

  private function refreshChannelList() {
    $channel_list = $this->tsAdmin->channelList(); // Przypisanie tablice z listą kanałów do parametru $channelList
    if($channel_list['success'] && !empty($channel_list['data'])) {
      $channel_list_end = Array();
      foreach($channel_list['data'] as $channel)  {
        $channel_list_end[$channel['cid']] = $channel;
      }
      $this->channel_list = $channel_list_end;
      return true;
    }else {
      $this->addError($this->lang['multibot_core']['refresh_channel_list_error']);
      return false;
    }
  }



  /** refreshClientList()
    *
    * Typ: Prywatna
    *
    * Ponownie ładuje listę użytkowników do zmiennej $clientList
    *
    */
  private function refreshClientList() {
    $client_list = $this->tsAdmin->clientList("-uid -away -voice -times -groups -info -icon -country -ip -badges"); // Przypisuje tablice z listą użytkowników do parametru $clientList
    if($client_list['success'] && !empty($client_list['data'])) {
      $client_list_end = Array();
      foreach($client_list['data'] as $client)  {
        $client_list_end[$client['clid']] = $client;
      }
      $this->client_list = $client_list_end;
      return true;
    }else {
      $this->addError($this->lang['multibot_core']['refresh_client_list_error'], false, true);
      return false;
    }
  }




  private function refreshServerInfo()  {
    $server_info = $this->tsAdmin->serverInfo();

    if($server_info['success'] && !empty($server_info['data']))  {
      $server_info['data']['virtualserver_uptime'] = $this->tsAdmin->convertSecondsToStrTime($server_info['data']['virtualserver_uptime']);
      $this->server_info = $server_info['data'];
      return true;
    }else {
      $this->addError($this->lang['multibot_core']['refres_server_info_error']);
      return false;
    }
  }




  /** refreshServerGroupNames()
    *
    * Typ: Prywatna
    *
    * Przypisuje do zmiennej $serverGroupNames nazwy grup
    *
    */
  private function refreshServerGroupNames()  {
    $group_list = $this->tsAdmin->serverGroupList();

    foreach($group_list['data'] as $group_list_temp)
    {
      if($group_list_temp['type'] == 2)  {
        $this->server_group_names['query'][$group_list_temp['sgid']] = Array("name" => $group_list_temp['name'], "iconid" => $group_list_temp['iconid'], "savedb" => $group_list_temp['savedb']);
      }
      else {
        $this->server_group_names['regular'][$group_list_temp['sgid']] = Array("name" => $group_list_temp['name'], "iconid" => $group_list_temp['iconid'], "savedb" => $group_list_temp['savedb']);
      }
    }
  }


// End class
}


/** start
  *
  * Klasa uruchamiająca funkcje bota w odpowiednich przedziałąch czasowych.
  * Każda funkcja ją dziedziczy dzięki czemu w każdej można użyć tej funkcji.
  *
  */
class start
{
/** start($time)
  *
  * Typ: Publiczna
  *
  * Uruchamia timer który uruchamia funkcję w odpowiednim przedziale czasowym
  *
  */
  public function start($time = 0)
  {
    //if(empty($this->timer))
    //{
      //$this->timer = date("r");
    //}

    if($this->start_timer <= date("r"))
    {
      if(empty($this->start_timer)) {
        print green . 'START FUNCTION: '. resetColor . $this->function_name."\n";
      }
      $this->start_timer = date("r", time() + $time);
      $this->start_function();
    }
  }
}


?>
