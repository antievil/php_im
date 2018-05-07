
<?php 
//@
$fp = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
 $con=socket_connect($fp, '127.0.0.1', 8081);
//$fp = stream_socket_client("tcp://192.168.40.132:8081", $errno, $errstr, 30);
if (!$con) {
	 echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";

    //echo "$errstr ($errno)<br />\n";
} 

$account=readline("account：");
$pwd = readline("pwd：");
$msg = $account.';'.$pwd;
$len = strlen($msg);
$buff = pack('nnI',0xabcd,0x01,$len);
$buff = $buff . $msg;

$get=socket_write($fp,$msg,strlen($msg));
if($get){}else{
	echo "socket_write() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}


$buff = '';
socket_recv($fp,$buff,1,MSG_WAITALL);

if($buff == 1){
	$msg = readline("msg:");  //
}else{
	echo "login error";
}

$len = strlen($msg);
$buff = pack('nnI',0xabcd,0x01,$len);
$buff = $buff . $msg;


  /*stream_set_blocking($this->_socket, 0);
        // Compatible with hhvm
        if (function_exists('stream_set_read_buffer')) {
            stream_set_read_buffer($this->_socket, 0);
        }*/





timer(5,signalHandler);

function timer($time,$signalHandler){

    pcntl_signal(SIGALRM, $signalHandler);
    
    
    pcntl_alarm($time);
    
    

    while(true){
       pcntl_signal_dispatch(); 
       sleep(1);
    }
    

}

function signalHandler(){
  echo 1;
  pcntl_alarm(5);
}

?>