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
	'description' => 'This backend extension finds any file uploaded by an editor which is not used anymore in the CMS',
	'category' => 'module',
	'version' => '0.3.5',
	'state' => 'alpha',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => false,
	'author' => 'Dan Untenzu',
	'author_email' => 'untenzu@webit.de',
	'author_company' => '',
	'constraints' => array (
		'depends' => array (
			'typo3' => '4.5.0-6.0.99',
		),
		'conflicts' => array (
		),
		'suggests' => array (
		),
	),
);

