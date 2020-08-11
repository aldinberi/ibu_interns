<?php
class DocumentsDao extends BaseDao {

    public function get_all($p = null){
        $params = array();

        $query = "SELECT document_name, type, id
                  FROM intern_documents 
                  WHERE 1 = 1";
        
        if ($p['intern_id'] != null){
            $bind = parent::bind( $params, $p['intern_id'], PDO::PARAM_INT );
            $query .= " AND intern_id =".$bind;
        }

        if ($p['type'] != null){
            $bind = parent::bind( $params, $p['type'], PDO::PARAM_STR );
            $query .= " AND type =".$bind;
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
        $params = array();

        $query = "SELECT count(*) as count
                  FROM intern_documents 
                  WHERE 1 = 1";
        
        if ($p['intern_id'] != null){
            $bind = parent::bind( $params, $p['intern_id'], PDO::PARAM_INT );
            $query .= " AND intern_id =".$bind;
        }

        if ($p['type'] != null){
            $bind = parent::bind( $params, $p['type'], PDO::PARAM_STR );
            $query .= " AND type =".$bind;
        }

        if($p['search']['value'] != null){  
            $query .= parent::search($p, $params);      
        }

        return parent::query($query, $params);
    }

    public function get_by_id($id){
        $query = "SELECT id, document_name, type, document
                  FROM intern_documents 
                  WHERE id = :id";
        return parent::get_by_id($query, $id);
        
    }

    public function insert($documnet){
        return parent::insert('intern_documents', $documnet);
    }  

    public function delete($id){
        return parent::delete('intern_documents', $id);
    }

}
?>