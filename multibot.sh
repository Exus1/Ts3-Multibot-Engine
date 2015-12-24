if [ $1 = 'stop' ]
    then
      NAZWA=`screen -list | grep 'ExusMultibotInstance' | cut -d . -f1`
      kill -3 $NAZWA

      NAZWA=`screen -list | grep 'ExusMultibot' | cut -d . -f1`
      kill -3 $NAZWA
    fi

if [ $1 = 'start' ]
    then
	     screen -A -m -d -S ExusMultibot php Core/commands-core.php
    fi
