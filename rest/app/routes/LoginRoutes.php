<?php

use \Firebase\JWT\JWT;


Flight::before('start', function(&$params, &$output){
  $is_register = Flight::request()->url == '/users'|| Flight::request()->url == '/register' ||Flight::request()->url == '/companies' && Flight::request()->method == 'POST';
  if ( Flight::request()->url != '/' && Flight::request()->url != '/login' && Flight::request()->url != '/users/send-mail' && strpos(Flight::request()->url, '/login') !== 0 && Flight::request()->url != '/login/company' && !$is_register) {
    $headers = apache_request_headers();
    try {
      $decoded = (array)JWT::decode($headers['Bearer'], JWT_SECRET, array('HS256'));
      Flight::set('user', $decoded);
    } catch (\Exception $e) {
        Flight::halt(403, json_encode(['msg' => "The token is not valid"]));
        die;
    }
  }
});

/**
 * @OA\Post(
 *      path="/login/company",
 *      tags={"login"},
 *      summary="API for loging in company user.",
 *     @OA\RequestBody(
 *          description="Request body.",
 *          @OA\JsonContent(ref="#/components/schemas/LoginUser")
 *       ),
 *
 *      @OA\Response(
 *           response=200,
 *           description="Successful login",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The couldn't be login.",
 *      )
 * )
 */

Flight::route('POST /login/company', function(){
  $login_data = Flight::request()->data->getData();
  $dao = new UserDao();
  $allowed_fields = ['email'];
  $response_data = $dao->get_login_user(array_intersect_key($login_data, array_flip($allowed_fields)));

  if(count($response_data) == 1){
    $user =$response_data[0];
    if($user['login_attempt'] >= 3 && !$dao->check_captcha($login_data['h-captcha-response'])){
      Flight::halt(403, json_encode(['msg'=>"Too many logins with account, fill captcha please try again", 'login_attempt' => $user['login_attempt']+1]));
    }
    if(password_verify($login_data['password'], $user['password'])){
      unset($user['password']);
      unset($user['login_attempt']);
      $dao->update(['id'=> $user['id'], 'login_attempt' => 0]);
      Flight::set('user', $user);
      $jwt = JWT::encode($user, JWT_SECRET);

      Flight::output(["token" => $jwt]);
    }else{
      $dao->update(['id'=> $user['id'], 'login_attempt' => $user['login_attempt']+1]);
      Flight::halt(403, json_encode(['msg'=>"Invalid username or password"]));
    }

  }else{
    Flight::halt(403, json_encode(['msg'=>"No user with that email"]));

  }
});

/**
 * @OA\Post(
 *      path="/login",
 *      tags={"login"},
 *      summary="Google API for loging in user.",
 *      @OA\Response(
 *           response=200,
 *           description="Successful login",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The couldn't be login.",
 *      )
 * )
 */

Flight::route('GET /login', function(){
  $code= Flight::request()->query['code'];
  $dao = new UserDao();
  $client = new Google_Client([
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI
  ]);

  if($code) {
    try {
      $token = $client->fetchAccessTokenWithAuthCode($code);
      $client->setAccessToken($token);
      $oauth2 = new \Google_Service_Oauth2($client);
      $userInfo = $oauth2->userinfo->get();
      if ($userInfo['email']){
        $dao = new UserDao();
        $user = $dao->check_email($userInfo['email']);
        if($user){
          Flight::set('user', $user[0]);
          $jwt = JWT::encode($user[0], JWT_SECRET);
          Flight::redirect('http://interns.ibu.edu.ba/index.html?jwt='.$jwt);
          //Flight::output(["token" => $jwt]);
        }else{
          $user['name'] = $userInfo['name'];
          $user['email'] = $userInfo['email'];
          if($userInfo['hd'] == 'stu.ibu.edu.ba'){
            $user['type'] = 'INTERN';
          } else if($userInfo['hd'] == 'ibu.edu.ba'){
            $user['type'] = 'PROFESSOR';
          }
            
          $jwt = JWT::encode($user, JWT_SECRET);
          $get_data = $dao->callAPI('GET', "https://rest.ibu.edu.ba/rest/utils/get_user_info", $jwt);
          $response = json_decode($get_data, true);
          if($response['study_year']){
            $user['year'] = $response['study_year'];
          }
          $user['department_id'] = $response['institution_id'];
          $insert_response = $dao->insert($user);
          $user['id'] = $insert_response['id'];
          $jwt = JWT::encode($user, JWT_SECRET);
          Flight::redirect('http://interns.ibu.edu.ba/index.html?jwt='.$jwt);
          //Flight::output(["token" => $jwt]);
          //$message = "You haven't been added to the system, ask your advisor to add you";
          //Flight::redirect('http://interns.ibu.edu.ba/index.html?message='.$message);
        }
      }else{
        header("Content-Type: application/json");
        Flight::halt(404, json_encode(["message" => "Invalid hack with Google"]));
      }
    } catch (\Exception $e) {
      header("Content-Type: application/json");
      Flight::halt(404, json_encode(["message" => $e]));
    }
}else {
        $client->setScopes(["email", "profile"]);
        $url = $client->createAuthUrl();
        Flight::redirect($url);

}
});
?>
