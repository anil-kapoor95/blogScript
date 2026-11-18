<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjPostModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'posts';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'category_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'author_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'title', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'body', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'source_path', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'medium_path', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'thumb_path', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'image_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'meta_title', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'meta_keywords', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'meta_description', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'url_slug', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'published_date', 'type' => 'date', 'default' => ':NOW()'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public static function factory($attr=array())
	{
		return new pjPostModel($attr);
	}
}
?>