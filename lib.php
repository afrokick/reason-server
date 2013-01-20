<?php
class NetResponse
{
	private $mas = array();
	
	public function AddParam($key, $val){
            $this->mas[$key] = urlencode($val);
	}
	
        public function AddError($val){
            $this->AddParam("method", $val);
        }
        
	public function SendResponse(){
		$res = "";
		foreach ($this->mas as $key => $val) {
			$res = $res.strtolower($key)."=".$val."&";
		}
		echo substr($res,0,-1);;
	}
}
?>