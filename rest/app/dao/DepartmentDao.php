<?php
class DepartmentDao extends BaseDao {

    public function get_all(){
        $query = "SELECT * FROM departments";
        return parent::query($query);
    }
}
?>