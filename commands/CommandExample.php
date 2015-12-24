<?php
/** $tsAdmin - Reference to ts3admin object
  *
  * commandInfo
  * Array(
  *   [command] => Array(
  *     0 => "command_name",
  *     1 => "arg1",
  *     2 => "arg2",
  *     ...
  *    )
  *
  *   [clientId] => "Invoker ID"
  *   [clientUID] => "Invoker UID"
  *   [clientName] => "Invoker Name"
  * );
  *
  * Available functions:
  *
  * - $this->getInstanceId(string $function_name)
  * - $this->getInstanceList()
  * - $this->executeCommand(string $command_name)
  * - $this->createInstance(Array $instance_config)
  *
  * Instance_config Array(
  *   [functions] => Array(
  *     [0] => [function_name],
  *     [2] => [function_name]
  *   )
  *   [weight] => functions_weight
  * );
  *
  * - $this->killInstance(int $id)
  * - $this->sendToInstance(int $id, string $msg)
  * - $this->readFromInstance(int $id)
  * - $this->getConfig(string $mode) - modes: "multibot", "commands", "all"
  * - $this->getCommandList()
  * - $this->setConfig(string $mode) - modes: "multibot", "commands"
  * - $this->getLang()
  * - $this->getPaths()
  * - $this->getTsAdmin()
  */

// Code
$tsAdmin->gm("Hello World");
?>
