<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCommentModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'comments';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'category_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'post_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'comment', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public static function factory($attr=array())
	{
		return new pjCommentModel($attr);
	}
}
?>