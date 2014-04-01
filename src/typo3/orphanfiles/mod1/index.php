<?php
/***************************************************************
*  Copyright notice
*
*  Dan Untenzu <untenzu@webit.de>
*  All rights reserved
*
*  This extension is a fork of EXT:kb_cleanfiles, which was
*  written by Bernhard Kraft <kraftb@gmx.net> and
*  Thorsten Reichelt <Thorsten_Reichelt@gmx.de>.
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Module 'Orphan Files' for the 'orphanfiles' extension.
 *
 */

unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$GLOBALS['LANG']->includeLLFile('EXT:orphanfiles/mod1/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.

class tx_orphanfiles_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Returns nothing. Initializes the class.
	 *
	 * @return	void		nothing
	 */
	function init()	{
		global $AB,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void		nothing
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return	void		nothing
	 */
	function main()	{
		global $AB,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id) || ($BE_USER->user['uid'] && !$this->id)) {
			// get TSconfig
			//// Since this module isnt located in »Web« the page uid will be 0,
			//// therefore uid »1« is defined as alternative instead - good enough for now
			//$modTSconfig = t3lib_BEfunc::getModTSconfig(1, 'mod.' . $this->MCONF['name']);
			$modTSconfig = t3lib_BEfunc::getModTSconfig($this->id, 'mod.' . $this->MCONF['name']);

			$this->modTSconfig = $modTSconfig['properties'];

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			//$this->doc->form='<form action="" method="POST">';

			// JavaScript
			$this->doc->JScode = '
				<script language="javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript">
					script_ended = 1;
					if (top.theMenu) top.theMenu.recentuid = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']) . '<br>' . $LANG->php3Lang['labels']['path'] . ': ' . t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'], 50);

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content .= $this->doc->divider(5);

			// Render content:
			$this->moduleContent();

			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content .= $this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content .= $this->doc->spacer(10);
		}
		else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void		nothing
	 */
	function printContent()	{
		global $SOBE;

		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void		nothing
	 */
	function moduleContent(){
		global $LANG;

		// Switch function
		switch((string)$this->MOD_SETTINGS['function'])	{
			// Introduction text
			case 1:
				$content = $LANG->getLL('description');
				$this->content .= $this->doc->section($LANG->getLL('titleIntroduction'), $content, 0, 1);
				break;

			// Crawl Files
			case 2:
				// Get all deletable files - Can be very time consuming
				$orphanFiles = $this->crawlFiles();

				$content .= $this->doc->sectionHeader(sprintf($LANG->getLL('filesFound'), count($orphanFiles)));
				if(count($orphanFiles)) {
					$content .= '<table width="470" border="0" cellspacing="2" cellpadding="2">'.chr(10);
					foreach ($orphanFiles as $file) {
						$content .= '<tr>';
						$content .= '<td style="padding: 4px; padding-left: 0; border-bottom: 1px dashed #8C8C8C;">';
						if(!empty($this->modTSconfig['baseurl'])) {
							$content .= '<a href="' . $this->modTSconfig['baseurl'] . $file . '" target="_blank">' . $file . '</a>';
						}
						else {
							$content .= $file;
						}
						$content .= '</td>';
						$content .= '</tr>' . chr(10);
					}
					$content .= '</table>';
				}
				else {
					$content .= '<p>' . $LANG->getLL('noFiles') . '</p>';
				}

				$this->content .= $this->doc->section($LANG->getLL('titleCrawling'), $content, 0, 1);
				break;

			// Delete files
			case 3:
				$cmd = t3lib_div::_GP('cmd');
				switch($cmd) {
					// Delete selected file/files
					case 'clear':
						$content .= $LANG->getLL('deleted') . ':<br />';
						$content .= $this->clearFiles(0);

						$content .= '<a style="display: block; margin-top: 30px;" href="index.php?id=' . $this->id . '">' . $LANG->getLL('backlink') . '</a>';
						$this->content .= $this->doc->section($LANG->getLL('titleClear'), $content, 0, 1);
						break;

					// Delete all files at once
					case 'clearall':
						$content .= $LANG->getLL('deleted') . ':<br />';
						if($this->modTSconfig['showDeleteAllButton']) {
							$content .= $this->clearFiles(1);
						}

						$content .= '<a style="display: block; margin-top: 30px;" href="index.php?id=' . $this->id . '">' . $LANG->getLL('backlink') . '</a>';
						$this->content .= $this->doc->section($LANG->getLL('titleClearall'), $content, 0, 1);
						break;

					// List all orphaned files and show delete actions
					default:
						$crawlingProcess = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
							'tstamp',
							'tx_orphanfiles_process',
							'active=0 AND deleted=0'
						);
						if(!empty($crawlingProcess)) {
							// Get all deletable files
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'uid,file_path',
								'tx_orphanfiles_queue',
								''
							);

							if($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
								$content .= $this->doc->sectionHeader(sprintf($LANG->getLL('crawlingNote'), date('d M Y H:i', $crawlingProcess['tstamp'])));

								$content .= '<form name="clear" action="index.php?id=' . $this->id . '" method="POST" enctype="multipart/form-data">';
								$content .= '<table width="470" border="0" cellspacing="2" cellpadding="2">' . chr(10);
								while($file = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
									$content .= '<tr>';
									$content .= '<td style="padding: 4px; padding-left: 0;  border-bottom: 1px dashed #8C8C8C;">';
									if(!empty($this->modTSconfig['baseurl'])) {
										$content .= '<a href="' . $this->modTSconfig['baseurl'] . $file['file_path'] . '" target="_blank">' . $file['file_path'] . '</a>';
									}
									else {
										$content .= $file['file_path'];
									}
									$content .= '</td>';
									if($this->modTSconfig['showDeleteCheckbox']) {
										// show delete checkbox
										$content .= '<td style="padding: 4px; padding-right: 0; border-bottom: 1px dashed #8C8C8C; width: 55px;"><input type="checkbox" id="fileUID-' . $file['uid'] . '" name="fileUID[]" value="' . $file['uid'] . '">&nbsp;<label for="fileUID-' . $file['uid'] . '">' . $LANG->getLL('clear') . '</label></td>';
									}
									else {
										// show delete button
										$content .= '<td style="padding: 4px; padding-right: 0; border-bottom: 1px dashed #8C8C8C; width: 40px;"><a style="display: inline-block; background: #FF8700; padding: 3px;" href="index.php?id=' . $this->id . '&cmd=clear&fileUID[]=' . urlencode($file['uid']) . '">' . $LANG->getLL('clear') . '</a></td>';
									}
									$content .= '</tr>' . chr(10);
								}
								$content .= '</table>';

								if($this->modTSconfig['showDeleteCheckbox']) {
									$content .= '<input type="submit" name="submit" value="' . $LANG->getLL('clearcheckbox') . '"'
										. ' style="border: 1px solid #black; background-color: #FFAD37; width: 470px;">'
										. '<input type="hidden" name="cmd" value="clear">';
								}
								$content .= '</form>' . chr(10);

								if($this->modTSconfig['showDeleteAllButton']) {
									// Show button to delete all files at once
									$content .= $this->doc->spacer(5);
									$content .= '<form style="margin-top: 30px;" name="clearall" action="index.php?id=' . $this->id . '" method="POST" enctype="multipart/form-data">'
										. '<input type="submit" name="submit" value="' . $LANG->getLL('clearall') . '"'
										. ' onClick="return confirm(\'' . $LANG->getLL('clearall_confirm') . '\');"'
										. ' style="border: 1px solid #black; background-color: #FFAD37; width: 470px;">'
										. '<input type="hidden" name="cmd" value="clearall">'
										. '</form>' . chr(10);
								}
							}
							else {
								$content .= '<p>' . $LANG->getLL('noFiles') . '</p>';
							}
						}
						else {
							// no valid crawling process found
							$content .= $this->doc->sectionHeader($LANG->getLL('crawlingError'));
						}

						$this->content .= $this->doc->section($LANG->getLL('titleDeletion'), $content, 0, 1);
						break;
				}
				break;
		}
	}

	/*
	 * Returns the image fields found in a flexform DS
	 *
	 * @params	array		The flexform DS (XML as PHP array)
	 * @params	array		The array which contains the return values and gets filled while traveresing
	 * @params 	array		A path to the actual item
	 *
	 * @return	array		A array of images and their uploadfolders.
	 */
	function getImageFields_Flex_Rec($flexformDS, $fields = array(), $path = array()) {
		$originalPath = $path;
		if (is_array($flexformDS)) {
			// crawl trough each flexform node
			foreach($flexformDS as $label => $flexformNode) {
				$lastname = end($path);
				$path[] = $label . ($flexformDS['section']? '|section' : '');

				if ($label === 'TCEforms') {
					// went all the way down to TCE configuration of the field
					// now store the type and possible uploadfolders

					// file upload
					if (($flexformNode['config']['type'] === 'group') && ($flexformNode['config']['internal_type'] === 'file')) {
						// get uploadfolder
						$uploadPath = (!empty($flexformNode['config']['uploadfolder']))? $flexformNode['config']['uploadfolder'] . '/' : '';
						//$allowed = $flexformNode['config']['allowed'];
						//$disallowed = $flexformNode['config']['disallowed'];
						$new = array(
							'field' => $lastname,
							'type' => $flexformNode['config']['type'],
							'path' => $path,
							'uploadPath' => $uploadPath,
							//'allowed' => $allowed,
							//'disallowed' => $disallowed
						);
						if (!in_array($new, $fields))	{
							$fields[] = $new;
						}
					}
					// …add input fields and textareas as well
					else if(($flexformNode['config']['type'] === 'input') OR ($flexformNode['config']['type'] === 'text')) {
						$fields[] = array(
							'field' => $lastname,
							'type' => $flexformNode['config']['type'],
							'path' => $path
						);
					}
				}
				else {
					// we are not deep enough in the XML yet, call this method recursively
					$fields = $this->getImageFields_Flex_Rec($flexformNode, $fields, $path);
				}

				$path = $originalPath;
			}
		}
		return $fields;
	}

	/*
	 * Parses the basic Flexform data array.
	 *
	 * @param	 array		The XML Data as PHP array
	 * @param	 array		The image field description (fieldname, type) which we are currently processing
	 * @param	 array		The return value, passed by reference
	 * @param	 integer	The type. 1=deleted record, 2=normal record
	 * @return	void
	 */
	function parseFlexData($editData, $imageField, &$fileStack, $type) {
		$node = array_shift($imageField['path']);

		// Clean up sheets
		if ($node === 'sheets') {
			// [path] => (
			//	[0] => sheets
			//	[1] => sMISC
			//	[2] => ROOT
			//	[3] => el
			//	[4] => fieldname
			//	[5] => TCEforms
			// )
			// Shift one more time to get the sheet name
			$sheet = array_shift($imageField['path']);
			// Select correct data array
			$editData = $editData[$sheet];
			// Shoft one more time to reach »ROOT«
			$node = array_shift($imageField['path']);
		}
		else {
			// [path] => (
			//	[0] => ROOT
			//	[1] => el
			//	[2] => fieldname
			//	[3] => TCEforms
			// )

			// Select default data array
			$editData = $editData['sDEF'];
		}

		// The root node of the flexform datastructure should be »ROOT«
		if ($node === 'ROOT') {
			if (is_array($editData)) {
				// ???
				foreach ($editData as $lKey => $lArr) {
					$this->parseFlexData_LLang($lArr, $imageField, $fileStack, $type);
				}
			}
		}
	}

	/*
	 * Parses hierarchical field structures
	 *
	 * @param	 array		The XML Data as PHP array
	 * @param	 array		The image field description (fieldname, type) which we are currently processing
	 * @param	 array		The return value, passed by reference
	 * @param	 integer	The type. 1=deleted record, 2=normal record
	 * @return	void
	 */
	function parseFlexData_LLang($editData, $imageField, &$fileStack, $type) {
		// [path] => (
		//	[0] => ROOT
		//	[1] => el
		//	[2] => fieldname
		//	[3] => TCEforms
		// )

		// current flexform path
		// on first method call this will return »el«
		// on second method call this will return »TCEforms« due to the shift within the following if
		$node = array_shift($imageField['path']);

		// first node
		if ($node === 'el')	{
			// shift one step, so f equals the fieldname
			$f = array_shift($imageField['path']);

			if (is_array($editData['el']))	{
				$this->parseFlexData_LLang($editData['el'][$f], $imageField, $fileStack, $type);
				$editData = $editData['el'][$f];
			}
			else {
				$this->parseFlexData_LLang($editData[$f], $imageField, $fileStack, $type);
				$editData = $editData[$f];
			}
		}
		// special node - build at the beginning of getImageFields_Flex_Rec()
		elseif ($node === 'el|section') {
			$f = array_shift($imageField['path']);
			if (is_array($editData['el']))	{
				foreach ($editData['el'] as $idx => $subData)	{
					$this->parseFlexData_LLang($subData[$f]['el'], $imageField, $fileStack, $type);
				}
			}
		}
		// …reached the last item/node in the path
		elseif ($node === 'TCEforms') {
			if (is_array($editData)) {
				foreach ($editData as $fieldName => $fieldContent)	{
					if(!empty($fieldContent)) {
						// file upload
						if ($imageField['type'] == 'group') {

							// split given csv file list
							$files = explode(',', $fieldContent);
							foreach ($files as $file) {
								$file = trim($file);
								if(!empty($file)) {
									$fileStack[] = $imageField['uploadPath'] . $file;
								}
							}
						}
						// filelinks in input fields or textareas
						else if(($imageField['type'] == 'input') OR ($imageField['type'] == 'text')) {
							// grep for file links to mountable folders /fileadmin and /uploads
							preg_match_all('/(fileadmin|uploads)\/(\w|-|\/)+\.\w{2,4}/', $fieldContent, $files);
							$fileStack = array_merge($fileStack, $files[0]);
						}
					}
				}
			}
		}
	}

	/**
	 * Returns a set of tables to be searched (either all tables in the TCA
	 * or all tables defined in the TSconfig of this extension)
	 *
	 * @return	array		list of all table names and fields which should be searched
	 */
	function getTableHaystack() {
		$tableHaystack = array();

		// A custom range of tables or fields is defined in TSconfig
		if (!empty($this->modTSconfig['crawl.'])) {
			$tableHaystack = $this->modTSconfig['crawl.'];
		}
		// default - select all tables in the TCA
		else {
			foreach ($GLOBALS['TCA'] as $tableName => $TCA) {
				$tableHaystack[$tableName] = '*';
			}
		}

		return $tableHaystack;
	}

	/*
	 * Find all files referenced in CMS tables
	 *
	 * @return	array	Set of filenames (with absolute path)
	 */
	function findReferencedFiles() {
		$fileStack = array();

		// get all fields which are supposed to be searched
		$tableHaystack = $this->getTableHaystack();

		foreach ($tableHaystack as $table => $fields) {
			t3lib_div::loadTCA($table);

			// sanitize fields
			$tableFields = explode(',', $fields);
			foreach ($tableFields as $tableFieldName => $tableFieldContent) {
				$tableFields[$tableFieldName] = $GLOBALS['TYPO3_DB']->quoteStr(trim($tableFieldContent));
			}
			$fields = implode(',', $tableFields);

			// ignore deleted records by default
			$where = ($GLOBALS['TCA'][$table]['ctrl']['delete'] && ($this->modTSconfig['includeDeletedRecords'] == 0))? $GLOBALS['TCA'][$table]['ctrl']['delete'] . '=0': '';

			// get all records which are not deleted (we dont care about deleted records anymore)
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				$fields,
				$GLOBALS['TYPO3_DB']->quoteStr($table),
				$where
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				foreach ($row as $fieldName => $fieldContent) {
					if(!empty($fieldContent)) {
						// file upload
						if (($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'group') && ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['internal_type'] == 'file')) {
							// get uploadfolder
							$uploadPath = (!empty($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['uploadfolder']))? $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['uploadfolder'] . '/' : '';

							// split given csv file list
							$files = explode(',', $fieldContent);
							foreach ($files as $file) {
								$file = trim($file);
								if(!empty($file)) {
									$fileStack[] = $uploadPath . $file;
								}
							}
						}
						// filelinks in input fields or textareas
						else if(($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'input') OR ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'text')) {
							// grep for file links to mountable folders /fileadmin and /uploads
							preg_match_all('/(fileadmin|uploads)\/(\w|-|\/)+\.\w{2,4}/', $fieldContent, $files);
							$fileStack = array_merge($fileStack, $files[0]);
						}
						// analyze flexform xml
						else if($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'flex') {
							// In order to find the the flexform DS the $row needs all fields
							// which point to the location of the DS configuration
							// If the search area is limited then these fields must be considered
							// (basically all fields mentioned when searching for »ds_pointerField« in TCA)
							$flexformDS = t3lib_BEfunc::getFlexFormDS($GLOBALS['TCA'][$table]['columns'][$fieldName]['config'], $row, $table, $fieldName);
							// search DS for all relevant fields
							$imageFields = $this->getImageFields_Flex_Rec($flexformDS);
							if (!empty($imageFields)) {
								$xmlData = $fieldContent;
								$editData = t3lib_div::xml2array($xmlData);
								$result = array();
								if (is_array($editData)) {
									foreach($imageFields as $imageField) {
										// parse flexform
										// pass $fileStack per reference
										$this->parseFlexData($editData['data'], $imageField, $fileStack, 2);
									}
								}
							}
						}
					}
				}
			}
		}

		return $fileStack;
	}

	/*
	 * Find all physically existing files on filesystem
	 *
	 * @return	array	Set of filenames (with absolute path)
	 */
	function findFilesystemFiles() {
		$fileStack = array();

		// regex pattern of files to exclude (index.html filers in upload folders etc)
		$excludePattern = 'index.html';

		// get all folders which are supposed to be searched
		// $haystack = path1/,path2/
		$folderHaystack = 'uploads/, fileadmin/user_upload/';

		// crawl folders
		$folders = explode(',', $folderHaystack);
		foreach ($folders as $folder) {
			$folder = trim($folder);
			if(!empty($folder)) {
				$fileStack = t3lib_div::getAllFilesAndFoldersInPath($fileStack, PATH_site . $folder, '', FALSE, 99, $excludePattern);
			}
		}

		// traverse files and remove absolute path from each (becomes relative)
		$fileStack = t3lib_div::removePrefixPathFromList($fileStack, PATH_site);
		// select files only (discard md5 hash of path and file)
		$fileStack = array_values($fileStack);

		// exclude a set of given files
		$whitelistFiles = array(
			'uploads/index.html',
		);
		$fileStack = array_diff($fileStack, $whitelistFiles);

		return $fileStack;
	}

	/*
	 * Remove files from filesystem and queue table
	 *
	 * @param	bool		Remove selected file/files OR all files
	 *
	 * @return	string		Log of deleted files
	 */
	function clearFiles($deleteAll = 0) {
		$content = '';

		// selection
		$where = '';
		if(!$deleteAll) {
			$list = '';
			$fileUIDs = t3lib_div::_GP('fileUID');
			foreach ($fileUIDs as $fileUID) {
				$list .= ',' . intval($fileUID);
			}
			$where = 'uid IN (' . substr($list, 1) . ')';
		}

		// Select all files to delete
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid,file_path',
			'tx_orphanfiles_queue',
			$where
		);

		if($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
			while($fileToDelete = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				// delete file
				unlink(PATH_site . $fileToDelete['file_path']);

				// remove file from queue
				$GLOBALS['TYPO3_DB']->exec_DELETEquery(
					'tx_orphanfiles_queue',
					'uid=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($fileToDelete['uid'])
				);

				$content .= $fileToDelete['file_path'] . '<br />';
			}
		}

		return $content;
	}


	/*
	 * Prepare the queue tables for the next crawling process
	 *
	 * @return	int	UID of the new crawling process
	 */
	function startQueue() {
		// flush queue
		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('tx_orphanfiles_queue');

		// scrub previous crawling processes
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tx_orphanfiles_process',
			'',
			array(
				'deleted' => 1,
				'active' => 0,
			)
		);

		// add new process
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_orphanfiles_process',
			array(
				'tstamp' => time(),
				'crdate' => time(),
				'active' => 1,
			)
		);
		// get process ID
		$id = $GLOBALS['TYPO3_DB']->sql_insert_id('tx_orphanfiles_process');

		return $id;
	}

	/*
	 * Fill the queue tables with all files found and end the crawling process
	 *
	 * @param	 int		UID of the current crawling process
	 * @param	 array		Set of filenames
	 *
	 * @return	void
	 */
	function endQueue(&$processUID, &$orphanFiles) {
		// fill queue
		foreach ($orphanFiles as $orphanFile) {
			$GLOBALS['TYPO3_DB']->exec_INSERTquery(
				'tx_orphanfiles_queue',
				array(
					'crdate' => time(),
					'file_path' => $orphanFile,
				)
			);
		}

		// end crawling processes
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tx_orphanfiles_process',
			'uid=' . $processUID,
			array(
				'tstamp' => time(),
				'active' => 0,
				'filecount' => count($orphanFiles),
			)
		);
	}

	/*
	 * Crawl for all referenced and stored files
	 *
	 */
	function crawlFiles() {
		$processUID = $this->startQueue();

		$referencedFiles = $this->findReferencedFiles();
		$filesystemFiles = $this->findFilesystemFiles();

		$orphanFiles = array_diff($filesystemFiles, $referencedFiles);

		$this->endQueue($processUID, $orphanFiles);

		return $orphanFiles;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/orphanfiles/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/orphanfiles/mod1/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_orphanfiles_module1');
$SOBE->init();

// Include files?
reset($SOBE->include_once);
while(list(,$INC_FILE)=each($SOBE->include_once))	{include_once($INC_FILE);}

$SOBE->main();
$SOBE->printContent();

?>