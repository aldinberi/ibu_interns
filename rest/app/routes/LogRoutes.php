<?php

use \Firebase\JWT\JWT;

/**
 * Flight endpoints for logs CRUD operations.
 * 
 */

Flight::before('start', function (&$params, &$output) {
    if (strpos(Flight::request()->url, '/logs') === 0) {
        $headers = apache_request_headers();
        $method = Flight::request()->method;

        try {
            $user = (array) JWT::decode($headers['Bearer'], JWT_SECRET, array('HS256'));
            $method = strcmp($method, 'PUT') == 0 || strcmp($method, 'POST') == 0 || strcmp($method, 'DELETE') == 0;
            if (strcmp($user['type'], 'INTERN') != 0 && strcmp($user['type'], 'COMPANY_USER') != 0 && $method) {
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
 *      path="/logs",
 *      tags={"log"},
 *      summary="API for getting all logs from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived logs."
 *      ),
 *      @OA\Parameter(
 *          name="intern_id",
 *          in="query",
 *          description="ID of intern",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Parameter(
 *          name="log_id",
 *          in="query",
 *          description="ID of log",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Parameter(
 *          name="internship_id",
 *          in="query",
 *          description="ID of internship",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Parameter(
 *          name="status",
 *          in="query",
 *          description="The status of the log",
 *          @OA\Schema(
 *              type="string",
 *              default="string"
 *          )
 *      ),
 *       security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /logs', function () {
    $params = Flight::request()->query->getData();
    $dao = new LogDao();
    Flight::output($dao->get_all($params['intern_id'], $params['log_id'], $params['internship_id'], $params['status']));
});

/**
 * @OA\Get(
 *      path="/logs/datatables",
 *      tags={"log"},
 *      summary="API for log datatable from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived logs."
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /logs/datatables', function () {
    $dao = new LogDao();
    $params = Flight::request()->query->getData();
    $res = $dao->get_count($params);
    $total_num = $res[0]['count'];
    $data = $dao->get_all($params);
    Flight::output(['data' => $data, 'recordsTotal' => $total_num, 'recordsFiltered' => $total_num, 'draw' => $params['draw']]);
});

/**
 * @OA\Get(
 *      path="/logs/{id}",
 *      tags={"log"},
 *      summary="API for getting log by id from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived log."
 *      ),
 *       @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of log",
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

Flight::route('GET /logs/@id', function ($id) {
    $dao = new LogDao();
    Flight::output($dao->get_by_id($id));
});

/**
 * @OA\Get(
 *      path="/logs/intern/{id}",
 *      tags={"log"},
 *      summary="API for getting logs from intern from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived logs."
 *      ),
 *       @OA\Parameter(
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
 *      
 * )
 */

Flight::route('GET /logs/intern/@id', function ($id) {
    $dao = new LogDao();
    Flight::output($dao->get_all($id));
});


/**
 * @OA\Post(
 *      path="/logs",
 *      tags={"log"},
 *      summary="API for inserting log to database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/LogInsert")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="Log inserted successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The log couldn't be inserted.",
 *      ),
 * 
 *      security={
 *          {"api_key": {}}
 *      }

 * )
 */

Flight::route('POST /logs', function () {

    $dao = new LogDao();
    $request = Flight::request();
    $log = $request->data->getData();
    $log['status'] = "PENDING";
    if (Flight::get('user')['id']) {
        $log['intern_id'] = Flight::get('user')['id'];
    }
    Flight::validate(LogInsert::class, $log);
    $returned_log = $dao->insert('logs', ['work_done' => $log['work_done'], 'date' => $log['date'], 'time' => $log['time']]);
    $dao->insert('log_internship', ['log_id' => $returned_log['id'], 'internship_id' => $log['internship_id'], 'intern_id' => $log['intern_id'], 'status' => $log['status']]);
    Flight::output($log);
});

/**
 * @OA\Put(
 *      path="/logs/{id}",
 *      tags={"log"},
 *      summary="API for updating log in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/LogInsert")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of log",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="Log updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The log couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /logs/@id', function ($id) {
    $dao = new LogDao();
    $request = Flight::request();
    $log = $request->data->getData();
    // if(Flight::get('user')['id']){
    //     $log['intern_id'] = Flight::get('user')['id'];
    // }
    //Flight::validate(LogInsert::class, $log);
    $dao->update_log_status($id, "PENDING");
    $log['id'] = $id;
    $dao->update($log);
    Flight::output($log);
});

/**
 * @OA\Put(
 *      path="/logs/{id}/status",
 *      tags={"log"},
 *      summary="API for updating log status in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/LogInsert")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of log",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="Log updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The log couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /logs/@id/status', function ($id) {
    $dao = new LogDao();
    $request = Flight::request();
    $log = $request->data->getData();
    $log['id'] = $id;
    $dao->update_log_status($id, $log['status']);
    Flight::output($log);
});

/**
 * @OA\Delete(
 *      path="/logs/{id}",
 *      tags={"log"},
 *      summary="API for deleteing a log in database.",
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of log",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="Log deleted successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The log couldn't be deleted.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('DELETE /logs/@id', function ($id) {
    $dao = new LogDao();
    $dao->delete($id);
});
