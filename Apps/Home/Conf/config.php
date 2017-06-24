<?php
return array(
	//'配置项'=>'配置值'
    'URL_ROUTER_ON'   => true,
    'URL_MODEL'        => '2',
    'MODULE_ALLOW_LIST' => array ('Home','Admin'),
    'DEFAULT_MODULE' => 'Home',
    'TMPL_L_DELIM'=>'<{',
    'TMPL_R_DELIM'=>'}>',
    'DB_TYPE'      =>  'mysqli',
    'DB_HOST'      =>  'localhost',
    'DB_NAME'      =>  'blog',
    'DB_USER'      =>  'root',
    'DB_PWD'       =>  'root',
    'DB_PORT'      =>  '3306',
    'DB_PREFIX'    =>  'blog_',
    'DB_CHARSET'   =>  'utf8',
);