<?php

use \Firebase\JWT\JWT;

require_once 'app/dao/BaseDao.php';

class UserDao extends BaseDao {

    public function get_interns($p = null){
        $params = array();
        $query = "SELECT u.id, u.year, u.name, ii.internship_id, i.company_id, ii.documents_id, ii.status, d.name AS department
                  FROM users u 
                  LEFT OUTER JOIN departments d ON d.id = u.department_id
                  LEFT OUTER JOIN internship_interns ii ON ii.intern_id = u.id
                  LEFT OUTER JOIN internships i ON i.id = ii.internship_id
                  WHERE u.type = 'INTERN'";
                  
        if ($p['status'] != null){
            $bind = parent::bind( $params, $p['status'], PDO::PARAM_STR );
            $query .= " AND ii.status =".$bind;
        }

        if ($p['internship_id'] != null){
            $bind = parent::bind( $params, $p['internship_id'], PDO::PARAM_INT );
            $query .= " AND ii.internship_id =".$bind;
        }

        if ($p['intern_id'] != null){
            $bind = parent::bind( $params, $p['intern_id'], PDO::PARAM_INT );
            $query .= " AND ii.intern_id =".$bind;
        }

        if($p['search']['value'] != null){
            $columns = ['u.id', 'u.name', 'u.id'];

            for ( $i=0, $ien=count($p['columns']) ; $i<$ien ; $i++ ) 
            $p['columns'][$i]['data'] = $columns[$i];

            $query .= parent::search($p, $params);   
        }

        if($p != null){
            $query .= parent::order($p);   
        } else {
            $query = "SELECT u.id, u.year, u.name
            FROM users u 
            WHERE u.type = 'INTERN'";
            return parent::query($query, null);
        }

        if ($p['start'] != null && $p['length'] != null){
            $bind_start = parent::bind( $params, $p['start'], PDO::PARAM_INT );
            $bind_length = parent::bind( $params,$p['length'], PDO::PARAM_INT );
            $query .= " LIMIT ".$bind_start.", ".$bind_length."";
        }

        return parent::query($query, $params);
    }

    public function get_count($p = null){
        $params = array();
        $query = "SELECT count(*) as count
                  FROM users u 
                  LEFT OUTER JOIN departments d ON d.id = u.department_id
                  LEFT OUTER JOIN internship_interns ii ON ii.intern_id = u.id
                  LEFT OUTER JOIN internships i ON i.id = ii.internship_id
                  WHERE u.type = 'INTERN'";
                  
        if ($p['status'] != null){
            $bind = parent::bind( $params, $p['status'], PDO::PARAM_STR );
            $query .= " AND ii.status =".$bind;
        }
        
        if ($p['internship_id'] != null){
            $bind = parent::bind( $params, $p['internship_id'], PDO::PARAM_INT );
            $query .= " AND ii.internship_id =".$bind;
        }

        if ($p['intern_id'] != null){
            $bind = parent::bind( $params, $p['intern_id'], PDO::PARAM_INT );
            $query .= " AND ii.intern_id =".$bind;
        }

        if($p['search']['value'] != null){
            $columns = ['u.id', 'u.name', 'u.id'];

            for ( $i=0, $ien=count($p['columns']) ; $i<$ien ; $i++ ) 
                $p['columns'][$i]['data'] = $columns[$i];

            $query .= parent::search($p, $params);   
        }

        return parent::query($query, $params);
    }

    public function get_interns_internships($id, $status1 = null, $status2 = null){
        $query = "SELECT i.* FROM internship_interns ii 
        INNER JOIN internships i ON ii.internship_id = i.id 
        WHERE ii.status IN (:status1, :status2) AND ii.intern_id = :id";

        return parent::query($query, ['id' => $id, 'status1'=> $status1, 'status2' => $status2]);
    }


    public function get_all($internship_id=null, $department_id=null, $company_id=null){
        $query = "SELECT u.* FROM users u 
        LEFT OUTER JOIN
        companies c ON c.id = u.company_id 
        LEFT OUTER JOIN 
        internship_interns i ON i.intern_id = u.id
        WHERE 1=1 ";
        $params = [];

        if ($department_id){
            $query .= " AND u.department_id = :department_id";
            $params['department_id'] = $department_id;
        }
        
        if ($company_id){
            $query .= " AND u.company_id = :company_id";
            $params['company_id'] = $company_id;
        }

        if ($internship_id){
            $query .= " AND i.internship_id = :internship_id";
            $params['internship_id'] = $internship_id;
        }

        return parent::query($query, $params);

    }

    public function get_company_user($id){
        $query = "SELECT * FROM companies WHERE company_id = :id";
        return parent::get_by_id($query, $id);
    }

    public function get_by_id($id){
        $query = "SELECT id, password, name, email, company_id, student_id, department_id, type 
                  FROM users 
                  WHERE id = :id";
        return parent::get_by_id($query, $id);
        
    }

    public function check_email($email){
        $query = "SELECT id, name, email, company_id, student_id, department_id, type, login_attempt 
                  FROM users
                  WHERE email = :email";
        return parent::query($query, ['email' => $email]);
    }

    public function get_login_user($login_data){
        $query = "SELECT u.id, u.password, u.name, u.email, u.company_id, c.status as company_status, u.student_id, u.department_id, u.type, u.login_attempt 
                  FROM users as u
                  LEFT JOIN companies as c ON c.id = u.company_id
                  WHERE u.email = :email ";
        return parent::query($query, $login_data);
    }

    public function check_captcha($captcha_response){
        $data = array(
            'secret' => CHAPCHA_API_SECRET,
            'response' => $captcha_response
        );
        $verify = curl_init();
        
        curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($verify, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($verify);
        
        $response = json_decode($response, true);
        print_r($data);
        return $response['success'];

    }

    public function check_forgoten_email($email){
        $query = "SELECT id, email, type
                  FROM users
                  WHERE email = :email";
        return parent::query($query, ['email' => $email]);
    }

    public function send_recovery_mail($email){

        if (gettype($email) == "string") {
            $email = parent::test_input($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
              }
        } else {
            throw new Exception('Email is required');
        }

        $user = $this->check_forgoten_email($email)[0];

        if($user){
            if(strpos($user['type'], 'COMPANY_USER') === false){
                throw new Exception('Only company users can request new password');
            }
            date_default_timezone_set("Europe/Helsinki");
            $jwt = JWT::encode(['exp' => time() + 3600, 'email' => $user['email'], 'type'=> $user['type'], 'id' => $user['id']], JWT_SECRET);

            $email_message = "If you have requested a password recovery click on this link\n";
            $email_message .= "http://interns.ibu.edu.ba/passwordRecovery.html?jwt=".$jwt;

            $transport = (new Swift_SmtpTransport('smtp.sendgrid.net', 587))
            ->setUsername(SENDGRID_USERNAME)
            ->setPassword(SENDGRID_SECRET);

            $mailer = new Swift_Mailer($transport);

            $message = (new Swift_Message('IBU interns password recovey'))
            ->setFrom(['aldin.berisa@stu.ibu.edu.ba' => 'Aldin B'])
            ->setTo([$email])
            ->setBody($email_message);

            $result = $mailer->send($message);
            print_r($result);
            die;
        }
    }

    public function callAPI($method, $url, $header, $data = null){
        return parent::callAPI($method, $url, $header, $data);
    }

    public function insert($user){
        return parent::insert('users', $user);
    }

    public function update($user){
        return parent::update('users', $user);
    }    

}
?>