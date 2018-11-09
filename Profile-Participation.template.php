<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

// Template for showing all the posts of the user, in chronological order.
function template_showThreads()
{
	global $context, $scripturl, $txt;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['showThreads'], ' - ', $context['member']['name'], '
			</h3>
		</div>
		<div class="pagesection">
			<span>', $txt['pages'], ': ', $context['page_index'], '</span>
		</div>';

	// For every post to be displayed, give it its own div, and show the important details of the post.
	foreach ($context['posts'] as $post)
	{
		echo '
		<div class="topic">
			<div class="', $post['alternate'] == 0 ? 'windowbg2' : 'windowbg', ' core_posts">
				<span class="topslice"><span></span></span>
				<div class="content">
					<div class="counter">', $post['counter'], '</div>
					<div class="topic_details">
						<h5><strong><a href="', $scripturl, '?board=', $post['board']['id'], '.0">', $post['board']['name'], '</a> / <a href="', $scripturl, '?topic=', $post['topic'], '.', $post['start'], '#msg', $post['id'], '">', $post['subject'], '</a></strong></h5>
						<span class="smalltext">&#171;&nbsp;<strong>', $txt['on'], ':</strong> ', $post['time'], '&nbsp;&#187;</span>
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>';
	}

	// Show more page numbers.
	echo '
		<div class="pagesection" style="margin-bottom: 0;">
			<span>', $txt['pages'], ': ', $context['page_index'], '</span>
		</div>';
}

?>