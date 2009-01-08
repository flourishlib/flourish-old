<?php
$config = array();

$config['active_database'] = 'sqlite';

$config['sqlite'] = array();
$config['sqlite']['database'] = './database/f_database_test.db';
$config['sqlite']['username'] = NULL; 
$config['sqlite']['password'] = NULL; 
$config['sqlite']['host']     = NULL; 
$config['sqlite']['port']     = NULL; 

$config['mysql'] = array();
$config['mysql']['database'] = 'f_database_test';
$config['mysql']['username'] = 'flourish'; 
$config['mysql']['password'] = 'test'; 
$config['mysql']['host']     = 'bond'; 
$config['mysql']['port']     = NULL; 

$config['pgsql'] = array();
$config['pgsql']['database'] = 'f_database_test';
$config['pgsql']['username'] = 'flourish'; 
$config['pgsql']['password'] = 'test'; 
$config['pgsql']['host']     = 'bond'; 
$config['pgsql']['port']     = NULL; 

$config['mssql'] = array();
$config['mssql']['database'] = 'f_database_test';
$config['mssql']['username'] = 'flourish'; 
$config['mssql']['password'] = 'test'; 
$config['mssql']['host']     = 'bond'; 
$config['mssql']['port']     = NULL; 
?>
