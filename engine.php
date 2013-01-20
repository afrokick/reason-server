<?php
header('Content-type: text/html; charset=utf-8');
include("database.php");
include("lib.php");
$resp = new NetResponse;
$method = $_GET["method"];

//function Pr
switch ($method){
    case "auth" :
        //тип авторизации
        $auth_type = $_GET["auth_type"];
        switch ($auth_type){
            case "stand_alone":
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
                        $resp->AddParam("method", $method);
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
                
                $resp->AddParam("method", $method);
                $resp->AddParam("id",$user_id);
                $cur_task = DB::GetFetchArray("SELECT id_task FROM current_tasks WHERE id_user = $user_id");
                if($cur_task){
                    $task_id = $cur_task["id_task"];
                    $task = DB::GetFetchArray("SELECT text, ranking FROM tasks WHERE id=$task_id");
                    $task_text = $task["text"];
                    $task_ranking = $task["ranking"];
                    $resp->AddParam("task", $task_text);
                    $resp->AddParam("rank", $task_ranking);
                }
                else{
                    $row_count = mysql_result(DB::SendQuery("SELECT COUNT(id) FROM tasks WHERE (`enable`=1 AND `id` NOT IN(SELECT id_task FROM completed_tasks WHERE id_user='$user_id')) ORDER BY `level` DESC"), 0);
                    
                    $task = DB::GetFetchArray("SELECT id, text, ranking FROM tasks WHERE `enable`=1 AND `id` NOT IN(SELECT id_task FROM completed_tasks WHERE id_user='$user_id') ORDER BY `level` DESC LIMIT ".rand(0, $row_count-1).", 1");
                    $task_id = $task["id"];
                    $query = DB::SendQuery("INSERT INTO current_tasks (id_user, id_task) VALUE ($user_id, $task_id)");
                    $task_text = $task["text"];
                    $task_ranking = $task["ranking"];
                    $resp->AddParam("task", $task_text);
                    $resp->AddParam("rank", $task_ranking);
                }
                $resp->SendResponse();
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
  case "update" :
    break;
  default:
    echo "method=auth&result=nomethod";
    break;
}
?>