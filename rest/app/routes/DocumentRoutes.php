<?php

use \Firebase\JWT\JWT;

Flight::before('start', function(&$params, &$output){
    if(strpos(Flight::request()->url, '/documents') === 0 ){
        $headers = apache_request_headers();
        $method = Flight::request()->method;

        try {
            $user = (array)JWT::decode($headers['Bearer'], JWT_SECRET, array('HS256'));
            $method = strcmp($method, 'POST') == 0 || strcmp($method, 'DELETE') == 0;
            if(strcmp($user['type'], 'INTERN') != 0 && $method){
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
 * @OA\Post(
 *      path="/documents/upload",
 *      tags={"document"},
 *      summary="API for uploading documents to intenrship.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/DocumentUpload")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="Successfully uploaded documnet",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="Coudn't upload the document",
 *      ),

 * )
 */

Flight::route('POST /documents/uploads', function(){
    try{
        $dao = new DocumentsDao();
        $request = Flight::request();
        $file = $request->data->getData();
        $file['intern_id'] = Flight::get('user')['id'];
        if($file['file_type'] != "application/pdf" && $file['file_type'] != "image/png"){
            Flight::halt(403, json_encode(['msg'=>"Invalid data type"]));
        }
        $allowed_fields = ["document", "intern_id", "document_name", "type"];
        $file = $dao->insert(array_intersect_key($file, array_flip($allowed_fields)));
        Flight::output($file);

    }catch (Exception $e) {
            Flight::halt(403, json_encode(['msg'=>$e->getMessage()]));
    }
    
});

/**
 * @OA\Get(
 *      path="/documents/uploads/datatable",
 *      tags={"document"},
 *      summary="API for getting document uploaded by intern from database for datatable.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived document."
 *      ),
 *    @OA\Parameter(
 *          name="intern_id",
 *          in="query",
 *          required=true,
 *          description="ID of intern",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *    @OA\Parameter(
 *          name="type",
 *          in="query",
 *          required=true,
 *          description="type of requested document",
 *          @OA\Schema(
 *              type="string",
 *              default=0
 *          )
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 *      
 * )
 */

Flight::route('GET /documents/uploads/datatable', function(){
    $dao = new DocumentsDao();
    $params = Flight::request()->query->getData();
    $res = $dao->get_count($params);
    $total_num = $res[0]['count'];
    $data = $dao->get_all($params);
    Flight::output(['data'=> $data, 'recordsTotal' => $total_num, 'recordsFiltered' => $total_num, 'draw' => $params['draw'] ]);
});

/**
 * @OA\Get(
 *      path="/documents/uploads",
 *      tags={"document"},
 *      summary="API for getting document uploaded by intern from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived document."
 *      ),
 *    @OA\Parameter(
 *          name="intern_id",
 *          in="query",
 *          required=true,
 *          description="ID of intern",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *    @OA\Parameter(
 *          name="type",
 *          in="query",
 *          required=true,
 *          description="type of requested document",
 *          @OA\Schema(
 *              type="string",
 *              default=0
 *          )
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 *      
 * )
 */

Flight::route('GET /documents/uploads', function(){
    $dao = new DocumentsDao();
    $params = Flight::request()->query->getData();
    $data = $dao->get_all($params);
    Flight::output(['data'=> $data]);
});

/**
 * @OA\Get(
 *      path="/documents/uploads/intern/{id}",
 *      tags={"document"},
 *      summary="API for getting document uploaded by intern from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived documents."
 *      ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of intern",
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

Flight::route('GET /documents/uploads/intern/@id', function($id){
    $dao = new DocumentsDao();
    Flight::output($dao->get_all(['intern_id' => $id]));
});

/**
 * @OA\Get(
 *      path="/documents/uploads/{id}",
 *      tags={"document"},
 *      summary="API for getting document by id from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived document."
 *      ),
 *       @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of document",
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

Flight::route('GET /documents/uploads/@id', function($id){
    $dao = new DocumentsDao();
    Flight::output($dao->get_by_id($id));
    
});

/**
 * @OA\Delete(
 *      path="/documents/uploads/{id}",
 *      tags={"document"},
 *      summary="API for deleteing a document in database.",
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of document",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="Document deleted successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The document couldn't be deleted.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('DELETE /documents/uploads/@id', function($id){
    $dao = new DocumentsDao();
    $dao->delete($id);
});