<?php

//Black       0;30     Dark Gray     1;30
//Blue        0;34     Light Blue    1;34
//Green       0;32     Light Green   1;32
//Cyan        0;36     Light Cyan    1;36
//Red         0;31     Light Red     1;31
//Purple      0;35     Light Purple  1;35
//Brown       0;33     Yellow        1;33
//Light Gray  0;37     White         1;37

define("resetColor", "\033[0m");
define("red", "\033[31m");
define("green", "\033[32m");
define("blue", "\033[34m");

// Kodowanie polskich znaków dla windows
if(strstr(mb_strtolower($_SERVER['OS']), "windows") !== false) {
  shell_exec("chcp 65001");
}

date_default_timezone_set('Europe/Berlin');


//***************************************************************************************
//*********************************** Config Section ************************************
//***************************************************************************************
$start_arguments = Array(
  "lang:",
  "startmode:",
);

$available_langs = Array(
  "pl",
  "eng"
);

$available_startmodes = Array(
  "commands",
  "multibot",
  "debug"
);

$paths = Array(
  "files" => Array(
    "ts3admin" => "core/classes/ts3admin.class.php",
    "commands-core" => "core/classes/commands-core.class.php",
    "multibot-core" => "core/classes/multibot-core.class.php",
    "exus-lib" => "core/classes/exus-lib.php",
    "base-object" => "core/classes/base.class.php",
    "general-config" => "configs/general-config.conf",
    "permissions" => "configs/permissions.conf",
    "multibot-commands" => "core/multibot-commands.php"
  ),
  "folders" => Array(
    "functions" => "functions/",
    "functions-configs" => "configs/functions/",
    "configs" => "configs/",
    "commands" => "commands/",
    "langs" => "langs/",
  ),
  "langs" => Array(
    "pl" => "langs/pl.lang",
    "eng" => "langs/eng.lang"
  )
);

$vars = Array(
  'clock' => Array(),
  'pokeBot' => Array('clientList', 'channelList'),
  'adminStatus' => Array('serverGroupNames'),
  'afkAutoMove' => Array('clientList'),
  'welcomeMessage' => Array('clientList', 'serverInfo'),
  'adminChannelStatus' => Array('clientList'),
  'channelChecker' => Array('clientList', 'serverInfo', 'channelList'),
);

$commands_list = Array(
  'status',
  'start',
  'stop',
  'reloadconfig'
);

$functions_list = Array();

$start_options = getopt("h::", $start_arguments);


//**********************************************************************************
//*********************************** Help Page ************************************
//**********************************************************************************
if(isset($start_options['h']))  {
  print "\n" . 'Available startmodes:' . "\n";
  print '  commands - runs the script in the command controller mode' . "\n";
  print '  multibot - runs the script in the multibot controller mode' . "\n\n";
  print 'Available langs:' . "\n";
  print '  pl';
  print "\n\n";
  print 'Exaple usage: "php core.php --lang pl --startmode commands';
  print "\n";

  die();
}


//*************************************************************************************
//*********************************** Args Checker ************************************
//*************************************************************************************
if(empty($start_options['lang']) && empty($start_options['startmode'])) {
  die(red . 'You must enter the appropriate values in the arguments "--lang" and "--startmode" if you do not know what is possible use the argument "-h"' . resetColor);
}
if(empty($start_options['lang'])) {
  die(red . 'You must enter a value argument "--lang" if you do not know what was possible use the argument "-h"' . resetColor);
}elseif(!in_array($start_options['lang'], $available_langs))  {
  die(red. 'The selected language is not available' . resetColor);
}else {
  if(!$lang = parse_ini_file($paths['langs'][$start_options['lang']], true)) {
    die(red . 'Failed to load the language file "' . $start_options['lang'] . '"' . resetColor);
  }
}

if(empty($start_options['startmode']))  {
  die(red . 'You must enter a value argument "--startmode" if you do not know what was possible use the argument "-h"' . resetColor);
}elseif(!in_array($start_options['startmode'], $available_startmodes))  {
  die(red . 'The wrong type of running a script check available types using the tag "-h"' . resetColor);
}

require($paths['files']['exus-lib']);
require($paths['files']['base-object']);


//**********************************************************************************************
//*********************************** Function List Creator ************************************
//**********************************************************************************************
if(is_dir($paths['folders']['functions']))  {
  if($dh = opendir($paths['folders']['functions'])) {
    while(($file = readdir($dh)) !== false) {
      if(strstr($file, ".class.php") !== false) {
        $function_name = substr($file, 0, strpos($file, ".class.php"));
        $functions_list[mb_strtolower($function_name, "UTF-8")] = $file;
      }
    }
    if(empty($functions_list))  {
      die(red."CRITICAL ERROR:".resetColor. $lang['function_list_creator']['load_error']);
    }else {
      print green."INFO:".resetColor. $lang['function_list_creator']['load_success'] . "\n";
    }
    closedir($dh);
  }else {
    die(red."CRITICAL ERROR:".resetColor. $lang['function_list_creator']['open_folder_error']);
  }
}else {
  die(red."CRITICAL ERROR:".resetColor. $lang['function_list_creator']['open_folder_error']);
}


//**************************************************************************************
//*********************************** Commands Mode ************************************
//**************************************************************************************
if($start_options['startmode'] == "commands") {

  require($paths['files']['commands-core']);
  $multibotObject = new commandsCore("commands");

  $multibot_config = $multibotObject->getConfig("multibot");
  $general_config = $multibotObject->getConfig("general");
  $tsAdmin = $multibotObject->getTsAdmin();

  $instance_list = Array();

  $functionCount = 0;
  foreach($multibot_config as $function_name => $function_vars)  {
    if(isset($function_vars['general_config']['enable']))  {
      if((!empty($function_vars['general_config']['enable'])) && ($function_vars['general_config']['enable'] == true)) {
        if(isset($functions_list[$function_name])) {
          $functionCount++;
        }else {
          $multibotObject->addError($lang['commands_mode']['function_file_doesnt_exist'] . " " . $function_name);
          unset($multibot_config[$function_name]);
        }
      }
    }
  }

  if($functionCount != 0) {

    if($general_config['multibot_config']['instances'] >= $functionCount)  {
      for($i = 1; $i <= $functionCount; $i++)  {
        $instance_list[] = Array('functions' => Array(), 'weight' => 0);
      }
    }elseif($general_config['multibot_config']['instances'] > 0) {
      for($i = 1; $i <= $config['general_config']['instances']; $i++)  {
        $instance_list[] = Array('functions' => Array(), 'weight' => 0);
      }
    }else {
      $multibotObject->addError($lang['commmands_mode']['instance_count_error'], true);
    }


    foreach($multibot_config as $function_name => $function_vars) {
      if(!isset($function_vars['general_config']['weight'])) {
        $function_vars['general_config']['weight'] = 1;
        $multibotObject->addError($lang['commmands_mode']['empty_weight'] . " " . $function_name);
      }

      if(!isset($function_vars['general_config']['primary_instance']))  {
        $function_vars['general_config']['primary_instance'] = false;
      }

      if($function_vars['general_config']['enable'] && $function_vars['general_config']['primary_instance'])  {
        $instance_list[0]['functions'][] = $function_name;
        $instance_list[0]['weight'] += $function_vars['general_config']['weight'];
        if($general_config['multibot_config']['protect_primary_instance']) {
          $instance_list[0]['weight'] += 1000;
        }
      }elseif($function_vars['general_config']['enable'])  {
        $index = getSmallerIndex($instance_list);
        $instance_list[$index]['functions'][] = $function_name;
        $instance_list[$index]['weight'] += $function_vars['general_config']['weight'];
      }
    }


    foreach($instance_list as $instance)  {
      $multibotObject->createInstance($instance);
    }
  }else {
    $multibotObject->addInfo($lang['commands_mode']['only_commands']);
  }

  //*****************************************************************************
  //*********************************** Loop ************************************
  //*****************************************************************************
  $tsAdminSocket = $multibotObject->getTsAdminSocket();
  $whoAmi_timer = date('r', time() + 120);
  sendCommand("servernotifyregister event=textprivate");
  while(true) {
    $socket_data = getData();

    if(is_array($socket_data) && !empty($socket_data))  {
      if(array_key_exists("notifytextmessage", $socket_data)) {
        sendCommand("servernotifyunregister");

        $status = $multibotObject->executeCommand($socket_data);
        if($status == "4")  {
          $tsAdmin->sendMessage(1,$socket_data['invokerid'], $lang['commands_mode']['chat_command_doesnt_exist']);
        }elseif ($status == "2") {
          $tsAdmin->sendMessage(1,$socket_data['invokerid'], $lang['commands_mode']['chat_no_permission']);
        }elseif ($status == "3") {
          $tsAdmin->sendMessage(1,$socket_data['invokerid'], $lang['commands_mode']['chat_permission_error']);
        }elseif($status == false) {
          $tsAdmin->sendMessage(1,$socket_data['invokerid'], $lang['unknown_error']);
        }
        sendCommand("servernotifyregister event=textprivate");
      }
    }
    //Sprawdza kim jest aby achować połączenie z serwerem
    if($whoAmi_timer < date('r'))  {
      $tsAdmin->whoAmI();
      $whoAmi_timer = date('r', time() + 120);
    }
    usleep(500000);
  }


//**************************************************************************************
//*********************************** Multibot Mode ************************************
//**************************************************************************************
}elseif($start_options['startmode'] == "multibot")  {

  require($paths['files']['multibot-commands']);
  require($paths['files']['multibot-core']);
  $multibotObject = new multibotCore("multibot");

  $multibot_config = $multibotObject->getConfig("multibot");
  $socket = $multibotObject->getInternalSocket();
  $tsAdmin = $multibotObject->getTsAdmin();


  //****************************************************************************************
  //*********************************** Function loader ************************************
  //****************************************************************************************
  foreach($functions_list as $function_name => $function_file) {
    print green . "LOAD FUNCTION: " . resetColor . $function_name . "\n";
    require_once($paths['folders']['functions'] . $function_file);
  }


  //*******************************************************************************************
  //*********************************** Multibot Controler ************************************
  //*******************************************************************************************
  foreach($vars as $function_name => $vars_list) {
    unset($vars[$function_name]);
    $vars[mb_strtolower($function_name, "UTF-8")] = $vars_list;
  }

  $vars_list = Array();

  $socket_timer = date('r', time() + 10);



  sleep(1);

  if(!$socket_info = socket_read($socket, 2048)) {
    $multibotObject->addError($lang['multibot_mode']['instance_read_error'], true);
  }else {
    $multibotObject->addInfo($lang['multibot_mode']['instance_read_success']);
  }

  if(!empty($socket_info)) {
    $socket_info = preg_replace('/\s+/', '', $socket_info);
    $socket_info = mb_strtolower($socket_info, "UTF-8");
    $functions = explode(",", $socket_info);

    $multibotObject->setName($functions[0]);
    $multibot_config = $multibotObject->getConfig("multibot");
    unset($functions[0]);



    $user_info = $tsAdmin->whoAmi();

    $nick = $user_info['data']['client_nickname'];



    foreach($functions as $function_name) {
      $functions_to_start[mb_strtolower($function_name. "UTF-8")] = mb_strtolower($function_name, "UTF-8");
    }

    refreshVarsList();

    socket_write($socket, $nick, strlen($nick));
  }


  //**************************************************************************************
  //****************************** Function Object Creator *******************************
  //**************************************************************************************
  foreach($functions_to_start as $function_name)  {
    $$function_name = new $function_name($multibotObject);
  }


  //*************************************************************************************
  //*************************************** Loop ****************************************
  //*************************************************************************************
  while(true) {
    sleep(1);
    $buffer = socketRead($socket);

    if(!empty($buffer)) {
      $buffer = explode(" ", $buffer);
      commands();
      if($break) {
        break;
      }
    }
    foreach($vars_list as $vars_list_value => $vars_list_temp)  {
      foreach($vars_list_temp as $var_name => $var_refresh_time)  {
        $multibotObject->refresh($var_name, $var_refresh_time);
      }

      $refresh_time = $multibot_config[$vars_list_value]['general_config']['refresh'];
      $$vars_list_value->start($refresh_time);
    }
  }


//*************************************************************************************
//************************************ Debug Mode *************************************
//*************************************************************************************
}elseif($start_options['startmode'] == "debug")  {
  require($paths['files']['multibot-core']);
  $multibotObject = new multibotCore("commands");

  $multibotObject->getserverinfo();



}









?>
