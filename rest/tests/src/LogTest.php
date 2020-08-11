<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__."/../../app/utils/ModelValidator.php";

require_once 'app/dao/LogDao.php';


class LogTest extends TestCase {
    private $dao;

    public function setUp() {
        $this->dao = new LogDao();
    }


    // public function testLogInsert() {
    //     try{
    //         $example_log = [
    //             "work_done" => "Made login page",
    //             "date" => "2020-01-17"
    //         ];
            
    //         $this->dao->insert("logs", $example_log);
    //         $this->assertTrue(true);

    //     }catch(Exception $e){
    //         $this->fail();
    //     }

    // }

    public function testBasicLogInfo(){
        $logs = $this->dao->get_all();
    
        $this->assertNotEmpty($logs);
    
        foreach ($logs as $log){
            $this->assertArrayHasKey("id", $log);
            $this->assertNotEmpty($log["id"]);
            $this->assertArrayHasKey("log_id", $log);
            $this->assertNotEmpty($log["log_id"]);
            $this->assertArrayHasKey("status", $log);
            $this->assertNotEmpty($log["status"]);
            $this->assertArrayHasKey("date", $log);
            $this->assertNotEmpty($log["date"]);
        } 
        
    }

    public function testIfReturnsRealLogs(){
        $logs = $this->dao->get_all();
            $example_log = [
                "id" => 10,
                "log_id" => 22,
                "status" => "APPROVED",
                "date" => "2020-01-17"
            ];
            $this->assertEquals($example_log["id"], $logs[0]["id"]);
            $this->assertEquals($example_log["log_id"], $logs[0]["log_id"]);
            $this->assertEquals($example_log["status"], $logs[0]["status"]);
            $this->assertEquals($example_log["date"], $logs[0]["date"]);
        }

    public function testIfReturnsRightLog(){
        $log = $this->dao->get_by_id(10);

        $example_log = [
            "log_id" => 22,
            "internship_id" => 4,
            "intern_id" => 20,
            "status" => "APPROVED",
            "work_done" => "Made login page",
            "date" => "2020-01-17",
            "title" => "Bakend Developer",
            "name" => "Senad Berisa"
        ];
        $this->assertEquals($example_log["log_id"], $log["log_id"]);
        $this->assertEquals($example_log["internship_id"], $log["internship_id"]);
        $this->assertEquals($example_log["intern_id"], $log["intern_id"]);
        $this->assertEquals($example_log["status"], $log["status"]);
        $this->assertEquals($example_log["work_done"], $log["work_done"]);
        $this->assertEquals($example_log["date"], $log["date"]);
        $this->assertEquals($example_log["title"], $log["title"]);
        $this->assertEquals($example_log["name"], $log["name"]);
    }

    public function testUpdateLog(){
        try{
            $example_log = [
                "id" => 25,
                "work_done" => "Made login page",
                "date" => "2020-01-17"
            ];
            
            $this->dao->update($example_log);
            $this->assertTrue(true);

        }catch(Exception $e){
            $this->fail();
        }

    }

    public function tearDown() {
        $this->dao = NULL;
    }
}