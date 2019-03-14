<?php
/**
 * Created by PhpStorm.
 * User: ocdpunk
 * Date: 2019-03-14
 * Time: 20:34
 */
function result($code, $msg, $data)
{
    return json_encode(['code' => $code, 'msg' => $msg, 'data' => $data]);
}

function curl_get($url, $data, $token, $type = 'GET')
{
    $curl = curl_init();
    $user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36";
    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: token ' . $token]);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
    curl_setopt($curl, CURLOPT_URL, $url . '?' . http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    $http_code = (string)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return result($http_code, '返回结果', $output);

}


function main($token)
{
    $start = microtime(true);
    $feed = json_decode(curl_get('https://api.github.com/users/ocdpunk/received_events', ['per_page' => 100], $token), true);

//如果没有获取到动态
    if (in_array(substr($feed['code'], 0, 1), ['4', '5'])) {
        echo result(500, '获取动态发生错误', $feed['data']);
    }
    $num = 0;
    $false = 0;
    $already = 0;
    $zero = 0;
    foreach (json_decode($feed['data'], true) as $k => $v) {
        $is_star = curl_get('https://api.github.com/user/starred/' . $v['repo']['name'], [], $token);
        if (json_decode($is_star, true)['code'] == 204) {
            echo '已关注' . $v['repo']['name'] . "\n";
            $already++;
            continue;
        }
        //关注人数是否为零
        $count_star_user = curl_get('https://api.github.com/repos/' . $v['repo']['name'] . '/stargazers', [], $token);
        $star_numser = json_decode(json_decode($count_star_user, true)['data'], true);
        if (count($star_numser) == 0) {
            echo "关注人数为0不关注".$v['repo']['name'] . "\n";
            $zero++;
            continue;
        }
        //关注操作
        $do_star = curl_get('https://api.github.com/user/starred/' . $v['repo']['name'], [], $token, 'PUT');
        $do_star_http_code = json_decode($do_star, true)['code'];
        if ($do_star_http_code != 204) {
            echo '关注' . $v['repo']['name'] . "失败\n";
            $false++;
            continue;
        }
        echo '关注' . $v['repo']['name'] . "成功\n";
        $num++;
    }
    $end = microtime(true);
    $time = round(($end - $start), 3);
    echo "完成所有收录,花费时间 $time 秒，检查" .count(json_decode($feed['data'], true)) ."，项目共star $num 项目，关注0的 $zero 个项目，关注失败 $false 个项目。\n";
}

$token = $argv[1];

main($token);