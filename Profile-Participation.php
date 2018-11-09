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

/********************************************************************************
* The functions necessary to list our topics for the user:
********************************************************************************/
function TUPC_showTopics()
{
	global $context, $txt, $scripturl, $modSettings, $smcFunc, $sourcedir, $user_info;

	// Set up for listing the "important" topics:
	loadTemplate('ManageAttachments');
	$context['page_title' ] = $txt['TUPC_topics'];
	$context['sub_template'] = 'attachment_paths';
	$context['topics_created'] = !(isset($_GET['sa']) && $_GET['sa'] == 'participated');
	$_GET['u'] = (int) (isset($_GET['u']) ? $_GET['u'] : $user_info['id']);

	// Create the tabs for the template.
	$context[$context['profile_menu_name']]['tab_data'] = array(
		'title' => $txt['TUPC_topics'],
		'description' => $txt['TUPC_topics_desc'],
		'icon' => 'profile_sm.gif',
		'tabs' => array(
			'created' => array(
			),
			'participated' => array(
			),
		),
	);

	// Set the options for the list component.
	$topic_listOptions = array(
		'id' => 'attach_paths',
		'title' => $txt['TUPC_topics'],
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'base_href' => $scripturl . '?action=profile;area=threads;sa=' . ($context['topics_created'] ? 'created' : 'participated'),
		'default_sort_col' => 'lastpost',
		'default_sort_dir' => 'desc',
		'no_items_label' => $context['topics_created'] ? $txt['TUPC_no_topics_created'] : $txt['TUPC_no_topics_participated'],
		'get_items' => array(
			'function' => $context['topics_created'] ? 'TUPC_Created' : 'TUPC_Participated',
		),
		'get_count' => array(
			'function' => $context['topics_created'] ? 'TUPC_Created_Count' : 'TUPC_Participated_Count',
		),
		'columns' => array(
			'subject' => array(
				'header' => array(
					'value' => $txt['topics'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl, $txt;
						$board = \'<strong><a href="\' . $scripturl . \'?board=\' . $rowData["id_board"] . \'.0">\' . $rowData[\'board_name\'] . \'</a></strong>\';
						$topic = \'<strong><a href="\' . $scripturl . \'?topic=\' . $rowData["id_topic"] . \'.0">\' . $rowData[\'first_subject\'] . \'</a></strong>\';
						if (!empty($rowData["first_member"]))
							$user = \'<a href="\' . $scripturl . \'?action=profile;user=\' . $rowData["first_member"] . \'">\' . $rowData[\'first_poster\'] . \'</a>\';
						else
							$user = $rowData["first_member"];
						return $board . " \\\\ " . $topic . \'<div class="smalltext">\' . $txt["started_by"] . " <strong>" . $user . \'</strong></div>\';
					'),
				),
				'sort' => array(
					'default' => 'b.name, mf.subject',
					'reverse' => 'b.name DESC, mf.subject DESC',
				),
			),
			'replies' => array(
				'header' => array(
					'value' => $txt['replies'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						return comma_format($rowData[\'num_replies\']);
					'),
					'style' => 'text-align: center; width: 7%',
				),
				'sort' => array(
					'default' => 't.num_replies',
					'reverse' => 't.num_replies DESC',
				),
			),
			'views' => array(
				'header' => array(
					'value' => $txt['views'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						return comma_format($rowData[\'num_views\']);
					'),
					'style' => 'text-align: center; width: 7%',
				),
				'sort' => array(
					'default' => 't.num_views',
					'reverse' => 't.num_views DESC',
				),
			),
			'lastpost' => array(
				'header' => array(
					'value' => $txt['last_post'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl, $txt;
						if (!empty($rowData["last_member"]))
							$user = \'<a href="\' . $scripturl . \'?action=profile;user=\' . $rowData["last_member"] . \'">\' . $rowData[\'last_poster\'] . \'</a>\';
						else
							$user = $rowData["last_member"];
						return "<strong>" . $txt["last_post"] . "</strong> " . $txt["by"] . " <strong>" . $user . \'</strong><div class="smalltext">\' . timeformat($rowData[\'last_posted\']);
					'),
					'style' => 'width: 30%',
				),
				'sort' => array(
					'default' => 'ml.poster_time',
					'reverse' => 'ml.poster_time DESC',
				),
			),
		),
	);

	// Create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($topic_listOptions);
}

/********************************************************************************
* Functions that get the topics the user has created:
********************************************************************************/
function TUPC_Created_Count()
{
	global $smcFunc;
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS count
		FROM {db_prefix}topics AS t
			LEFT JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE {query_see_board}
			AND id_member_started = {int:id_member}' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			AND b.id_board != {int:recycle_board}' : ''),
		array(
			'id_member' => (int) $_GET['u'],
			'recycle_board' => $modSettings['recycle_board'],
		)
	);
	list($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	return $count;
}

function TUPC_Created($start, $items_per_page, $sort)
{
	global $smcFunc;
 	$request = $smcFunc['db_query']('', '
		SELECT
			t.id_topic, t.num_replies, t.num_views, t.id_first_msg, b.id_board, b.name AS board_name,
			mf.id_member AS first_member, COALESCE(meml.real_name, ml.poster_name) AS last_poster, 
			ml.id_member AS last_member, COALESCE(memf.real_name, mf.poster_name) AS first_poster, 
			mf.subject AS first_subject, mf.poster_time AS first_posted,
			ml.subject AS last_subject, ml.poster_time AS last_posted
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			LEFT JOIN {db_prefix}members AS memf ON (memf.id_member = mf.id_member)
			LEFT JOIN {db_prefix}members AS meml ON (meml.id_member = ml.id_member)
		WHERE {query_see_board}
			AND t.id_member_started = {int:id_member}' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			AND b.id_board != {int:recycle_board}' : '') . '
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:per_page}',
		array(
			'recycle_board' => $modSettings['recycle_board'],
			'sort' => $sort,
			'start' => $start,
			'per_page' => $items_per_page,
			'id_member' => (int) $_GET['u'],
		)
	);
	$topics = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$topics[] = $row;
	$smcFunc['db_free_result']($request);
	return $topics;
}

/********************************************************************************
* Functions that get the topics the user has participated in:
********************************************************************************/
function TUPC_Participated_Count()
{
	global $smcFunc, $user_info, $context;
	
	$request = $smcFunc['db_query']('', '
		SELECT DISTINCT id_topic AS id_topic
		FROM {db_prefix}messages AS m
			LEFT JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE {query_see_board}
			AND id_member = {int:id_member}' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			AND b.id_board != {int:recycle_board}' : ''),
		array(
			'id_member' => (int) $_GET['u'],
			'recycle_board' => $modSettings['recycle_board'],
		)
	);
	$topics = array(0);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$topics[] = $row['id_topic'];
	$smcFunc['db_free_result']($request);
	$context['TUPC_topics'] = $topics;
	return count($topics) - 1;
}

function TUPC_Participated($start, $items_per_page, $sort)
{
	global $smcFunc, $user_info, $context;
	
	// Is the topic list defined?  If not, then do so before continuing:
	if (empty($context['TUPC_topics']))
		TUPC_Participated_Count();

	// Do the database query:
	$request = $smcFunc['db_query']('', '
		SELECT
			t.id_topic, t.num_replies, t.num_views, t.id_first_msg, b.id_board, b.name AS board_name,
			mf.id_member AS first_member, COALESCE(meml.real_name, ml.poster_name) AS last_poster, 
			ml.id_member AS last_member, COALESCE(memf.real_name, mf.poster_name) AS first_poster, 
			mf.subject AS first_subject, mf.poster_time AS first_posted,
			ml.subject AS last_subject, ml.poster_time AS last_posted
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			LEFT JOIN {db_prefix}members AS memf ON (memf.id_member = mf.id_member)
			LEFT JOIN {db_prefix}members AS meml ON (meml.id_member = ml.id_member)
		WHERE {query_see_board}
			AND t.id_topic IN ({array_int:topics})' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			AND b.id_board != {int:recycle_board}' : '') . '
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:per_page}',
		array(
			'recycle_board' => $modSettings['recycle_board'],
			'sort' => $sort,
			'start' => $start,
			'per_page' => $items_per_page,
			'topics' => $context['TUPC_topics'],
		)
	);
	$topics = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$topics[] = $row;
	$smcFunc['db_free_result']($request);
	return $topics;
}

?>