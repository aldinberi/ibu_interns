<?php

use \Firebase\JWT\JWT;


/**
 * Flight endpoints for department CRUD operations.
 * 
 */

  /**
 * @OA\Get(
 *      path="/departments",
 *      tags={"department"},
 *      summary="API for getting all departments from database.",
 *      @OA\Response(
 *          response=200,
 *          description="Succesfully retrived departments."
 *      ),
 *      security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('GET /departments', function(){
    $dao = new DepartmentDao();
    Flight::output($dao->get_all());
});

?>