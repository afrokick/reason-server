<?php

class NetResponse
{
	private $mas = array();
	
	public function AddParam($key, $val){
            $this->mas[$key] = urlencode($val);
	}
	
        public function AddError($val){
            $this->AddParam("error", $val);
        }
        
        public function AddMethod($val){
            $this->AddParam("method", $val);
        }
        
	public function SendResponse(){
		$res = "";
		foreach ($this->mas as $key => $val) {
			$res = $res.strtolower($key)."=".$val."&";
		}
                if(strlen($res) > 0)
                    echo substr($res,0,-1);
	}
        
        public function AddSendResponse(){
            if(count($this->mas) > 0){
                echo "&";
                $this->SendResponse();
            }
        }
}

function GetTask($user_id){
    $cur_task = DB::GetFetchArray("SELECT id_task FROM current_tasks WHERE id_user=$user_id");
    $resp = new NetResponse;    
    //если нет текущего задания
    if(!$cur_task){
        //$row_count = mysql_result(DB::SendQuery("SELECT COUNT(id) FROM tasks WHERE (`enable`=1 AND `id` NOT IN(SELECT id_task FROM completed_tasks WHERE id_user='$user_id') AND `id` NOT IN(SELECT id_task FROM skipped_tasks WHERE id_user='$user_id')) ORDER BY `level` DESC"), 0);
        //$task = DB::GetFetchArray("SELECT id, text, ranking FROM tasks WHERE `enable`=1 AND `id` NOT IN(SELECT id_task FROM completed_tasks WHERE id_user='$user_id') AND `id` NOT IN(SELECT id_task FROM skipped_tasks WHERE id_user='$user_id') ORDER BY `level` DESC LIMIT ".rand(0, $row_count-1).", 1");
                    
        $task = DB::GetFetchArray("SELECT id, text, ranking FROM tasks WHERE `enable`=1 AND `id` NOT IN(SELECT id_task FROM completed_tasks WHERE id_user='$user_id') AND `id` NOT IN(SELECT id_task FROM skipped_tasks WHERE id_user='$user_id') ORDER BY `level` ASC LIMIT 1");
        if($task){
            $task_id = $task["id"];
            $query = DB::SendQuery("INSERT INTO current_tasks (id_user, id_task) VALUE ($user_id, $task_id)");
            $task_text = $task["text"];
            $task_ranking = $task["ranking"];
            
            $resp->AddParam("task", $task_text);
            $resp->AddParam("rank", $task_ranking);
        }
        //не можем дать задание(нет заданий или время)
        else{
            
        }
    }
    //если есть текущее задание
    else{
        $task_id = $cur_task["id_task"];
        $task = DB::GetFetchArray("SELECT text, ranking FROM tasks WHERE id=$task_id");
        $task_text = $task["text"];
        $task_ranking = $task["ranking"];
        $resp->AddParam("task", $task_text);
        $resp->AddParam("rank", $task_ranking);
    }
    
    return $resp;
}
?>