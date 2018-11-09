<?php
/********************************************************************************
* Subs-Participation.php - Subs of the Topics Participated/Created mod
*********************************************************************************
* This program is distributed in the hope that it is and will be useful, but
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE,
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

function TUPC_profile(&$buttons)
{
	global $txt, $scripturl;
	$new = array();
	foreach ($buttons['info']['areas'] as $id => $info)
	{
		$new[$id] = $info;
		if ($id == 'showposts')
		{
			$new['threads'] = array(
				'label' => $txt['TUPC_topics'],
				'file' => 'Profile-Participation.php',
				'function' => 'TUPC_showTopics',
				'subsections' => array(
					'created' => array($txt['TUPC_user_created'], array('profile_view_own', 'profile_view_any')),
					'participated' => array($txt['TUPC_user_participated'], array('profile_view_own', 'profile_view_any')),
				),
				'permission' => array(
					'own' => array('profile_view_own'),
					'any' => array('profile_view_any'),
				),
			);
		}
	}
	$buttons['info']['areas'] = $new;
}

?>