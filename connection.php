<?php 

class connection{
	public $fd;
   
    public $last_read_time;

	__construct($fd){
       
       $this->fd = $fd;

	}


	public function rec_time($time){
       $this->last_read_time = $time;
	} 

}