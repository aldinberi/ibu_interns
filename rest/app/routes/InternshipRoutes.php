<?php

use \Firebase\JWT\JWT;

Flight::before('start', function(&$params, &$output){
    if(strpos(Flight::request()->url, '/internships') === 0 ){
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
 * Flight endpoints for internship CRUD operations.
 * 
 */

  /**
 * @OA\Get(
 *      path="/internships",
 *      tags={"internship"},
 *      summary="API for getting all internships from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived internships."
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /internships', function(){
    $params = Flight::request()->query->getData();
    $dao = new InternshipDao();
    Flight::output($dao->get_all($params));
});

 /**
 * @OA\Get(
 *      path="/internships/datatables",
 *      tags={"internship"},
 *      summary="API for internship datatable from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived internships."
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /internships/datatables', function(){
    $dao = new InternshipDao();
    $params = Flight::request()->query->getData();
    $res = $dao->get_count($params);
    $total_num = $res[0]['count'];
    $data = $dao->get_all($params);
    Flight::output(['data'=> $data, 'recordsTotal' => $total_num, 'recordsFiltered' => $total_num, 'draw' => $params['draw'] ]);
});

/**
 * @OA\Get(
 *      path="/internships/{id}",
 *      tags={"internship"},
 *      summary="API for getting internship by id from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived internship."
 *      ),
 *       @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of insternship",
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

Flight::route('GET /internships/@id', function($id){
    $dao = new InternshipDao();
    Flight::output($dao->get_by_id($id));
    
});

/**
 * @OA\Get(
 *      path="/internships/status/{status}",
 *      tags={"internship"},
 *      summary="API for getting internship by status from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived internships."
 *      ),
 *       @OA\Parameter(
 *          name="status",
 *          in="path",
 *          required=true,
 *          description="Status of insternship",
 *          @OA\Schema(
 *              type="string",
 *              default=0
 *          )
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 *      
 * )
 */

Flight::route('GET /internships/status/@status', function($status){
  $dao = new InternshipDao();
  Flight::output($dao->get_by_status($status));
  
});


/**
 * @OA\Post(
 *      path="/internships",
 *      tags={"internship"},
 *      summary="API for inserting internship to database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/InternshipInsert")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="Internship inserted successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The internship couldn't be inserted.",
 *      ),
 * 
 *      security={
 *          {"api_key": {}}
 *      }

 * )
 */

Flight::route('POST /internships', function(){
 
    $dao = new InternshipDao();
    $request = Flight::request();
    $internship = $request->data->getData();
    $allowed_fields = ['title', 'job_description', 'start_date', 'end_date', 'status', 'mentor_id', 'company_id','department_id'];
    $internship['status'] = "PENDING";
    $internship['mentor_id'] = Flight::get('user')['id'];
    if(Flight::get('user')['company_id']){
        $internship['company_id'] = Flight::get('user')['company_id'];
    }
    Flight::validate(InternshipInsert::class, $internship);
    $dao->insert(array_intersect_key($internship, array_flip($allowed_fields)));
    Flight::output($internship);

     
});


 /**
 * @OA\Put(
 *      path="/internship/interns",
 *      tags={"internship"},
 *      summary="API for updating all users internship status.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully updated status."
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /internship/interns', function(){
    $params = Flight::request()->data->getData();
    $dao = new InternshipDao();
    Flight::output($dao->update_internship_status($params['internship_id'], $params['intern_id'], $params['status']));
});

/**
 * @OA\Put(
 *      path="/internships/{id}",
 *      tags={"internship"},
 *      summary="API for updating internship in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/InternshipInsert")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of internship",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="Internship updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The internship couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /internships/@id', function($id){
    $dao = new InternshipDao();
    $request = Flight::request();
    $internship = $request->data->getData();
  
    if($internship['status'] == null){
        $internship['status'] = "PENDING";
    }

    if($internship['status'] == "COMPLETED"){
        $dao->update_internship_status($id, $internship['intern_id'], "COMPLETED");
    }
    $internship['id'] = $id;
    $dao->update($internship);
    Flight::output($internship);
});

/**
 * @OA\Delete(
 *      path="/internships/{id}",
 *      tags={"internship"},
 *      summary="API for deleteing a internship in database.",
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of internship",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="Internship deleted successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The internship couldn't be deleted.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('DELETE /internships/@id', function($id){
    $dao = new InternshipDao();
    $dao->delete($id);
});


?>