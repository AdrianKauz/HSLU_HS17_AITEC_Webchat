<?php

class ChatUser extends ChatBase
{
	
	protected $name = '';
    protected $is_admin = '';
    protected $gravatar = '';

    /*
    ================
    save()
    ================
    */
	public function save()
    {
		DB::query("
			INSERT INTO webchat_users (name,is_active, is_admin, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'1',
				'".DB::esc($this->is_admin)."',
				'".DB::esc($this->gravatar)."'
		)");
		
		return DB::getMySQLiObject();
	}

    /*
    ================
    update()
    ================
    */
	public function update()
    {
		DB::query("
			INSERT INTO webchat_users (name,is_active, is_admin, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'1',
				'".DB::esc($this->is_admin)."',
				'".DB::esc($this->gravatar)."'
			) ON DUPLICATE KEY UPDATE last_activity = NOW()");
	}

    /*
    ================
    exists()
    ================
    */
	public function exists()
    {
	    $result = DB::query("
	                  SELECT EXISTS(SELECT 1 
	                                FROM webchat_users
	                                WHERE name = '".DB::esc($this->name)."'
	                                AND gravatar = '".DB::esc($this->gravatar)."'
	                                ) as cnt
	                  ")->fetch_object()->cnt;

	    return $result;
    }

    /*
    ================
    setAdmin()
    ================
    */
    public function setAdmin($newValue)
    {
	    $this->is_admin = $newValue;
    }

    /*
    ================
    getAdmin()
    ================
    */
    public function getAdmin()
    {
        return $this->is_admin;
    }

    /*
    ================
    setActive()
    ================
    */
    public function setActive()
    {
        DB::query("
            UPDATE webchat_users
            SET is_active = 1
            WHERE name = '".DB::esc($this->name)."'
            AND gravatar = '".DB::esc($this->gravatar)."'");
    }
}
?>