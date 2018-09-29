<?php
function server()
{
    date_default_timezone_set('PRC'); //设置时区
    set_time_limit(0);                //脚本请求时间响应无限制
    error_reporting(null);            //屏蔽错误警告
    $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP); //创建socket
    socket_set_option($socket,SOL_SOCKET,SO_REUSEADDR,1); //配置参数
    socket_set_nonblock($socket);//无阻塞
    if ($bind = socket_bind($socket,'127.0.0.1',8889)==false) { //绑定ip,端口
        echo "socket bind fail:".socket_strerror(socket_last_error($bind));
    }
    if ($listen = socket_listen($socket,4)==false) {//开始监听
        echo 'server listen fail:'.socket_strerror(socket_last_error($listen));
    }

    $socket_pool[] = $socket; //初始化连接池
    $num = 0;//记录连线用户数
    echo "欢迎来到PHP Socket测试服务。".PHP_EOL;
    do {
        $pools  = $socket_pool;
        $write  = NULL;
        $except = NULL;
        socket_select($pools,$write,$except,NULL);//
        foreach ($pools as $s) {
            if ($s == $socket) {
                $accept = socket_accept($s);
                if ($accept) {
                    $socket_pool[] = $accept;//
                    $hand = false; //初次seq
                    $user = $num = $num+1; //新用户数加1
                    echo "欢迎新用户".$user.PHP_EOL;//$user用户标识
                    $return_client = $user;
                    $res = socket_write($accept,$return_client,strlen($return_client));//发送用户标识给客户端
                    if (!$res) {
                        echo "fail to write";
                    }
                }
            } else {
                $str = socket_read($s,1024);
                if (!$hand) {
                    socket_write($s,"HTTP/1.1 200 OK\r\nContent-Length: 12\r\n\r\nhello world!",12);
//                    socket_write($s, '握手', strlen('握手'));
                    $hand = true;
                } else {
                    echo "服务端接收：".$str.PHP_EOL;
                    foreach ($socket_pool as $send) {
                        if ($send != $socket && $send != $s) { //广播数据,除自己和服务端外
                            if ($str) {
                                $return_client = '这是'.$str.PHP_EOL;
                                socket_write($send,$return_client,strlen($return_client));
                            } else {
                                echo 'close';
                                $num--;
                                socket_close($s);
                            }
                        }
                    }
                }
            }
        }
    } while(true);
    socket_close($socket);
}

server();
