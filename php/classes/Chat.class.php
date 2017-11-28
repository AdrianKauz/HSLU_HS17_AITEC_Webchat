<?php

/* The Chat class exploses public static methods, used by ajax.php */

class Chat
{
    /*
    ================
    login()
    ================
    */
    public static function login($name,$email)
    {
        if(!$name || !$email){
            throw new Exception('Fill in all the required fields.');
        }

        if(!filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL)){
            throw new Exception('Your email is invalid.');
        }

        // Preparing the gravatar hash:
        $gravatar = md5(strtolower(trim($email)));

        // Prepare user (Standard is user)
        $user = new ChatUser(array(
            'name'		   => $name,
            'is_admin'	   => '0',
            'is_activated' => 0,
            'gravatar'	   => $gravatar
        ));

        // Check if user exist on the DB
        if($user->exists()){
            if($user->isActivated()) {
                $user->setActive();
                $user->setRole();
            } else{
                throw new Exception('Please wait until you\'re activated!');
            }
        } else {
            throw new Exception('Please register first!');
        }

        // Set session
        $_SESSION['user']	= array(
            'name'		=> $name,
            'is_admin'  => $user->getAdmin(),
            'gravatar'	=> $gravatar
        );

        return array(
            'status'	=> 1,
            'name'		=> $name,
            'is_admin'  => $user->getAdmin(),
            'gravatar'	=> Chat::gravatarFromHash($gravatar)
        );
    }


    /*
    ================
    register()
    ================
    */
    public static function register($name,$email)
    {
        if(!$name || !$email){
            throw new Exception('Fill in all the required fields.');
        }

        if(!filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL)){
            throw new Exception('Your email is invalid.');
        }

        // Preparing the gravatar hash:
        $gravatar = md5(strtolower(trim($email)));

        // Prepare user (Standard is user)
        $user = new ChatUser(array(
            'name'		   => $name,
            'is_active'	   => '0',
            'is_admin'	   => '0',
            'is_activated' => '0',
            'gravatar'	   => $gravatar
        ));

        // Check if user exist on the DB
        if($user->exists()){
            throw new Exception('This nick is in use!');
        } else {
            // Check if an admin exists, if not (because of reasons): This user is automatically an admin
            $user->createRole();
            $result = $user->register();

            if(!$result) {
                throw new Exception("Registering failed!");
            }
        }

        return array('status' => 1);
    }


    /*
    ================
    checkLogged()
    ================
    */
    public static function checkLogged()
    {
        $response = array('logged' => false);

        if($_SESSION['user']['name']){
            $response['logged'] = true;
            $response['loggedAs'] = array(
                'name'		=> $_SESSION['user']['name'],
                'is_admin'	=> $_SESSION['user']['is_admin'],
                'gravatar'	=> Chat::gravatarFromHash($_SESSION['user']['gravatar'])
            );
        }

        return $response;
    }


    /*
    ================
    logout()
    ================
    */
    public static function logout()
    {
        DB::query("UPDATE webchat_users SET is_active = 0 WHERE name = '".DB::esc($_SESSION['user']['name'])."'");
        DB::commit();

        $_SESSION = array();
        unset($_SESSION);

        return array('status' => 1);
    }


    /*
    ================
    submitChat()
    ================
    */
    public static function submitChat($chatText)
    {
        if(!$_SESSION['user']){
            throw new Exception('You are not logged in');
        }

        if(!$chatText){
            throw new Exception('You haven\' entered a chat message.');
        }

        $chat = new ChatLine(array(
            'author'	=> $_SESSION['user']['name'],
            'gravatar'	=> $_SESSION['user']['gravatar'],
            'text'		=> htmlentities($chatText, ENT_QUOTES,"UTF-8") // Convert some predefined characters into HTML entities
        ));

        // The save method returns a MySQLi object
        $insertID = $chat->save()->insert_id;

        return array(
            'status'	=> 1,
            'insertID'	=> $insertID
        );
    }


    /*
    ================
    countUsers()
    ================
    */
    public static function countUsers()
    {
        $result = DB::query('SELECT COUNT(*) as cnt FROM webchat_users') -> fetch_object() -> cnt;

        return array(
            'total' => $result
        );
    }


    /*
    ================
    userIsAdmin()
    ================
    */
    public static function userIsAdmin()
    {
        $result = DB::query("SELECT EXISTS( SELECT 1
                                                FROM webchat_users
                                                WHERE name = '".DB::esc($_SESSION['user']['name'])."' 
                                                AND gravatar = '".DB::esc($_SESSION['user']['gravatar'])."'
                                                AND is_admin = 1)
                                AS res")-> fetch_object() -> res;

        return array(
            'result' => $result
        );
    }


    /*
    ================
    getUsers()
    ================
    */
    public static function getUsers()
    {
        if($_SESSION['user']['name']){
            $user = new ChatUser(array('name' => $_SESSION['user']['name']));
            $user->update();
        }

        // Deleting chats older than 5 minutes
        DB::query("DELETE FROM webchat_lines WHERE ts < SUBTIME(NOW(),'0:5:0')");

        $result = DB::query('SELECT * FROM webchat_users WHERE is_active = 1 AND is_blocked = 0 AND is_activated = 1 ORDER BY name ASC LIMIT 18');

        $users = array();
        while($user = $result->fetch_object()){
            $user->gravatar = Chat::gravatarFromHash($user->gravatar,30);
            $users[] = $user;
        }

        return array(
            'users' => $users,
            'total' => DB::query('SELECT COUNT(*) as cnt FROM webchat_users WHERE is_active = 1')->fetch_object()->cnt
        );
    }


    /*
    ================
    getBlockedUsers()
    ================
    */
    public static function getBlockedUsers(){
        $result = DB::query('SELECT * FROM webchat_users WHERE is_blocked = 1 ORDER BY name ASC LIMIT 18');

        $users = array();
        while($user = $result->fetch_object()){
            $user->gravatar = Chat::gravatarFromHash($user->gravatar,30);
            $users[] = $user;
        }

        return array(
            'users' => $users,
            'total' => DB::query('SELECT COUNT(*) as cnt FROM webchat_users WHERE is_blocked = 1')->fetch_object()->cnt
        );
    }
    /*
    ================
    getNewUsers()
    ================
    */
    public static function getNewUsers(){
        $result = DB::query('SELECT * FROM webchat_users WHERE is_activated = 0 and is_blocked = 0 ORDER BY name ASC LIMIT 18');

        $users = array();
        while($user = $result->fetch_object()){
            $user->gravatar = Chat::gravatarFromHash($user->gravatar,30);
            $users[] = $user;
        }

        return array(
            'users' => $users,
            'total' => DB::query('SELECT COUNT(*) as cnt FROM webchat_users WHERE is_activated = 0 and is_blocked = 0')->fetch_object()->cnt
        );
    }

    /*
    ================
    getChats()
    ================
    */
    public static function getChats($lastID)
    {
        $lastID = (int)$lastID;

        $result = DB::query('SELECT * FROM webchat_lines WHERE id > '.$lastID.' ORDER BY id ASC');

        $chats = array();
        while($chat = $result->fetch_object()){

            // Returning the GMT (UTC) time of the chat creation:
            $chat->time = array(
                'hours'		=> gmdate('H',strtotime($chat->ts)),
                'minutes'	=> gmdate('i',strtotime($chat->ts))
            );

            $chat->gravatar = Chat::gravatarFromHash($chat->gravatar);

            $chats[] = $chat;
        }

        return array('chats' => $chats);
    }


    /*
    ================
    blockUser()
    ================
    */
    public static function blockUser($sUserName)
    {
        return self::privBlockUser($sUserName, true);
    }


    /*
    ================
    unblockUser()
    ================
    */
    public static function unblockUser($sUserName)
    {
        return self::privBlockUser($sUserName, false);
    }


    /*
    ================
    privBlockUser()
    ================
    */
    private static function privBlockUser($sUserName, $bBlockUser)
    {
        $success = null;

        if($bBlockUser){
            $success = DB::query("UPDATE webchat_users SET is_blocked = 1, is_active = 0 WHERE name = '".DB::esc($sUserName)."' AND is_blocked = 0");
        } else {
            $success = DB::query("UPDATE webchat_users SET is_blocked = 0 WHERE name = '".DB::esc($sUserName)."' AND is_blocked = 1");
        }

        return array('result' => ($success == false) ? false : true);
    }

    /*
    ================
    unblockUser()
    ================
    */
    public static function activateUser($sUserName)
    {
        return self::privSetActivatedState($sUserName, true);
    }

    /*
    ================
    privSetActivatedState()
    ================
    */
    private static function privSetActivatedState($sUserName, $bActivatedState)
    {
        $success = null;

        if($bActivatedState){
            $success = DB::query("UPDATE webchat_users SET is_activated = 1 WHERE name = '".DB::esc($sUserName)."' AND is_activated = 0");
        } else {
            $success = DB::query("UPDATE webchat_users SET is_activated = 0 WHERE name = '".DB::esc($sUserName)."' AND is_activated = 1");
        }

        return array('result' => ($success == false) ? false : true);
    }


    /*
    ================
    getStatus()
    ================
    */
    public static function getStatus()
    {
        $result = DB::query("SELECT is_admin, is_blocked FROM webchat_users WHERE name = '".DB::esc($_SESSION['user']['name'])."'");

        if(!$result){
            return array(
                'is_admin' => null,
                'is_blocked' => null
            );
        } else {
            $obj = $result->fetch_object();
            return array(
                'is_admin' => ($obj->is_admin == 1) ? true : false,
                'is_blocked' => ($obj->is_blocked == 1) ? true : false
            );
        }
    }


    /*
    ================
    gravatarFromHash()
    ================
    */
    public static function gravatarFromHash($hash, $size=23)
    {
        return 'http://www.gravatar.com/avatar/'.$hash.'?size='.$size.'&amp;default='.
            urlencode('http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?size='.$size);
    }
}
?>