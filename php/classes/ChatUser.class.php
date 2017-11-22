<?php

class ChatUser extends ChatBase{
	
	protected $name = '', $is_admin = '', $gravatar = '';
	
	public function save(){
		
		DB::query("
			INSERT INTO webchat_users (name, is_admin, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'".DB::esc($this->is_admin)."',
				'".DB::esc($this->gravatar)."'
		)");
		
		return DB::getMySQLiObject();
	}
	
	public function update(){
		DB::query("
			INSERT INTO webchat_users (name, is_admin, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'".DB::esc($this->is_admin)."',
				'".DB::esc($this->gravatar)."'
			) ON DUPLICATE KEY UPDATE last_activity = NOW()");
	}

	public function exists(){
	    return DB::query("
	    SELECT EXISTS(SELECT 1 
	                  FROM webchat_users
	                  WHERE name = '".DB::esc($this->name)."'
	                  AND gravatar = '".DB::esc($this->gravatar)."')
	    ")->fetch_object(0);
    }
}

?>