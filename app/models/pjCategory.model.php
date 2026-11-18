<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCategoryModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'categories';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'category', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'meta_title', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'meta_keywords', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'meta_description', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'url_slug', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	protected $validate = array(
		'rules' => array(
			'type' => 'pjActionRequired'
		)
	);
	
	public static function factory($attr=array())
	{
		return new pjCategoryModel($attr);
	}
}
?>