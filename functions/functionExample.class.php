<?php
//Function name must be the same as config file name and function file name. Excluding the capitalization.
//The file extension must be class.php.
class yourFunctionName extends start {

  protected $start_timer = 0; //Timer for start function
  protected $function_name = "Function Name"; //Name for start function

  function start_function() {
    //The function performed at startup
  }

  function __construct() {
    //The function performed at first startup
  }
}
?>
