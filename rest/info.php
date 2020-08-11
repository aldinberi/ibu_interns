<?php 
// error_reporting(1);
// ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

// foreach ($results as $item) {
//   echo $item['volumeInfo']['title'], "<br /> \n";
// }

// // echo password_hash('1234', PASSWORD_DEFAULT);

// if(password_verify('1234', '$2y$10$i.bHenwYZOXrZZ09Ay2Dx./TEJvD4fi4yW6Za/X5oiVC/XpJssRyW')){
//     echo "radi";
// }
    



phpinfo();
/*header("Content-Type: application/json");
http_response_code(200);
echo json_encode([ "hello" => "world" ]);
*/

/*if (!function_exists('getallheaders')) {
    function getallheaders() {
    $headers = [];
foreach($_SERVER as $name => $value) {
    if($name != 'HTTP_MOD_REWRITE' && (substr($name, 0, 5) == 'HTTP_' || $name == 'CONTENT_LENGTH' || $name == 'CONTENT_TYPE')) {
        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', str_replace('HTTP_', '', $name)))));
        if($name == 'Content-Type') $name = 'Content-type';
        $headers[$name] = $value;
    }
}
    return $headers;
    }
}
print_r(getallheaders());*/

/*Flight::json([ "hello" => "world" ]);
Flight::start();
*/
/*print_r($_SERVER);*/
?>