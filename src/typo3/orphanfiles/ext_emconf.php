<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "orphanfiles".
 *
 * Auto generated 08-05-2015 13:57
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Orphan Files',
	'description' => 'This backend extension finds any file uploaded by an editor which is not used anymore in the CMS - Not working with DAM/Workspaces/FAL, please read the manual!',
	'category' => 'module',
	'version' => '0.4.1',
	'state' => 'beta',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => false,
	'author' => 'Dan Untenzu',
	'author_email' => 'untenzu@webit.de',
	'author_company' => '',
	'constraints' => array (
		'depends' => array (
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array (
		),
		'suggests' => array (
		),
	),
);

