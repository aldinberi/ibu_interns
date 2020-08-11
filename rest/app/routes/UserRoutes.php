<?php

use \Firebase\JWT\JWT;

/**
 * Flight endpoints for company CRUD operations.
 * 
 */



Flight::before('start', function(&$params, &$output){
    if(strpos(Flight::request()->url, '/user' ) === 0 && Flight::request()->url != '/users/send-mail'){
        $headers = apache_request_headers();
        $method = Flight::request()->method;

        try {
            $user = (array)JWT::decode($headers['Bearer'], JWT_SECRET, array('HS256'));
            $method = strcmp($method, 'PUT') == 0 || strcmp($method, 'POST') == 0 || strcmp($method, 'DELETE') == 0;
            if(strcmp($user['type'], 'PROFESSOR') != 0 && strcmp($user['type'], 'COMPANY_USER') != 0 && $method){
                Flight::halt(403, json_encode(['msg' => "Invalid access"]));
                die;
            }
        } catch (\Exception $e) {
            Flight::halt(403, json_encode(['msg' => "Invalid access"]));
            die;
        }
    }
});

  /**
 * @OA\Get(
 *      path="/users",
 *      tags={"user"},
 *      summary="API for getting all users from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived users."
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /users', function(){
    $params = Flight::request()->query->getData();
    $dao = new UserDao();
    Flight::output($dao->get_all($params['company_id'], $params['department_id'], $params['internship_id']));
});

 /**
 * @OA\Get(
 *      path="/users/datatables",
 *      tags={"user"},
 *      summary="API for intern datatable from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived interns."
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /users/datatables', function(){
    $dao = new UserDao();
    $params = Flight::request()->query->getData();
    $res = $dao->get_count($params);
    $total_num = $res[0]['count'];
    $data = $dao->get_interns($params);
    Flight::output(['data'=> $data, 'recordsTotal' => $total_num, 'recordsFiltered' => $total_num, 'draw' => $params['draw'] ]);
});

 /**
 * @OA\Get(
 *      path="/users/intern/{intern_id}/internship/{internship_id}",
 *      tags={"user"},
 *      summary="API for getting all users from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived users."
 *      ),
 *  @OA\Parameter(
 *          name="intern_id",
 *          in="path",
 *          required=true,
 *          description="ID of intern",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *  @OA\Parameter(
 *          name="internship_id",
 *          in="path",
 *          required=true,
 *          description="ID of internship",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /users/intern/@intern_id/internship/@internship_id', function($intern_id, $internship_id){
    $dao = new UserDao();
    Flight::output($dao->get_interns(['intern_id' => $intern_id, 'internship_id' => $internship_id]));
});

 /**
 * @OA\Get(
 *      path="/users/interns",
 *      tags={"user"},
 *      summary="API for getting all users from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived users."
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /users/interns', function(){
    $dao = new UserDao();
    Flight::output($dao->get_interns());
});

 /**
 * @OA\Get(
 *      path="/users/intern/{id}/internships",
 *      tags={"user"},
 *      summary="API for getting all internships from intern.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived users."
 *      ),
 *  @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of intern",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /users/intern/@id/internships', function($id){
    $dao = new UserDao();
    $params = Flight::request()->query->getData();
    Flight::output($dao->get_interns_internships($id, $params['status1'], $params['status2']));
});

/**
 * @OA\Get(
 *      path="/users/{id}",
 *      tags={"user"},
 *      summary="API for getting user by id from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived user."
 *      ),
 *       @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of user",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 *      
 * )
 */

Flight::route('GET /users/@id', function($id){
    $dao = new UserDao();
    Flight::json($dao->get_by_id($id));
    
});

/**
 * @OA\Post(
 *      path="/users",
 *      tags={"user"},
 *      summary="API for inserting user to database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserInsert")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="User inserted successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user couldn't be inserted.",
 *      ),

 * )
 */

Flight::route('POST /register', function(){
    $dao = new UserDao();
    $request = Flight::request();
    $user = $request->data->getData();
    if(!$dao->check_captcha($user['h-captcha-response'])){
        Flight::halt(403, json_encode(['msg'=>"Invalid Captcha, please try again"]));
      }
    if($dao->check_password($user['password'])){
        Flight::halt(403, json_encode(['msg'=>'You can not use a weak password, try a new one']));
        die;
    }
    $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
    $allowed_fields = ['name', 'password', 'email', 'type'];
    if(sizeof($dao->check_email($user['email'])) == 0){
        Flight::validate(UserInsert::class, $user);
        $dao->insert(array_intersect_key($user, array_flip($allowed_fields)));
        unset($user['password']);
        Flight::output($user);
    }else{
        Flight::halt(403, json_encode(['msg'=>'Email already taken']));
    }
});

/**
 * @OA\Post(
 *      path="/intern/apply",
 *      tags={"user"},
 *      summary="API for appling intern to intenrship.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/InternApply")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="Successfully applied to the internship",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="Coudn't apply to the internship",
 *      ),

 * )
 */

Flight::route('POST /intern/apply', function(){
    try{
        $dao = new InternshipDao();
        $request = Flight::request();
        $internship = $request->data->getData();
        $allowed_fields = ['internship_id', 'intern_id', 'status', 'documents_id'];
        $dao->apply(array_intersect_key($internship, array_flip($allowed_fields)));
        Flight::output($internship);
    }catch (Exception $e) {
        if(strpos($e->getMessage(), "Duplicate entry") == true){
            Flight::halt(403, json_encode(['msg'=>'You already applied to this internship']));
        }else{
            Flight::halt(403, json_encode(['msg'=>$e->getMessage()]));
        }

    }
    
});


/**
 * @OA\Put(
 *      path="/users/{id}",
 *      tags={"user"},
 *      summary="API for updating user in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserInsert")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of user",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="User updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /users/@id', function($id){
    $dao = new UserDao();
    $request = Flight::request();
    $user = $request->data->getData();
    $user['id'] = $id;
    $allowed_fields = ['name', 'email', 'id', 'company_id'];
    $dao->update(array_intersect_key($user, array_flip($allowed_fields)));
    Flight::output($user);
});

/**
 * @OA\Put(
 *      path="/users/password/{id}",
 *      tags={"user"},
 *      summary="API for updating user password in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserPasswordUpdate")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of user",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="User password updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user password couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /users/password/@id', function($id){
    $dao = new UserDao();
    $request = Flight::request();
    $data = $request->data->getData();
    $user = $dao->get_by_id($id);    
    if(password_verify($data['old_password'], $user['password'])){

        $dao->update(['password' => password_hash($data['new_password'], PASSWORD_DEFAULT), 'id' => $id]);
    }else{
        Flight::halt(403, json_encode(['msg'=>'Entered worng old password']));
    }
    Flight::output($data);
});

 /**
 * @OA\Post(
 *      path="/users/send-mail",
 *      tags={"user"},
 *      summary="API for sedning recovery mail to company user",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserSendEmail")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="Successfully send email",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="Email couldn't be send.",
 *      ),

 * )
 */

Flight::route('POST /users/send-mail', function(){
    $dao = new UserDao();
    $request = Flight::request();
    $response_data = $request->data->getData();
    try {
      $dao->send_recovery_mail($response_data['email']);
      
    } catch (Exception $e) {
      Flight::halt(500, json_encode(['msg'=>$e->getMessage()]));
    }
  });

  /**
 * @OA\Put(
 *      path="/users/password-recover/{id}",
 *      tags={"user"},
 *      summary="API for recovering user password in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserPasswordUpdate")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of user",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="User password updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user password couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /users/password-recover/@id', function($id){
    $dao = new UserDao();
    $request = Flight::request();
    $data = $request->data->getData();  
    try {
  
      if($dao->check_password($data['password']))
          throw new Exception('You can not use a weak password, try a new one');
      $dao->update(['password' => password_hash($data['password'], PASSWORD_DEFAULT), 'login_attempt' => 0, 'id' => $id]);
          
    } catch (Exception $e) {
      Flight::halt(500, json_encode(['msg'=>$e->getMessage()]));
    }
  });

?>