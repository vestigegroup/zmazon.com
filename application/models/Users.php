<?php

class Application_Model_Users extends Zend_Db_Table_Abstract
{
    protected $_name = "users";
    
    public function retrieveAllUsers() {
        return $this->fetchAll();
    }
    
    public function editRecord($id, $data) {
        $this->update($data, "id = ".$id);
    }
    
    public function remove($id) {
        $this->delete("id = ".$id);
    }

 	function Register($formData)
	{

	$row=$this->createRow();
	$row->userName=$formData['userName'];
	$row->email=$formData['email'];
	$row->password=$formData['password'];
	$row->save();

	}




}

