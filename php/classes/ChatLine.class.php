<?php
/* Chat line is used for the chat entries */

class ChatLine extends ChatBase
{
	protected $text = '';
	protected $author = '';
	protected $gravatar = '';

    /*
    ================
    save()
    ================
    */
	public function save()
    {
		DB::query("
			INSERT INTO
			webchat_lines(
			  author,
			  gravatar,
			  text)
			VALUES (
				'".DB::esc($this->author)."',
				'".DB::esc($this->gravatar)."',
				'".DB::esc($this->text)."'
		)");
		
		// Returns the MySQLi object of the DB class
		return DB::getMySQLiObject();
	}
}
?>