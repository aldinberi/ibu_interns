<?php

require_once 'app/dao/BaseDao.php';

class CompanyDao extends BaseDao {

    public function get_all($p = null){
        $params = array();
        $query = "SELECT id, name, status, id
                  FROM companies 
                  WHERE 1 = 1";
        
        if ($p['status'] != null){
            $bind = parent::bind( $params, $p['status'], PDO::PARAM_STR );
            $query .= " AND status =".$bind;
        }

        if($p['search']['value'] != null){
            $query .= parent::search($p, $params);   
        }

        if($p != null){
            $query .= parent::order($p);   
        }

        if ($p['start'] != null && $p['length'] != null){
            $bind_start = parent::bind( $params, $p['start'], PDO::PARAM_INT );
            $bind_length = parent::bind( $params,$p['length'], PDO::PARAM_INT );
            $query .= " LIMIT ".$bind_start.", ".$bind_length."";
        }

       return parent::query($query, $params);
    }


    public function get_count($p = null){
        $query = "SELECT count(*) as count FROM companies WHERE 1=1"; 
        $params = array();
        if ($p['status'] != null){
            $bind = parent::bind( $params, $p['status'], PDO::PARAM_STR );
            $query .= " AND status =".$bind;
        }

        if($p['search']['value'] != null){  
            $query .= parent::search($p, $params);      
        }

        return parent::query($query, $params);
    }

    public function get_by_id($id){
        $query = "SELECT c.*, u.name AS contact, u.email AS contact_email
        FROM companies AS c
        INNER JOIN users AS u ON u.company_id = c.id 
        WHERE c.id = :id";
        return parent::get_by_id($query, $id);
    }

      public function insert($company){
        return parent::insert('companies', $company);
    }

    public function update($company){
        return parent::update('companies', $company);
    } 
}
?>