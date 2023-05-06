<?php
header("Content-Type:application/json;charset=utf-8");
date_default_timezone_set('Asia/Shanghai');
header("Access-Control-Allow-Origin: *");
function send_post_lw($post_data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://lite.duckduckgo.com/lite/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    $headers = array();
    $headers[] = "Host: lite.duckduckgo.com";
    $headers[] = "Content-Type: application/x-www-form-urlencoded";
    $headers[] = "Origin: https://lite.duckduckgo.com";
    $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:107.0) Gecko/20100101 Firefox/107.0";
    $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8";
    $headers[] = "Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $content=array(
            'code' => "500",
            'msg' => "访问出错");
        print_r(json_encode($content,JSON_UNESCAPED_UNICODE));
        exit();
    }
    curl_close($ch);
    return $result;
}
@$message=$_REQUEST['message'];
if(empty($message)){
    $content=array(
        'code' => "203",
        'msg' => "请输入待回答的问题");
    print_r(json_encode($content,JSON_UNESCAPED_UNICODE));
}
else{
    $message_bm=urlencode($message);
    $post_data="q=$message_bm&df=&kl=wt-wt";
    $data=send_post_lw($post_data);
    preg_match_all("/<a rel=\"nofollow\" href=\"(.*?)\" class=\'result-link\'>.*<\/a>/",$data,$urlss);
    preg_match_all("/<td class=\'result-snippet\'>\s*(.*?)\s*<\/td>/",$data,$contentss);
    $urls=$urlss[1];
    $contents=$contentss[1];
    $array=array();
    $date = date('Y/m/d');
    for($i=0;$i<3;$i++){
        $js=$i+1;
        $content=$contents[$i];
        $url=$urls[$i];
        $content_bq=strip_tags($content);
        $string="[$js]:\"$content_bq\"\nURL:$url";
        array_push($array,$string);
    }
    $allcontents=implode("\n\n",$array);
    $resultwb="Web search results:\n\n".$allcontents."\n\nCurrent date:$date\n\nInstructions: Using the provided web search results, write a comprehensive reply to the given query. Make sure to cite results using [[number](URL)] notation after the reference. If the provided search results refer to multiple subjects with the same name, write separate answers for each subject.\n\nQuery: $message\n\nReply in 中文";
    $content = array(
        'code' => "200",
        'msg' => "获取成功",
        'message'=>"$message",
        'online'=>"$resultwb"
    );
    print_r(json_encode($content,JSON_UNESCAPED_UNICODE));
}
?>
