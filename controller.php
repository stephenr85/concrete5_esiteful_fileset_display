<?php

namespace Concrete\Package\EsitefulFilesetDisplay;

use \Loader;
use Route;
use \Events;

use Package;

use Concrete\Core\Block\BlockType\BlockType;

/** 
 * This is the main controller for the package which controls the functionality like Install/Uninstall etc. 
 * 
 * @author Stephen Rushing, eSiteful 
 */ 
class Controller extends Package {

	/**
	* Protected data members for controlling the instance of the package 
	*/
	protected $pkgHandle = 'esiteful_fileset_display'; 
	protected $appVersionRequired = '8.0.1';
	protected $pkgVersion = '0.0.1';

	/**
	 * This function returns the functionality description ofthe package.
	 * 
	 * @param void 
	 * @return string $description
	 * @author Stephen Rushing, eSiteful
	 */
	public function getPackageDescription()
	{
	    return t("Custom package for File Set Display block.");
	}

	/**
	 * This function returns the name of the package.
	 * 
	 * @param void
	 * @return string $name
	 * @author Stephen Rushing, eSiteful
	 */
	public function getPackageName()
	{
	    return t("eSiteful File Set Display");
	}	


	public function on_start(){

		$this->setupAutoloader();

	}

	/**
     * Configure the autoloader
     */
    private function setupAutoloader()
    {
        if (file_exists($this->getPackagePath() . '/vendor')) {
            require_once $this->getPackagePath() . '/vendor/autoload.php';
        }
    }

	/**
	 * This function is executed during initial installation of the package.
	 * 
	 * @param void
	 * @return void
	 * @author Stephen Rushing, eSiteful
	 */
	public function install()
	{
		$this->setupAutoloader();

	    $pkg = parent::install();

	    // Install Package Items
	    $this->install_block_types($pkg);
	}

	/**
	 * This function is executed during upgrade of the package.
	 * 
	 * @param void
	 * @return void
	 * @author Stephen Rushing, eSiteful
	 */
	public function upgrade()
	{
		parent::upgrade();
		$pkg = Package::getByHandle($this->getPackageHandle());
	    // Install Package Items
	    $this->install_block_types($pkg);
	}

	/**
	 * This function is executed during uninstallation of the package.
	 * 
	 * @param void
	 * @return void
	 * @author Stephen Rushing, eSiteful
	 */
	public function uninstall()
	{
	    $pkg = parent::uninstall();
	}


	/**
	 * This function is used to install block types.
	 * 
	 * @param type $pkg
	 * @return void
	 * @author Stephen Rushing, eSiteful 
	 */
	function install_block_types($pkg)
	{
		
		$this->upsertBlockType('fileset_display', $pkg);

	}


	function upsertBlockType($btHandle, $pkg) 
	{
		$bt = BlockType::getByHandle($btHandle);
        if(!$bt) {
            $bt = BlockType::installBlockTypeFromPackage($btHandle, $pkg);
        }       
        return $bt;
	}

	
}