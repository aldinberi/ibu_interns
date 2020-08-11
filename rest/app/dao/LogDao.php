<?php 

    require_once 'app/dao/BaseDao.php';

    class LogDao extends BaseDao {

        public function get_all($p = null){
            $params = array();
            $query = "SELECT li.id, li.log_id, li.status, l.date 
                      FROM log_internship AS li 
                      LEFT OUTER JOIN logs AS l ON  l.id = li.log_id
                      LEFT OUTER JOIN internships AS i ON i.id = li.internship_id 
                      LEFT OUTER JOIN users AS u ON u.id = li.intern_id 
                      WHERE 1=1";
    
            if ($p['intern_id'] != null){
                $bind = parent::bind( $params, $p['intern_id'], PDO::PARAM_INT );
                $query .= " AND li.intern_id =".$bind;
            }

            if ($p['log_id'] != null){
                $bind = parent::bind( $params, $p['log_id'], PDO::PARAM_INT );
                $query .= " AND li.log_id =".$bind;
            }

            if ($p['internship_id'] != null){
                $bind = parent::bind( $params, $p['internship_id'], PDO::PARAM_INT );
                $query .= " AND li.internship_id =".$bind;
            }

            if ($p['status'] != null){
                $bind = parent::bind( $params, $p['status'], PDO::PARAM_STR );
                $query .= " AND li.status =".$bind;
            }

            if($p['search']['value'] != null){

                $columns = ['li.log_id', 'l.date', 'li.status', 'i.company_id', 'li.id'];

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
                $query .= " LIMIT ".$bind_start.", ".$bind_length."";
            }
    
            return parent::query($query, $params);
        }

        public function get_count($p = null){
            $params = array();
            $query = "SELECT count(*) as count
                      FROM log_internship AS li 
                      LEFT OUTER JOIN logs AS l ON  l.id = li.log_id
                      LEFT OUTER JOIN internships AS i ON i.id = li.internship_id 
                      LEFT OUTER JOIN users AS u ON u.id = li.intern_id 
                      WHERE 1=1";
    
            if ($p['intern_id'] != null){
                $bind = parent::bind( $params, $p['intern_id'], PDO::PARAM_INT );
                $query .= " AND li.intern_id =".$bind;
            }

            if ($p['log_id'] != null){
                $bind = parent::bind( $params, $p['log_id'], PDO::PARAM_INT );
                $query .= " AND li.log_id =".$bind;
            }

            if ($p['internship_id'] != null){
                $bind = parent::bind( $params, $p['internship_id'], PDO::PARAM_INT );
                $query .= " AND li.internship_id =".$bind;
            }

            if ($p['status'] != null){
                $bind = parent::bind( $params, $p['status'], PDO::PARAM_STR );
                $query .= " AND li.status =".$bind;
            }

            if($p['search']['value'] != null){
                $columns = ['li.log_id', 'l.date', 'li.status', 'i.company_id', 'li.id'];

                for ( $i=0, $ien=count($p['columns']) ; $i<$ien ; $i++ ) 
                        $p['columns'][$i]['data'] = $columns[$i];
                        
                $query .= parent::search($p, $params);   
            }
    
            return parent::query($query, $params);
        }

        public function get_by_id($id){
            $query = "SELECT li.*, l.work_done, l.time, l.date, i.title, u.name FROM
            log_internship AS li LEFT OUTER JOIN
            logs AS l ON  l.id = li.log_id
            LEFT OUTER JOIN internships AS i ON
            i.id = li.internship_id 
            LEFT OUTER JOIN users AS u ON
            u.id = li.intern_id WHERE li.id = :id";
            return parent::get_by_id($query, $id);
        }
            
        public function insert($table ,$log){
            return parent::insert($table, $log);
        }

        public function update($log){
            return parent::update('logs', $log);
        }

        public function update_log_status($log_id, $status){
            $query = "UPDATE log_internship SET status = :status WHERE log_id = :log_id";
            return parent::query($query, ['log_id' => $log_id, 'status' => $status]);
        }

        public function delete($id){
            return parent::delete('logs', $id);
        }
    }
