<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "orphanfiles".
 *
 * Auto generated 17-04-2013 13:45
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Orphan Files',
	'description' => 'This backend extension finds any file uploaded by an editor which is not used anymore in the CMS',
	'category' => 'module',
	'author' => 'Dan Untenzu',
	'author_email' => 'untenzu@webit.de',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.3.4',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.0.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>