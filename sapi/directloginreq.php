<?php

if (!isset($_REQUEST['username']))
{
    exit;
}

$username = $_REQUEST['username'];

// Server limited
require_once "inc_server.php";

// Do not load FS ...
$RUNTIME_NOSETUPFS = true;

// Init owncloud
require_once('../lib/base.php');

function get_rand($num, $type)
{
    $str_n = '0123456789';
    $str_e = 'abcdefghijklmnopqrstuvwxyz';
    $str_E = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $result = '';

    switch ($type) {

        // 數字
        case 1:
            $charset = $str_n;
            break;

            // 英文小寫
        case 2:
            $charset = $str_e;
            break;

            // 英文大寫
        case 3:
            $charset = $str_E;
            break;

            // 數字+英文大小寫
        case 4:
            $charset = $str_n . $str_e . $str_E;
            break;

            // 英文大小寫
        case 5:
            $charset = $str_e . $str_E;
            break;

            // 數字+英文小寫
        case 6:
            $charset = $str_n . $str_e;
            break;
    }

    $len_charset = strlen($charset);

    for ($j = 0; $j < $num; $j++)
    {
        $result .= substr($charset, rand(0, ($len_charset - 1) ), 1);
    }

    return $result;
}

if (OC_User::userExists( $username ))
{
    
    $counts = rand(16,32);
    $token = get_rand($counts, 4);
    
    $query = OC_DB::prepare( 'DELETE FROM *PREFIX*directlogin WHERE uid = ?' ); // TIME_TO_SEC(TIMEDIFF(now(), createtime)) > 30 or
    $query->execute( array( $username ));
    
    $query = OC_DB::prepare( 'INSERT INTO *PREFIX*directlogin (uid, token) values (?, ?)' );
    $query->execute( array( $username, $token ));
    
    OC_JSON::success(array( "name" => $username,  "token" => urlencode(base64_encode($token))));

}
elseif (OC_User::userExists( '##'.$username ))
{
    OC_JSON::error(array( "name" => $username,  "message" => "User already blocked!"));
}
else
{
    OC_JSON::error(array("data" => array( "message" => "User not Exist!" )));
}