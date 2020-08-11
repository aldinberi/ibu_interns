<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__."/../../app/utils/ModelValidator.php";


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Sample test class which showcases some basic aspects of PHPUnit.
 * This test file can be removed, or edited to suit your project needs.
 */
class IntegrationTest extends TestCase {
    private $client;

    public function setUp() {
        $this->client = new GuzzleHttp\Client(['base_uri' => "http://interns.ibu.edu.ba/"]);

    }


    public function testCompanyUserRegisterWithWeakPassword() {
        try{
            $this->client->request('POST', '/rest/register', [
                "json" => [
                    "email"=> "campo@gmail.com",
                    "name"=> "Campo Berisa",
                    "password"=> "1234",
                    "type" => "COMPANY_USER"
                ]
            ]);

            //print_r($response->getStatusCode());

        }catch(Exception $e){
            if(strpos($e->getMessage(), 'You can not use a weak password, try a new one') !== false){
                $this->assertTrue(true);
            }else{
                $this->fail();
            }
             
         }

    }

    public function testCompanyUserRegisterWithTakenEmail() {
        try{
            $response = $this->client->request('POST', '/rest/register', [
                "json" => [
                    "email"=> "campo@gmail.com",
                    "name"=> "Campo Berisa",
                    "password"=> "SarajevoSchool1",
                    "type" => "COMPANY_USER"
                ]
            ]);

        }catch(Exception $e){
            if(strpos($e->getMessage(), 'Email already taken') !== false){
                $this->assertTrue(true);
            }else{
                $this->fail();
            }
                
        }

    }


    public function testCompanyRegister() {
        $example_company = [
            'name'=>"Stark Inc.", 
            'address'=>"Los Angeles", 
            'email'=>"campo@stark.com", 
            'phone'=>'033645765', 
            'website' => "www.stark.com"
        ];
        try{
            $response = $this->client->request('POST', 'rest/companies', [
                "json" => $example_company,
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6bnVsbCwiY29tcGFueV9zdGF0dXMiOm51bGwsInN0dWRlbnRfaWQiOm51bGwsImRlcGFydG1lbnRfaWQiOm51bGwsInR5cGUiOiJDT01QQU5ZX1VTRVIifQ.OUnB00zl37jFbkA2LqeY9p-4vInmY8PZBU2h5a9yPf0"
                ],
            ]);
            $stream = $response->getBody();
            $json = json_decode($stream->getContents(), true);
            $this->assertEquals($example_company['name'], $json['name']);
            $this->assertEquals($example_company['address'], $json['address']);
            $this->assertEquals($example_company['email'], $json['email']);
            $this->assertEquals($example_company['phone'], $json['phone']);
            $this->assertEquals($example_company['website'], $json['website']);

        }catch (RequestException $e) {
            $this->fail();
        }


    }

        public function testCompanyDenied() {
        try{
            $this->client->request('PUT', 'rest/companies/50', [
                "json" =>["status" => "DENIED"],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTUsIm5hbWUiOiJEaW5vIEsiLCJlbWFpbCI6ImRpbm8ua2Vjb0BpYnUuZWR1LmJhIiwiY29tcGFueV9pZCI6bnVsbCwiY29tcGFueV9zdGF0dXMiOm51bGwsInN0dWRlbnRfaWQiOm51bGwsImRlcGFydG1lbnRfaWQiOjEsInR5cGUiOiJQUk9GRVNTT1IifQ.PBQ2MG3FiuRflw-H2ZkQeons3uuMLY6ukmUxN92tDpo"
                ]
            ]);

            
            $this->assertTrue(true);

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }

    }

    public function testCompanyApprove() {
        try{
            $this->client->request('PUT', 'rest/companies/50', [
                "json" =>["status" => "APPROVED"],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTUsIm5hbWUiOiJEaW5vIEsiLCJlbWFpbCI6ImRpbm8ua2Vjb0BpYnUuZWR1LmJhIiwiY29tcGFueV9pZCI6bnVsbCwiY29tcGFueV9zdGF0dXMiOm51bGwsInN0dWRlbnRfaWQiOm51bGwsImRlcGFydG1lbnRfaWQiOjEsInR5cGUiOiJQUk9GRVNTT1IifQ.PBQ2MG3FiuRflw-H2ZkQeons3uuMLY6ukmUxN92tDpo"
                ]
            ]);

            
            $this->assertTrue(true);

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }

    }

    public function testPostInternship() {
        try{
            $this->client->request('POST', 'rest/internships', [
                "json" =>['title'=>"IOS developer", 'job_description' =>"Develop apps in ISO", 'start_date' => "2020-04-27", 'end_date' =>"2020-05-27", 'department_id'=>1],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTAsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.g6CuTDf4HVZ_fBLUQykKFmIqqjcSu_hVI6OIQRGio3I"
                ]
            ]);

            
            $this->assertTrue(true);

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }

    }
   
    public function testUpdateInternship() {
        try{
            $this->client->request('PUT', 'rest/internships/51', [
                "json" =>['title'=>"IOS developer", 'job_description' =>"Develop apps in ISO devices", 'start_date' => "2020-04-27", 'end_date' =>"2020-05-27", 'department_id'=>1],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTAsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.g6CuTDf4HVZ_fBLUQykKFmIqqjcSu_hVI6OIQRGio3I"
                ]
            ]);

            
            $this->assertTrue(true);

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }

    }

    public function testProfesorDenieInternship() {
        try{
            $this->client->request('PUT', 'rest/internships/51', [
                "json" =>["status" => "DENIED"],
                "headers" => [
                     "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTUsIm5hbWUiOiJEaW5vIEsiLCJlbWFpbCI6ImRpbm8ua2Vjb0BpYnUuZWR1LmJhIiwiY29tcGFueV9pZCI6bnVsbCwiY29tcGFueV9zdGF0dXMiOm51bGwsInN0dWRlbnRfaWQiOm51bGwsImRlcGFydG1lbnRfaWQiOjEsInR5cGUiOiJQUk9GRVNTT1IifQ.PBQ2MG3FiuRflw-H2ZkQeons3uuMLY6ukmUxN92tDpo"
                ]
            ]);  
            $this->assertTrue(true);

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }

    public function testProfesorApproveInternship() {
        try{
            $this->client->request('PUT', 'rest/internships/51', [
                "json" =>["status" => "APPROVED"],
                "headers" => [
                     "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTUsIm5hbWUiOiJEaW5vIEsiLCJlbWFpbCI6ImRpbm8ua2Vjb0BpYnUuZWR1LmJhIiwiY29tcGFueV9pZCI6bnVsbCwiY29tcGFueV9zdGF0dXMiOm51bGwsInN0dWRlbnRfaWQiOm51bGwsImRlcGFydG1lbnRfaWQiOjEsInR5cGUiOiJQUk9GRVNTT1IifQ.PBQ2MG3FiuRflw-H2ZkQeons3uuMLY6ukmUxN92tDpo"
                ]
            ]);  
            $this->assertTrue(true);

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }

    public function testInternAppyToInternship() {
        try{
            $this->client->request('POST', 'rest/intern/apply', [
                "json" =>[ 
                    "internship_id" => 48,
                    "intern_id" => 20,
                    "status" => "PENDING",
                    "documents_id" => ["id_array"=>["6","7"]]
                ],
                "headers" => [
                     "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MjAsIm5hbWUiOiJTZW5hZCBCZXJpc2EiLCJlbWFpbCI6ImJlcmlzYUBnbWFpbC5jb20iLCJjb21wYW55X2lkIjpudWxsLCJjb21wYW55X3N0YXR1cyI6bnVsbCwic3R1ZGVudF9pZCI6bnVsbCwiZGVwYXJ0bWVudF9pZCI6MSwidHlwZSI6IklOVEVSTiJ9.GDbfyZ0yjzgNeg08MaT-4xOEFLM_uAnldXMuOc7h99g"
                ]
            ]);  
            $this->assertTrue(true);

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }
    
    public function testInternCanNotAppyTwiceToInternship() {
        try{
            $this->client->request('POST', 'rest/intern/apply', [
                "json" =>[ 
                    "internship_id" => 48,
                    "intern_id" => 20,
                    "status" => "PENDING",
                    "documents_id" => ["id_array"=>["6","7"]]
                ],
                "headers" => [
                     "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MjAsIm5hbWUiOiJTZW5hZCBCZXJpc2EiLCJlbWFpbCI6ImJlcmlzYUBnbWFpbC5jb20iLCJjb21wYW55X2lkIjpudWxsLCJjb21wYW55X3N0YXR1cyI6bnVsbCwic3R1ZGVudF9pZCI6bnVsbCwiZGVwYXJ0bWVudF9pZCI6MSwidHlwZSI6IklOVEVSTiJ9.GDbfyZ0yjzgNeg08MaT-4xOEFLM_uAnldXMuOc7h99g"
                ]
            ]);  
            $this->fail();

        }catch (GuzzleHttp\Exception\ClientException $e) {
            if(strpos($e->getMessage(), 'You already applied to this internship') !== false){
                $this->assertTrue(true);
            }else{
                $this->fail();
            }
        }
    }

    public function testDownloadAttachedFiles() {
        try{
            $response = $this->client->request('GET', 'rest/documents/uploads/6', [
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTAsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.g6CuTDf4HVZ_fBLUQykKFmIqqjcSu_hVI6OIQRGio3I"
                ]
            ]);  
            $stream = $response->getBody();
            $json = json_decode($stream->getContents(), true);
            $this->assertEquals($json['document_name'], "_Aneks-Ugovora-br-3011_14_ITA_16CP-Alem-Beriša-Ponovni-upis3505.pdf");
            $this->assertEquals($json['type'], "CV");

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }
    
    public function testDenieIntern() {
        try{
            $this->client->request('PUT', 'rest/internship/interns', [
                "json" => [
                    "internship_id" => 51,
                    "intern_id" => 20,
                    "status" => "APPROVED"
                ],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTAsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.g6CuTDf4HVZ_fBLUQykKFmIqqjcSu_hVI6OIQRGio3I"
                ]
            ]);  
            $this->assertTrue(true);
            

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }

    public function testApproveIntern() {
        try{
            $this->client->request('PUT', 'rest/internship/interns', [
                "json" => [
                    "internship_id" => 51,
                    "intern_id" => 20,
                    "status" => "APPROVED"
                ],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTAsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.g6CuTDf4HVZ_fBLUQykKFmIqqjcSu_hVI6OIQRGio3I"
                ]
            ]);  
            $this->assertTrue(true);
            

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }
    
    public function testInternPostLog() {
        try{
            $response = $this->client->request('POST', 'rest/logs', [
                "json" => [
                    "internship_id" => 4,
                    "work_done" => "Make API for login",
                    "date" => "2020-05-26"
                ],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MjAsIm5hbWUiOiJTZW5hZCBCZXJpc2EiLCJlbWFpbCI6ImJlcmlzYUBnbWFpbC5jb20iLCJjb21wYW55X2lkIjpudWxsLCJjb21wYW55X3N0YXR1cyI6bnVsbCwic3R1ZGVudF9pZCI6bnVsbCwiZGVwYXJ0bWVudF9pZCI6MSwidHlwZSI6IklOVEVSTiJ9.GDbfyZ0yjzgNeg08MaT-4xOEFLM_uAnldXMuOc7h99g"
                ]
            ]);  
             $stream = $response->getBody();
             $json = json_decode($stream->getContents(), true);
             $this->assertEquals($json['internship_id'], 4);
             $this->assertEquals($json['work_done'], "Make API for login");
             $this->assertEquals($json['date'], "2020-05-26");
            

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }


    public function tearDown() {
        $this->client = NULL;
    }
}