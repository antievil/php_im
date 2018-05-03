<?php
 $user_online= array();
 $token_pool = array();

//socket function add @
$dbconnect = mysqli_connect('p:127.0.0.1','root','root','im') or die('Unale to connect');
mysqli_query($dbconnect,"set names utf8");

$read  = array();
$write = array();
$except = array();

$s_socket_uri = 'tcp://127.0.0.1:8081';
$s_socket = stream_socket_server($s_socket_uri, $errno, $errstr) OR
    trigger_error("Failed to create socket: $s_socket_uri, Err($errno) $errstr", E_USER_ERROR);

        
$buf = ''; 
$read[] = $s_socket;

while(1){

        $ret = stream_select($read, $write, $except, 0, 100000000);
        // add 信号中断系统调用的处理
        if (!$ret) {
            continue;
        }

        if ($read) {
            foreach ($read as $fd) {

                if ($fd == $s_socket){
                    $connection = stream_socket_accept($s_socket, 60, $peer);
                    echo "accept client".$connection;
                    $read[] = $connection;
                    echo "-----------";
                    var_dump($read);

                }else{
                        //解决方案2 先读取协议头字节长度
                        //然后再根据协议头里的bodylength读取剩下的消息
                        //这样就避免处理粘包了。
                        //stream_socket_recvfrom 如果内核缓冲小于指定长度，会等待多长时间超时
                        //所以虽然一定返回指定长度 存疑 仍然要循环判断得到的长度
                        //false的情况是套接字断开吗 close fd?
                        echo "getinto con--------";
                        if (false !== ($header = stream_socket_recvfrom($fd,8))) {


                                  //找到数据包头位置，第一个magic?判断从此处开始的长度是否大于协议头长度。
                                 //通过0xABCD确定协议包开始位置，但是如果消息中有0xabcd,ji
                                 /* if($magic_pos=strpos($bytes,0xabcd)){
                                     $leave=substr($bytes,$magic_pos);
                                  }else{
                                    continue;
                                  }*/

                                  // 数据包长度小于协议头长度就跳过 单位字节
                                  if(strlen($header) <  8){
                                      continue;
                                  }



                                  $resarr = unpack('nmagic/ntype/Ibodylength',$header);
    

                                 //$resarr = unpack('nmagic/ntype/Ibodylength/a*msg',$buf);

                                 $magic = $resarr['magic'];
                                 $type = $resarr['type'];
                                 $msglength = $resarr['bodylength'];
                                 
                                 if (false !== ($msg = stream_socket_recvfrom($fd,$msglength))) {
                                      
                                      if(strlen($msg) < $msglength)continue;



                                 }

                                 //$msg = $resarr['msg'];

                                /* if($magic !== 0xabcd){
                                    continue;
                                 }
*/
                            //登录
                            if($type == 0x01 ){
                                $login_msg=explode(";",$msg);
                                $id = $login_msg[0];
                                $password = $login_msg[1];

                                $sql = "select * from user where userid='" .$id."'";
                                $result = mysqli_query($connect,$sql);
                                $row = mysqli_fetch_row($result);
                                if(!$row){
                                  stream_socket_sendto($connection,'0');

                                }
                                //md5
                                if($password != $row['password']){
                                  stream_socket_sendto($connection,'0');

                                }

                                  $user_online[$id] = $connection;
                                  stream_socket_sendto($connection,'1');
                                  $token = create_token();
                                  $token_pool[$connection] = $token;
                                  stream_socket_sendto($connection,$token);

                                  echo "iamhere";
                                  var_dump($user_online);
                            }
                            //发送消息
                            if($type == 0x02) {
                              $fpos=strpos($msg,';');
                              $postID = substr($msg, 0,$fpos -1);
                              $left = substr($msg, $fpos + 1);
                              $spos = strpos($left,';');

                               $toId =  substr($left,0,$spos - 1);
                               $toMsg = substr($left,$spos + 1);
                               
                               if(isset($user_online[$toId])){
                               $connection = $user_online[$toId]
                                
                             }else{
                              //不在线，存入数据库
                             }


                            }
                              
                            //断线重连
                            if($type == 0x02){
                                if(in_array($msg,$token_pool)){

                                }
                            }
                        }
                        else {
                                    echo "socket_recv() failed; reason: " . socket_strerror(socket_last_error($fd)) . "\n";
                        }
                }
            }
        }

        if($except) {
            foreach($except as $fd) {
                $fd_key = (int) $fd;

                if(isset($user_online[$fd_key])){
                    fclose($fd_key);
                    unset($token_pool[$fd_key]);

                }
            }
        }
}




function create_token(){
     
    $token=mt_rand();

    while(in_array($token, $token_pool)){
        $token = mt_rand();
    }

    return $token;
}       



    