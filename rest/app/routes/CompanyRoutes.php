<?php

use \Firebase\JWT\JWT;


/**
 * Flight endpoints for company CRUD operations.
 * 
 */


Flight::before('start', function(&$params, &$output){
    if(strpos(Flight::request()->url, '/companies') === 0 ){
        $headers = apache_request_headers();
        $method = Flight::request()->method;


        try {
            $user = (array)JWT::decode($headers['Bearer'], JWT_SECRET, array('HS256'));
            $method = strcmp($method, 'PUT') == 0 || strcmp($method, 'POST') == 0 || strcmp($method, 'DELETE') == 0;
            if(strcmp($user['type'], 'PROFESSOR') != 0 && strcmp($user['type'], 'COMPANY_USER') != 0 && $method){
                Flight::halt(403, json_encode(['msg' => "Invalid access"]));
                die;
            }
            Flight::set('user', $user);
        } catch (\Exception $e) {
            Flight::halt(403, json_encode(['msg' => "Invalid access aaaa"]));
            die;
        }
    }
});

  /**
 * @OA\Get(
 *      path="/companies",
 *      tags={"company"},
 *      summary="API for getting all companies from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived companies."
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /companies', function(){
    $params = Flight::request()->query->getData();
    $dao = new CompanyDao();
    Flight::output($dao->get_all($params['status']));
});

  /**
 * @OA\Get(
 *      path="/companies/datatables",
 *      tags={"company"},
 *      summary="API for company datatable from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived companies."
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /companies/datatables', function(){
    $dao = new CompanyDao();
    $params = Flight::request()->query->getData();
    $res = $dao->get_count($params);
    $total_num = $res[0]['count'];
    $data = $dao->get_all($params);
    Flight::output(['data'=> $data, 'recordsTotal' => $total_num, 'recordsFiltered' => $total_num, 'draw' => $params['draw'] ]);
});

/**
 * @OA\Get(
 *      path="/companies/{id}",
 *      tags={"company"},
 *      summary="API for getting company by id from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived company."
 *      ),
 *       @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of company",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 *      
 * )
 */

Flight::route('GET /companies/@id', function($id){
    $dao = new CompanyDao();
    Flight::output($dao->get_by_id($id));
    
});


/**
 * @OA\Post(
 *      path="/companies",
 *      tags={"company"},
 *      summary="API for inserting company to database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/CompanyInsert")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="Company inserted successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The couldn't be inserted.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('POST /companies', function(){
    $dao = new CompanyDao();
    $userDao = new UserDao();
    $request = Flight::request();
    $company = $request->data->getData();

    $allowed_fields = ['name', 'address', 'email', 'phone', 'website'];
    Flight::validate(CompanyInsert::class, $company);
    $company = $dao->insert(array_intersect_key($company, array_flip($allowed_fields)));
    $userDao->update(['id' => Flight::get('user')['id'], 'company_id' => $company['id']]);
    Flight::output($company);
});

/**
 * @OA\Put(
 *      path="/companies/{id}",
 *      tags={"company"},
 *      summary="API for updating company in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/CompanyInsert")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of company",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="Company updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The company couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /companies/@id', function($id){
    $dao = new CompanyDao();
    $request = Flight::request();
    $company = $request->data->getData();
    Flight::validate(CompanyUpdate::class, $company);
    $company['id'] = $id;
    $allowed_fields = ['name', 'address', 'email', 'phone', 'website', 'id', 'status'];
    $dao->update(array_intersect_key($company, array_flip($allowed_fields)));
    Flight::output($company);
});
?>