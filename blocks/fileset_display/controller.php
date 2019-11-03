<?php 
namespace Concrete\Package\EsitefulFilesetDisplay\Block\FilesetDisplay;

use Concrete\Core\Block\BlockController;
use Loader;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\File\FileList;
use Concrete\Core\File\Search\ColumnSet\Available as FileSearchColumnSetAvailable;
use Concrete\Core\Attribute\Key\FileKey as FileAttributeKey;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends BlockController {
	
	protected $btTable = 'btFileSetDisplay';
	protected $btInterfaceWidth = "500";
	protected $btInterfaceHeight = "470";
	protected $btWrapperClass = 'ccm-ui';
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = true;
	protected $btDefaultSet = '';

	public function getBlockTypeName() {
		return t("File Set Display");
	}
	
	public function getBlockTypeDescription() {
		return t("Block for displaying files in a file set.");
	}

	public function getSearchableContent(){
		// return $this->alertText;
	}

	public function validate($args) {
		$error = Loader::helper('validation/error');
		//if (trim($args['alertText']) == '') {
		//	$error->add(t('Alert Text Required'));
		//}		
		if($error->has()) {
			return $error;
		}
	}
		
	public function add() {
		

	}
	
	public function edit() {
		

	}

	public function view() {
		$fs = $this->getFileSet();
		if(!is_object($fs)) {
			$this->set('files', []);
		}

		$this->set('files', $this->getFiles());
	}	
	
	public function save($args){
		foreach($args as $key=>$value) {
			if($value === ''){
				$args[$key] = null;
			}
		}

		if($args['sortBy'] == ''){
			//$data['sortBy'] = NULL;
		}
		if($args['sortOrder'] == ''){
			//$data['sortOrder'] = NULL;
		}
		//Clean the file extensions list of spaces and periods
		if(!empty($args['fExts'])){
			$args['fExts'] = preg_replace('/\s*,\s*/',',', trim($args['fExts']));
			$args['fExts'] = str_replace('.','', $args['fExts']);
		}
		
		parent::save($args);
	}

	function getFileSet(){
		return $fs = FileSet::getByID($this->fsID);
	}
	
	function getFileSetName(){
		$fs = $this->getFileSet();
		if(is_object($fs)){
			return $fs->getFileSetName();	
		}
	}
	
	function getFileList(){
		Loader::model('file_list');				
		Loader::model('file_set');
		$fileList = new FileList();

		$fs = $this->getFileSet();
		if(is_object($fs)) {
			$fileList->filterBySet($fs);
		}		

		return $fileList;
	}
	
	function getFiles($sortBy=NULL, $sortOrder=NULL, $max=NULL, $offset=0){			
		if (intval($this->fsID) < 1) {
			return FALSE;
		}
		$fileSet = $this->getFileSet();
		$fileList = $this->getFileList();
		//$fileList->debug();
		//Filter the extensions
		$exts = $this->getFileExtensions();

		if(is_array($exts)){
			foreach($exts as $ext){
				$fileList->filterByExtension($ext);	
			}
		}
				
		//All that sorting
		if(empty($sortBy)){
			$sortBy = $this->sortBy;				
		}
		
		if(!empty($sortBy) && is_numeric($sortBy)){				
			Loader::model('file_attributes');
			//assume it's an attribute key ID
			$sortBy = intval($sortBy);
			$attr = FileAttributeKey::getByID($sortBy);
			$sortBy = 'ak_'.$attr->getKeyHandle();
		}			
		
		if(empty($sortOrder)){
			$sortOrder = !empty($this->sortOrder) ? $this->sortOrder : 'asc';	
		}
		
		if(is_object($fileSet) && empty($sortBy)){
			//Sort by display order
			$fileList->sortByFileSetDisplayOrder();
		}else if(is_array($sortBy)){
			//Multi sort
			call_user_func_array(array($fileList, 'sortByMultiple', $sortBy));				
		}else if(is_string($sortBy)){
			//Regular ol' sort
			$fileList->sortBy($sortBy, $sortOrder);
		}
		
		$limit = is_null($max) ? $this->max : $max;
		if(empty($limit)) $limit = 9999;
		
		//$this->pre($fileList->getQuery());
		$files = $fileList->get($limit, $offset);
		
		return $this->files = $files;
	}
	
	
	function getAvailableFileSets(){
		$sets = FileSet::getMySets();
		$options = array();
		foreach ($sets as $set){
			$options[$set->getFileSetID()] = $set->getFileSetName();
		}
		return $options;
	}
	
	
	function getAvailableSortColumns(){
		
		$fscs = new FileSearchColumnSetAvailable();
		$cols = $fscs->getColumns();
		
		$options = array(''=>'Auto');
		foreach($cols as $col){
			$options[$col->getColumnKey()] = $col->getColumnName();
		}
		
		$attrs = FileAttributeKey::getList();
		foreach($attrs as $attr){
			$options['ak_'.$attr->getAttributeKeyHandle()] = $attr->getAttributeKeyName();
		}
		
		return $options;		
	}
	
	function getAvailableSortOrders(){
		$orders = array(
			''=>t('Auto'),
			'asc'=>t('Ascending'),
			'desc'=>t('Descending')
		);
		return $orders;
	}
	
	function getLinkTarget(){
		if(!empty($this->linkTarget)){
			return $this->linkTarget;	
		}
		return '_blank';
	}
	
	function getFileExtensions(){
		if(!empty($this->fExts)){
			return explode(',', $this->fExts);	
		}
		return NULL;	
	}
}
?>