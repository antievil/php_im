<?php
function msg_encode($sign,$type,$msg,$magic=PACKET_MAGIC){

return  pack('nnnI',$magic,$sign,$type,strlen($msg)).$msg;
}   