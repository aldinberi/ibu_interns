<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__."/../../app/utils/ModelValidator.php";


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Sample test class which showcases some basic aspects of PHPUnit.
 * This test file can be removed, or edited to suit your project needs.
 */
class SystemTest extends TestCase {
    private $client;

    public function setUp() {
        $this->client = new GuzzleHttp\Client(['base_uri' => "http://interns.ibu.edu.ba/"]);

    }


    public function testCompanyPostsInternship() {
        try{
            $this->client->request('POST', 'rest/internships', [
                "json" =>['title'=>"IOS developer", 'job_description' =>"Develop apps in ISO", 'start_date' => "2020-04-27", 'end_date' =>"2020-05-27", 'department_id'=>5],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTIsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.bPjXY13mbRcTWjURVODMLIKSUGUVqqY90tOk-nqMo-U"
                ]
            ]);

            
            $this->assertTrue(true);

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }

    }
   

    public function testProfessorApprovesInternship() {
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

    public function testInternAppliesToInternship() {
        try{
            $this->client->request('POST', 'rest/intern/apply', [
                "json" =>[ 
                    "internship_id" => 48,
                    "intern_id" => 20,
                    "status" => "PENDING",
                    "documents_id" => json_encode(["id_array"=>["6","7"]])
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
    

    public function testCompanyDownloadsAttachedFiles() {
        try{
            $response = $this->client->request('GET', 'rest/documents/uploads/6', [
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTIsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.bPjXY13mbRcTWjURVODMLIKSUGUVqqY90tOk-nqMo-U"
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
    

    public function testCompanyApprovesIntern() {
        try{
            $this->client->request('PUT', 'rest/internship/interns', [
                "json" => [
                    "internship_id" => 54,
                    "intern_id" => 20,
                    "status" => "APPROVED"
                ],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTIsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.bPjXY13mbRcTWjURVODMLIKSUGUVqqY90tOk-nqMo-U"
                ]
            ]);  
            $this->assertTrue(true);
            

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }
    
    public function testInternPostsLog() {
        try{
            $response = $this->client->request('POST', 'rest/logs', [
                "json" => [
                    "internship_id" => 54,
                    "work_done" => "Make API for login",
                    "date" => "2020-05-26"
                ],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MjAsIm5hbWUiOiJTZW5hZCBCZXJpc2EiLCJlbWFpbCI6ImJlcmlzYUBnbWFpbC5jb20iLCJjb21wYW55X2lkIjpudWxsLCJjb21wYW55X3N0YXR1cyI6bnVsbCwic3R1ZGVudF9pZCI6bnVsbCwiZGVwYXJ0bWVudF9pZCI6MSwidHlwZSI6IklOVEVSTiJ9.GDbfyZ0yjzgNeg08MaT-4xOEFLM_uAnldXMuOc7h99g"
                ]
            ]);  
             $stream = $response->getBody();
             $json = json_decode($stream->getContents(), true);
             $this->assertEquals($json['internship_id'], 54);
             $this->assertEquals($json['work_done'], "Make API for login");
             $this->assertEquals($json['date'], "2020-05-26");
            

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }

    public function testCompanyApprovesLog() {
        try{
            $response = $this->client->request('PUT', 'rest/logs/35/status', [
                "json" => [
                    "status" => "APPROVED"
                ],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MjAsIm5hbWUiOiJTZW5hZCBCZXJpc2EiLCJlbWFpbCI6ImJlcmlzYUBnbWFpbC5jb20iLCJjb21wYW55X2lkIjpudWxsLCJjb21wYW55X3N0YXR1cyI6bnVsbCwic3R1ZGVudF9pZCI6bnVsbCwiZGVwYXJ0bWVudF9pZCI6MSwidHlwZSI6IklOVEVSTiJ9.GDbfyZ0yjzgNeg08MaT-4xOEFLM_uAnldXMuOc7h99g"
                ]
            ]);  
             $stream = $response->getBody();
             $json = json_decode($stream->getContents(), true);
             $this->assertEquals($json['status'], "APPROVED");
            

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }

    public function testCompanyGradesInternship() {
        try{
            $response = $this->client->request('PUT', 'rest/internships/54', [
                "json" => [
                    "grade" => json_encode(["student_name"=>"Aldin Beriša", "student_department"=>"IT", 
                                "student_id"=>"301017009","company_name"=>"Stark Inc.","branch_name"=>"Develpoment",
                                "company_department"=>"Mobile devision","start_date"=>"2020-06-30","end_date"=>"2020-07-11",
                                "attendence[]"=>"4","obedience"=>"4","work_knowledge"=>"4","willingness_to_learn"=>"4",
                                "new_concepts_into_practice"=>"4","responsibility"=>"4","own_initiative"=>"4","orderliness"=>"4",
                                "outfit"=>"4","communication_customers"=>"4","communication_colleagues"=>"4","competence"=>"4",
                                "overall"=>"4","opinion"=>"He will be quite successful","accept_again"=>"Quite possible"]),
                    "status" => "COMPLETED",
                    "intern_id" => 20
                ],
                "headers" => [
                    "Bearer" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6NTYsIm5hbWUiOiJDYW1wbyBCZXJpc2EiLCJlbWFpbCI6ImNhbXBvQG91dGxvb2suY29tIiwiY29tcGFueV9pZCI6NTIsImNvbXBhbnlfc3RhdHVzIjoiQVBQUk9WRUQiLCJzdHVkZW50X2lkIjpudWxsLCJkZXBhcnRtZW50X2lkIjpudWxsLCJ0eXBlIjoiQ09NUEFOWV9VU0VSIn0.bPjXY13mbRcTWjURVODMLIKSUGUVqqY90tOk-nqMo-U"
                ]
            ]);  
             $stream = $response->getBody();
             $json = json_decode($stream->getContents(), true);
             $this->assertEquals($json['status'], "COMPLETED");
             $this->assertEquals($json['intern_id'], 20);
             $this->assertEquals($json['id'],  54);

            

        }catch (GuzzleHttp\Exception\ClientException $e) {
                $this->fail();
        }
    }


    public function tearDown() {
        $this->client = NULL;
    }
}