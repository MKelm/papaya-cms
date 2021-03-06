<?php

class PapayaContentMediaFolders extends PapayaDatabaseRecordsTree {

  protected $_fields = array(
    'id' => 'folder_id',
    'parent_id' => 'parent_id',
    'ancestors' => 'parent_path',
    'language_id' => 'lng_id',
    'title' => 'folder_name'
  );

  public function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValueFromFieldToProperty =
     array($this, 'callbackMapValueFromFieldToProperty');
    $mapping->callbacks()->onGetFieldForProperty =
     array($this, 'callbackGetFieldForProperty');
    return $mapping;
  }

  public function callbackMapValueFromFieldToProperty($context, $property, $field, $value) {
    if ($property == 'ancestors') {
      return PapayaUtilArray::decodeIdList($value);
    }
    return $value;
  }

  public function callbackGetFieldForProperty($context, $property) {
    switch ($property) {
    case 'language_id' :
    case 'title' :
      return 'ft.'.$this->_fields[$property];
    default :
      if (isset($this->_fields[$property])) {
        return 'f.'.$this->_fields[$property];
      }
    }
    return NULL;
  }

  public function load($filter = NULL, $limit = NULL, $offset = NULL) {
    $sql = "SELECT f.folder_id, f.parent_id, f.parent_path, ft.lng_id, ft.title
              FROM %s AS f
              LEFT JOIN %s AS ft ON (ft.folder_id = f.folder_id AND ft.lng_id = '%d')";
    if (isset($filter['language_id'])) {
      $languageId = (int)$filter['language_id'];
      unset($filter['language_id']);
    } else {
      $languageId = 0;
    }
    $sql .= PapayaUtilString::escapeForPrintf(
      $this->_compileCondition($filter).$this->_compileOrderBy()
    );
    $parameters = array(
      $this->getDatabaseAccess()->getTableName(PapayaContentTables::MEDIA_FOLDERS),
      $this->getDatabaseAccess()->getTableName(PapayaContentTables::MEDIA_FOLDER_TRANSLATIONS),
      $languageId
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }

}
