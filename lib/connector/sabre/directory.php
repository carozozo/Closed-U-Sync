<?php
/**
 * Directory class
 *
 * @package Sabre
 * @subpackage DAV
 * @copyright Copyright (C) 2007-2011 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class OC_Connector_Sabre_Directory extends OC_Connector_Sabre_Node implements Sabre_DAV_ICollection, Sabre_DAV_IQuota {
	private static $hideFileNameArr = array(
		'.',
		'..',
		'.usync',
		'._.DS_Store',
		'.DS_Store',
		'desktop.ini',
		'Thumbs.db'
	);
	private static $hidePathArr = array();

	/**
	 * @author Caro Huang
	 * 20130613 added
	 * Add the dir name that will not show in dav
	 * @param string $dirName
	 * @return void
	 */
	static function addHideFileName($dirName) {
		if (!in_array($dirName, self::$hideFileNameArr))
			self::$hideFileNameArr[] = $dirName;
	}

	/**
	 * @author Caro Huang
	 * 20130613 added
	 * Remove the dir name that will not show in dav
	 * @param string $dirName
	 * @return void
	 */
	static function removeHideFileName($dirName) {
		$key = array_search($dirName, self::$hideFileNameArr);
		unset(self::$hideFileNameArr[$key]);
	}

	/**
	 * @author Caro Huang
	 * 20130613 added
	 * Add the dir path  that will not show in dav
	 * @param string $dirName
	 * @return void
	 */
	static function addHidePath($path) {
		if (!in_array($path, self::$hidePathArr))
			self::$hidePathArr[] = $path;
	}

	/**
	 * @author Caro Huang
	 * 20130613 added
	 * Remove the dir path  that will not show in dav
	 * @param string $dirName
	 * @return void
	 */
	static function removeHidePath($path) {
		$key = array_search($path, self::$hidePathArr);
		unset(self::$hidePathArr[$key]);
	}

	/**
	 * Creates a new file in the directory
	 *
	 * data is a readable stream resource
	 *
	 * @param string $name Name of the file
	 * @param resource $data Initial payload
	 * @return void
	 */
	public function createFile($name, $data = null) {

		$newPath = $this -> path . '/' . $name;
		OC_Filesystem::file_put_contents($newPath, $data);

	}

	/**
	 * Creates a new subdirectory
	 *
	 * @param string $name
	 * @return void
	 */
	public function createDirectory($name) {

		$newPath = $this -> path . '/' . $name;
		OC_Filesystem::mkdir($newPath);

	}

	/**
	 * Returns a specific child node, referenced by its name
	 *
	 * @param string $name
	 * @throws Sabre_DAV_Exception_FileNotFound
	 * @return Sabre_DAV_INode
	 */
	public function getChild($name) {

		$path = $this -> path . '/' . $name;

		if (!OC_Filesystem::file_exists($path))
			throw new Sabre_DAV_Exception_FileNotFound('File with name ' . $path . ' could not be located');

		if (OC_Filesystem::is_dir($path)) {

			return new OC_Connector_Sabre_Directory($path);

		} else {

			return new OC_Connector_Sabre_File($path);

		}

	}

	/**
	 * Returns an array with all the child nodes
	 *
	 * @return Sabre_DAV_INode[]
	 */
	public function getChildren() {
		$nodes = array();
		if (OC_Filesystem::is_dir($this -> path)) {
			$dh = OC_Filesystem::opendir($this -> path);
			while (($node = readdir($dh)) !== false) {
				$nodePath = $this -> path . '/' . $node;
				// OC_Log::write($nodePath, in_array($nodePath, self::$hidePathArr), 1);
				if (!in_array($node, self::$hideFileNameArr) && !in_array($nodePath, self::$hidePathArr)) {
					$nodes[] = $this -> getChild($node);
				}
			}
		}
		return $nodes;

	}

	/**
	 * Checks if a child exists.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function childExists($name) {

		$path = $this -> path . '/' . $name;
		return OC_Filesystem::file_exists($path);

	}

	/**
	 * Deletes all files in this directory, and then itself
	 *
	 * @return void
	 */
	public function delete() {

		foreach ($this->getChildren() as $child)
			$child -> delete();
		OC_Filesystem::rmdir($this -> path);

	}

	/**
	 * Returns available diskspace information
	 *
	 * @return array
	 */
	public function getQuotaInfo() {

		return array(
			OC_Filesystem::filesize('/'),
			OC_Filesystem::free_space()
		);

	}

}
