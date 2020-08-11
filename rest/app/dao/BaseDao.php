<?php 

require_once 'config/Config.php';

class BaseDao {
    private $pdo;
    public function __construct()
    {
        
       
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_SCHEME.";charset=utf8";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $opt);
    }

    public function query($query, $params=[]) {
        foreach($params as $key => $value){
            $params[$key] = $this->test_input($value);
        }
        $statment = $this->pdo->prepare($query);
        $statment->execute($params);
        return $statment->fetchAll();
    }

    public function bind( &$a, $val, $type )
	{
		$key = ':binding_'.count( $a );
        $a[$key] = $val;
		return $key;
    }
    
    public function check_password($password){
        $hashed_password = strtoupper(sha1($password));
        $hash_first_5 = substr($hashed_password, 0, 5);
        $control_hash = substr($hashed_password, 5);
        $response = file_get_contents("https://api.pwnedpasswords.com/range/" .$hash_first_5);
        return strpos($response, $control_hash);
    }

    public function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
      }

    public function insert($table, $object){
        $columns = "";
        $params = "";
        foreach($object as $key => $value){
            $object[$key] = $this->test_input($value);
            $columns .= $key.",";
            $params .= ":".$key.",";
        }
        $columns = rtrim($columns, ',');
        $params = rtrim($params, ',');
        $insert = "INSERT INTO ".$table." (".$columns.") VALUES (".$params.")";

        $statment = $this->pdo->prepare($insert);
        $statment->execute($object);
        $object['id'] =  $this->pdo->lastInsertId(); 
        return $object;
    }

    public function order($params = null){
		$order = '';

		if ( isset($params['order']) && count($params['order']) ) {
			$orderBy = array();
			

			for ( $i=0, $ien=count($params['order']) ; $i<$ien ; $i++ ) {
                $columnIdx = intval($params['order'][$i]['column']);
				$requestColumn = $params['columns'][$columnIdx];
				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $params['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = $requestColumn['data'].' '.$dir;
				}
			}

			if ( count( $orderBy ) ) {
				$order = ' ORDER BY '.implode(', ', $orderBy);
			}
        }
        
		return $order;
    }

    public function search($params = null, &$bindings){
        $globalSearch = array();
		$columnSearch = array();

		if ( isset($params['search']) && $params['search']['value'] != '' ) {
			for ( $i=0, $ien=count($params['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $params['columns'][$i];
                $str = $params['search']['value'];
				if ( $requestColumn['searchable'] == 'true' ) {
					$binding = self::bind( $bindings, '%'.strtolower($str).'%', PDO::PARAM_STR );
					$globalSearch[] = "LOWER(".$requestColumn['data'].") LIKE ".$binding;
				}
			}
		}

		// Individual column filtering
		if ( isset( $params['columns'] ) ) {
			for ( $i=0, $ien=count($params['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $params['columns'][$i];
				$str = $requestColumn['search']['value'];
				if ( $requestColumn['searchable'] == 'true' && $str != '' ) {
					$binding = self::bind( $bindings, '%'.strtolower($str).'%', PDO::PARAM_STR );
					$columnSearch[] = "LOWER(".$requestColumn['data'].") LIKE ".$binding;
				}
			}
		}

		// Combine the filters into a single string
		$where = '';

		if ( count( $globalSearch ) ) {
			$where = '('.implode(' OR ', $globalSearch).')';
		}

		if ( count( $columnSearch ) ) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND '. implode(' AND ', $columnSearch);
		}

		if ( $where !== '' ) {
			$where = ' AND '.$where;
		}

        // var_dump($where);
        // die;
		return $where;
    }

    public function update($table, $object) {
        $columns = "";
        foreach($object as $key => $value){
            $object[$key] = $this->test_input($value);
            if($key=="id")
                continue;
            $columns .= $key." =:".$key.", ";
        }
        $columns = rtrim($columns, ', ');
        $update = "UPDATE ".$table." SET ".$columns." WHERE id = :id ";
        $statment = $this->pdo->prepare($update);
        $statment->execute($object); 
        return $object;
        
    }

    public function delete($table, $id) {
        $id = $this->test_input($id);
        $delete = "DELETE FROM ".$table." WHERE id = :id ";   
        $statment = $this->pdo->prepare($delete);
        $statment->execute(['id' => $id]); 
    }

    public function get_by_id($query, $id) {
        $id = $this->test_input($id);
        $statment = $this->pdo->prepare($query);
        $statment->execute(['id' => $id]);
        return $statment->fetch();
        
    }

    public function callAPI($method, $url, $header, $data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        switch ($method){
           case "POST":
              curl_setopt($curl, CURLOPT_POST, 1);
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
              break;
           case "PUT":
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
              break;
           default:
              if ($data)
                 $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
           'intern-user-token:'.$header
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){print_r($result);}
        if (curl_errno($curl)) {
            print_r(curl_error($curl));
            die;
        }
        curl_close($curl);
        return $result;
     }

}
?>