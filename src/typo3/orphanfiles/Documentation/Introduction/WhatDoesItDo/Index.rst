What does it do?
^^^^^^^^^^^^^^^^

- This backend extension finds any file uploaded by an editor which is
  not used anymore in the CMS

- A file is »orphaned« if it is not referenced in the CMS by an upload
  field, a link in an input field nor inside of a text

- Please note that no backup is made, the files will be completely
  deleted, so be careful and *use this extension at your own risk*

- Please have in mind that TYPO3 is creating a copy of a file for most
  file uploads

  - The copy in »/uploads/« (a system folder, which is not selectable by
    an editor) will remain if it's referenced in the CMS, but the original
    file in the filestorage in »/fileadmin/user\_upload/« will be marked
    as orphaned
