<?php

class ChatUser extends ChatBase
{
	
	protected $name = '';
    protected $is_admin = '';
    protected $is_blocked = '';
    protected $is_activated = '';
    protected $gravatar = '';

    /*
    ================
    save()
    ================
    */
	public function save()
    {
		DB::query("
			INSERT INTO webchat_users (name, is_active, is_admin, is_activated, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'1',
				'".DB::esc($this->is_admin)."',
				'".DB::esc($this->is_activated)."',
				'".DB::esc($this->gravatar)."'
		)");
		
		return DB::getMySQLiObject();
	}

    /*
    ================
    register()
    ================
    */
    public function register()
    {
        return DB::query("
			INSERT INTO webchat_users (name, is_active, is_admin, is_activated, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'0',
				'".DB::esc($this->is_admin)."',
				'".DB::esc($this->is_activated)."',
				'".DB::esc($this->gravatar)."'
		)");

        // return DB::getMySQLiObject();
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
	                                ) AS cnt
	                  ")->fetch_object()->cnt;

	    return $result;
    }



    /*
    ================
    exists()
    ================
    */
    public function isActivated()
    {
        $result = DB::query("
	                  SELECT is_activated
                        FROM webchat_users
                        WHERE name = '".DB::esc($this->name)."'
                        AND gravatar = '".DB::esc($this->gravatar)."'
	                  ")->fetch_object()->is_activated;

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

    /*
    ================
    setActivated()
    ================
    */
    public function setActivated()
    {
        DB::query("
            UPDATE webchat_users
            SET is_activated = 1
            WHERE name = '".DB::esc($this->name)."'
            AND gravatar = '".DB::esc($this->gravatar)."'");
    }

    /*
    ================
    createRole()
    ================
    */
    public function createRole()
    {
        $result = DB::query('SELECT EXISTS(SELECT 1
                                              FROM webchat_users
                                              WHERE is_admin = 1)
                                              AS res')->fetch_object()->res;

        $this->is_admin = ($result == 0) ? '1' : '0';
        $this->is_activated = ($result == 0) ? '1' : '0';
    }

    /*
    ================
    setRole()
    ================
    */
    public function setRole()
    {
        $this->is_admin = DB::query("SELECT is_admin AS res
                                        FROM webchat_users
                                        WHERE name = '".DB::esc($this->name)."'
                                        AND gravatar = '".DB::esc($this->gravatar)."'")->fetch_object()->res;
    }
}
?>