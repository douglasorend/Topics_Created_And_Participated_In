<?php
$SSI_INSTALL = false;
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$SSI_INSTALL = true;
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');
require($sourcedir.'/Subs-Admin.php');
db_extend('packages');

//==============================================================================
// Insert one column into the necessary tables:
//==============================================================================
// {prefix}boards table gets a new column to hold the number of anonymous posts:
$smcFunc['db_add_column'](
	'{db_prefix}boards', 
	array(
		'name' => 'post_anon', 
		'size' => 8, 
		'type' => 'int', 
		'null' => false, 
		'default' => 0
	)
);
// {prefix}members table gets a new column to hold the number of anonymous posts:
$smcFunc['db_add_column'](
	'{db_prefix}members', 
	array(
		'name' => 'anon_posts', 
		'size' => 8, 
		'type' => 'int', 
		'null' => false, 
		'default' => 0
	)
);
// {prefix}messages table gets a new column to hold the ID of anonymous poster:
$smcFunc['db_add_column'](
	'{db_prefix}messages', 
	array(
		'name' => 'anonymous', 
		'size' => 8, 
		'type' => 'int', 
		'null' => false, 
		'default' => 0
	)
);

//==============================================================================
// Set the default value for the PAM mode if not already set:
//==============================================================================
if (!isset($modSettings['PAM_mode']))
	updateSettings(	array( 'PAM_mode' => 3 ) );

// Echo that we are done if necessary:
if ($SSI_INSTALL)
	echo 'DB Changes should be made now...';
?>