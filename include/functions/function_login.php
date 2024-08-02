<?php
function getip()
{
    if (isset($_SERVER))
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    }
    else
    {
        if (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_CLIENT_IP'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        else
        {
            $ip = getenv('REMOTE_ADDR');
        }
    }

    return $ip;
}

function zufalls_string ($max = 8)
{
    srand ((double)microtime()*1000000);
    $result = "";
    $zufall = rand();
    $max    = intval($max);

    $result = substr(md5($zufall) , 0 , $max);

    return $result;
}

function hash_pad($hash)
{
    return str_pad($hash, 20);
}

function mksecret($len = 20)
{
    $result = "";
    $len    = intval($len);

    for ($i = 0; $i < $len; $i++)
      $result .= chr(mt_rand(0, 255));

    return $result;
}
?>