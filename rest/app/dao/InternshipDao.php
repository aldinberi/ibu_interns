<?php

require_once 'app/dao/BaseDao.php';

class InternshipDao extends BaseDao {

    public function get_all($p = null){
        $params = array();
        $query = "SELECT DISTINCT i.id as id, i.company_id, i.intern_id, i.title, i.start_date, i.end_date, i.department_id,
                         i.status, i.grade, c.name AS company_name, d.name AS department_name, 
                         ii.status AS intern_status
                  FROM internships i 
                  LEFT OUTER JOIN companies c ON c.id = i.company_id
                  LEFT OUTER JOIN departments d ON d.id = i.department_id 
                  LEFT OUTER JOIN internship_interns as ii ON i.id = ii.internship_id  WHERE 1=1 ";
        

        if ($p['department_id'] != null){
            $bind = parent::bind( $params, $p['department_id'], PDO::PARAM_INT );
            $query .= " AND i.department_id =".$bind;
        }
        if ($p['status'] != null){
            // if($p['status'] == "ACTIVE" || $p['status'] == "COMPLETED"){
            //     $bind = parent::bind( $params, "APPROVED", PDO::PARAM_STR );
            //     $query .= " AND ii.status =".$bind;
            // }
            $bind = parent::bind( $params, $p['status'], PDO::PARAM_STR );
            $query .= " AND i.status =".$bind;

        }
        if ($p['company_id'] != null){
            $bind = parent::bind( $params, $p['company_id'], PDO::PARAM_INT );
            $query .= " AND i.company_id =".$bind;
        }

        if ($p['intern_id'] != null){
            $bind = parent::bind( $params, $p['intern_id'], PDO::PARAM_INT );
            $query .= " AND ii.intern_id =".$bind;
        }

        if ($p['intern_status'] != null){
            $bind = parent::bind( $params, $p['intern_status'], PDO::PARAM_STR );
            $query .= " AND ii.status =".$bind;
        }

        if($p['search']['value'] != null){
            $columns = ['i.id', 'c.name', 'd.name', 'i.company_id', 'i.intern_id', 'i.title', 'i.start_date', 'i.end_date', 'i.status',
                        'i.id', 'i.id'];
            for ( $i=0, $ien=count($p['columns']) ; $i<$ien ; $i++ ) 
                $p['columns'][$i]['data'] = $columns[$i];
            
            $query .= parent::search($p, $params);   
        }

        if($p != null){
            $query .= parent::order($p);   
        }

        if ($p['start'] != null && $p['length'] != null){
            $bind_start = parent::bind( $params, $p['start'], PDO::PARAM_INT );
            $bind_length = parent::bind( $params,$p['length'], PDO::PARAM_INT );
            $query .= " LIMIT ".$bind_start.", ".$bind_length;
        }
        
        return parent::query($query, $params);
    }

    public function get_count($p = null){
        $params = array();
        $query = "SELECT count(*) as count
                  FROM internships i 
                  LEFT OUTER JOIN companies c ON c.id = i.company_id
                  LEFT OUTER JOIN departments d ON d.id = i.department_id 
                  LEFT OUTER JOIN internship_interns as ii ON i.id = ii.internship_id  WHERE 1=1 ";

        if ($p['department_id'] != null){
            $bind = parent::bind( $params, $p['department_id'], PDO::PARAM_INT );
            $query .= " AND i.department_id =".$bind;
        }
        if ($p['status'] != null){
            $bind = parent::bind( $params, $p['status'], PDO::PARAM_STR );
            $query .= " AND i.status =".$bind;
        }
        if ($p['company_id'] != null){
            $bind = parent::bind( $params, $p['company_id'], PDO::PARAM_INT );
            $query .= " AND i.company_id =".$bind;
        }

        if ($p['intern_id'] != null){
            $bind = parent::bind( $params, $p['intern_id'], PDO::PARAM_INT );
            $query .= " AND ii.intern_id =".$bind;
        }

        if ($p['intern_status'] != null){
            $bind = parent::bind( $params, $p['intern_status'], PDO::PARAM_STR );
            $query .= " AND ii.status =".$bind;
        }

        if($p['search']['value'] != null){
            $columns = ['i.id', 'c.name', 'd.name', 'i.company_id', 'i.intern_id', 'i.title', 'i.start_date', 'i.end_date', 'i.status',
                        'i.id', 'i.id'];

            for ( $i=0, $ien=count($p['columns']) ; $i<$ien ; $i++ ) 
                $p['columns'][$i]['data'] = $columns[$i];

            $query .= parent::search($p, $params);   
        }

        return parent::query($query, $params);
    }



    public function get_by_id($id){
        $query = "SELECT i.*, d.name as department_name FROM internships as i INNER JOIN departments as d ON i.department_id = d.id WHERE i.id = :id";
        return parent::get_by_id($query, $id);
    }

    public function get_by_status($status){
        $query = "SELECT * FROM internships WHERE status = :status";
        return parent::query($query, ['status' => $status]);
    }


    public function insert($internship){
        return parent::insert('internships', $internship);
    }

    public function delete($id){
        return parent::delete('internships', $id);
    }

    public function apply($internship){
        return parent::insert('internship_interns', $internship);
    }

    public function update_internship_status($internship_id, $intern_id, $status){
        $query = "UPDATE internship_interns SET status = :status WHERE intern_id = :intern_id AND internship_id = :internship_id";
        if(strcmp($status, "APPROVED")==0){
            parent::update('internships', ['id' => $internship_id, 'intern_id' => $intern_id, 'status' => "ACTIVE"]);
        }
        return parent::query($query, ['intern_id' => $intern_id, 'internship_id' => $internship_id, 'status' => $status]);
    }

    public function update($internship){
        return parent::update('internships', $internship);
    }    
}
