<?php
error_reporting(null);
date_default_timezone_set('PRC');
$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array('sec' => 1,'usec' => 0));
socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,array('sec' => 6,'usec' => 0));
if ($coon = socket_connect($socket,'127.0.0.1',8889) == false) {
    echo 'connect error '.socket_strerror(socket_last_error($coon));
} else {
    $num = 1;
    do {
//        if ($callback = socket_read($socket,1024)) {
//            echo '群内信息: '.$callback.PHP_EOL;
//        }
//        fwrite(STDOUT,'请输入：');
//        $msg  = fgets(STDIN);
//        $msg = substr($msg, 0, -1);
//        if (base64_encode($msg) == base64_encode('end')) {
//            echo $msg;
//            socket_close($socket);
//            break;
//        }
        $msg = array('甲','乙','丙','丁','end');
        if ($callback = socket_read($socket,1024)) {
            if ($num == 1) {
                //服务端第一次回复对的信息为用户标识
                $user = intval($callback);//用户标识
                $num ++;
            }
            echo '群内信息: '.$callback.PHP_EOL;
            sleep(2);
        }
        $k = $user-1;
        if ($msg[$k] == 'end') {
            socket_close($socket);
        }
        sleep(2);
        echo '发送内容:'.$msg[$k].PHP_EOL;
        $sendMsg = '用户'.$user.'发送'.$msg[$k];
        if ($write = socket_write($socket,$sendMsg,strlen($sendMsg)) == false) {
            $error = socket_strerror(socket_last_error($write));
            echo 'fail to write '.$error;
            socket_close($socket);
        } else {
            echo "client write success ".PHP_EOL;
        }
    } while(true);
}
//            while ($callback = socket_read($socket,1024)) {
//                echo '客户端接收服务端回复信息: '.$callback.PHP_EOL;
//            }
//            socket_close($socket);
socket_close($socket);
