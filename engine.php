<?php
header('Content-type: text/html; charset=utf-8');
include("database.php");
include("lib.php");
$method = $_GET["method"];

//function Pr
switch ($method){
    case "auth" :
        //тип авторизации
        $auth_type = $_GET["auth_type"];
        switch ($auth_type){
            case "stand_alone":
                $resp = new NetResponse;
                
                $login = $_GET["login"];
                $pass = $_GET["pass"];
                //проверка
                $user = DB::GetFetchArray("SELECT `id_user`, `password` FROM `logins` WHERE `login` = '$login'");
                //есть в базе
                $user_id = 0;
                if($user){
                    $user_id = $user["id_user"];
                    $user_pass = $user["password"];
                    //valid auth
                    if($user_pass != $pass){
                        $resp->AddMethod($method);
                        $resp->AddError("invalidpass");
                        $resp->SendResponse();
                        exit;
                    }
                    
                    $user_data = DB::GetFetchArray("SELECT user_name, completed_tasks, skipped_tasks FROM users WHERE id = '$user_id'");
                    $resp->AddParam("name", $user_data["user_name"]);
                    $resp->AddParam("completed", $user_data["completed_tasks"]);
                    $resp->AddParam("skipped", $user_data["skipped_tasks"]);
                }else{
                    DB::SendQuery("INSERT INTO users(user_name) value ('$login')");
                    $query = DB::GetFetchArray("SELECT id FROM users WHERE user_name = '".$login."' LIMIT 1");
                    $user_id = $query['id'];
                    DB::SendQuery("INSERT INTO logins(id_user,login,password) value ('$user_id','$login','$pass')");
                    $resp->AddParam("name", $login);
                    $resp->AddParam("completed", 0);
                    $resp->AddParam("skipped", 0);
                }
                
                $resp->AddMethod($method);
                $resp->AddParam("id",$user_id);
                $newtask = GetTask($user_id);
                $resp->SendResponse();
                $newtask->AddSendResponse();
                DB::SendQuery("UPDATE users SET last_login_date=NOW() WHERE id = '$user_id'");
                break;
            case "vk":

                    break;
            case "fb":
                    break;
            case "tw":
                    break;
            default:
                    echo "method=auth&result=noauthtype";
                    break;
        }
    break;
    case "sendtask" :
        $resp = new NetResponse;
        $user_id = $_GET["userdbid"];
        $completed = $_GET["completed"];
        $cur_task = DB::GetFetchArray("SELECT id, id_task,start_date FROM current_tasks WHERE id_user=$user_id");
        //если есть текущее задание
        if($cur_task){
            $task_id = $cur_task["id_task"];
            $curtask_id= $cur_task["id"];
            $query = DB::SendQuery("DELETE FROM current_tasks WHERE `id`=$curtask_id");
            if($completed == "1"){
                $task_dur = time() - strtotime($cur_task["start_date"]);
                $query = DB::SendQuery("INSERT INTO completed_tasks(id_user,id_task,duration) VALUE ($user_id,$task_id,$task_dur)");
                $query = DB::SendQuery("UPDATE tasks SET completed=completed+1 WHERE id=$task_id");
                $query = DB::SendQuery("UPDATE users SET completed_tasks=completed_tasks+1 WHERE id=$user_id");
                //есть ли комент и оценка?
                if(isset($_GET["rank"])){
                    $task_rank = $_GET["rank"];
                    if($task_rank && $task !=0){
                        $query = DB::SendQuery("INSERT INTO task_marks(id_user,id_task,mark) VALUE ($user_id,$task_id,$task_rank)");
                    }
                }
                if(isset($_GET["comment"])){
                    $task_comment = $_GET["comment"];
                    if($task_comment && strlen($task_comment) > 0){
                        $task_comment = urldecode($task_comment);
                        $query = DB::SendQuery("INSERT INTO comments(id_task,id_user,text) VALUE ($task_id,$user_id,'$task_comment')");
                    }
                }
            }else{
                $query = DB::SendQuery("INSERT INTO skipped_tasks(id_user,id_task) VALUE ($user_id,$task_id)");
                $query = DB::SendQuery("UPDATE tasks SET skipped=skipped+1 WHERE id=$task_id");
                $query = DB::SendQuery("UPDATE users SET skipped_tasks=skipped_tasks+1 WHERE id=$user_id");
            }
            //SEND NEW TASK
            $newtask = GetTask($user_id);
            $resp->AddMethod($method);
            $resp->SendResponse();
            $newtask->AddSendResponse();
        }else{
            //hack
        }
        break;
    case "gettask":
        $resp = new NetResponse;
        $user_id = $_GET["userdbid"];
        $newtask = GetTask($user_id);
        $resp->AddMethod($method);
        $resp->SendResponse();
        $newtask->AddSendResponse();
        break;
  default:
    echo "method=".$method."&error=nomethod";
    break;
}
?>