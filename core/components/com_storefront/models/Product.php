<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Storefront\Models;

use Components\Storefront\Models\Course;
use Components\Storefront\Models\Warehouse;
use Exception;
use Filesystem;
use Component;
use Lang;

require_once __DIR__ . DS . 'Warehouse.php';

/**
 * Storefront product class
 */
class Product
{
	// Product data container
	var $data;
	// SKUs container
	var $skus;

	/**
	 * Constructor
	 *
	 * @param   int   $pId  Product ID
	 * @return  void
	 */
	public function __construct($pId = false)
	{
		// Load language file
		\App::get('language')->load('com_storefront');

		$this->data = new \stdClass();

		if (isset($pId) && is_numeric($pId) && $pId)
		{
			$this->setId($pId);
			$this->load();
		}
	}

	/**
	 * Load existing product
	 *
	 * @throws  Exception
	 * @return  void       Throws exception if product cannot be loaded
	 */
	private function load()
	{
		$db = \App::get('db');
		$pId = $this->getId();

		//$warehouse = new Warehouse();
		//$productInfo = $warehouse->getProductInfo($pId, true);

		$sql = 	"SELECT p.*,
				IF((p.`publish_up` IS null OR p.`publish_up` = '0000-00-00 00:00:00' OR p.`publish_up` <= NOW())
				AND (p.`publish_down` IS null OR p.`publish_down` = '0000-00-00 00:00:00' OR p.`publish_down` > NOW()), 1, 0) AS pPublishedNow,
				pt.ptName, pt.ptModel";
		$sql .= " FROM `#__storefront_products` p
				LEFT JOIN `#__storefront_product_types` pt ON pt.ptId = p.ptId
				WHERE p.`pId` = " . $db->quote($pId);

		$db->setQuery($sql);
		$productInfo = $db->loadObject();

		if (!$productInfo)
		{
			throw new \Exception(Lang::txt('Error loading product'));
		}

		$this->setType($productInfo->ptId);
		$this->setTypeInfo($productInfo->ptId, $productInfo->ptName, $productInfo->ptModel);
		$this->setName($productInfo->pName);
		if (!empty($productInfo->pAlias))
		{
			$this->setAlias($productInfo->pAlias);
		}
		$this->setDescription($productInfo->pDescription);
		$this->setFeatures($productInfo->pFeatures);
		$this->setTagline($productInfo->pTagline);
		$this->setActiveStatus($productInfo->pActive);
		$this->setAccessLevel($productInfo->access);
		$this->setAllowMultiple($productInfo->pAllowMultiple);
		$this->setPublishTime($productInfo->publish_up, $productInfo->publish_down, $productInfo->pPublishedNow);
	}

	/**
	 * Set product type
	 *
	 * @param   string     $productType  Product type
	 * @throws  Exception
	 * @return  bool       Throws exception if the produt type is bad
	 */
	public function setType($productType)
	{
		if (is_numeric($productType))
		{
			$this->data->type = $productType;
			return true;
		}

		switch (strtolower($productType))
		{
			case 'course':
				$this->data->type = 20;
				return true;
				break;
			case 'product':
				$this->data->type = 1;
				return true;
				break;
		}

		throw new \Exception(Lang::txt('COM_STOREFRONT_INVALID_PRODUCT_TYPE'));
	}

	/**
	 * Get product type
	 *
	 * @return  mixed  Product type (int) or false
	 */
	public function getType()
	{
		if (empty($this->data->type))
		{
			return false;
		}
		return $this->data->type;
	}

	/**
	 * Set product type info
	 *
	 * @param   int     $ptId     Product type ID
	 * @param   string  $ptName   Product type name
	 * @param   string  $ptModel  Product type model
	 * @return  void
	 */
	private function setTypeInfo($ptId, $ptName, $ptModel)
	{
		$productTypeInfo = new \stdClass();
		$productTypeInfo->id = $ptId;
		$productTypeInfo->name = $ptName;
		$productTypeInfo->model = $ptModel;

		$this->data->typeInfo = $productTypeInfo;
	}

	/**
	 * Get product type info
	 *
	 * @return  mixed  Product type info (obj) or false
	 */
	public function getTypeInfo()
	{
		if (empty($this->data->typeInfo))
		{
			return false;
		}
		return $this->data->typeInfo;
	}

	/**
	 * Get product access groups
	 *
	 * @param   string  $type
	 * @return  array
	 */
	public function getAccessGroups($type = 'include')
	{
		$id = (int)$this->getId();

		if (!$id)
		{
			return array();
		}

		$type = ($type == 'include' ? 0 : 1);

		$db = \App::get('db');
		$db->setQuery("SELECT * FROM `#__storefront_product_access_groups` WHERE `pId`=" . $db->quote($id) . " AND `exclude`=" . $db->quote($type));

		$accessgroups = array();
		foreach ($db->loadObjectList() as $row)
		{
			$accessgroups[] = $row->agId;
		}

		return $accessgroups;
	}

	/**
	 * Set product access groups
	 *
	 * @param   array   $groups
	 * @param   string  $type
	 * @return  boolean
	 */
	public function setAccessGroups($groups = array(), $type = 'include')
	{
		if (!is_array($groups))
		{
			$groups = array($groups);
		}

		$id = (int)$this->getId();

		if (!$id)
		{
			return true;
		}

		$groups = array_map('intval', $groups);

		$db = \App::get('db');

		// Get the previous list of groups
		$prev = $this->getAccessGroups($type);

		if (empty($prev) && empty($groups))
		{
			// Nothing to change
			return true;
		}

		$type = ($type == 'include' ? 0 : 1);

		foreach ($prev as $group)
		{
			// Clear old record
			if (!in_array($group, $groups))
			{
				$db->setQuery("DELETE FROM `#__storefront_product_access_groups` WHERE `pId`=" . $db->quote($id) . " AND `agId`=" . $db->quote($group) . " AND `exclude`=" . $db->quote($type));
				if (!$db->query())
				{
					return false;
				}
			}
		}

		foreach ($groups as $group)
		{
			if (in_array($group, $prev))
			{
				// Record already exists
				continue;
			}

			// Insert new record
			$db->setQuery("INSERT INTO `#__storefront_product_access_groups` (`id`, `pId`, `agId`, `exclude`) VALUES (null," . $db->quote($id) .  "," . $db->quote($group) . "," . $db->quote($type) . ")");
			if (!$db->query())
			{
				return false;
			}
		}

		return true;
	}

	/* ****************************** Collections ******************************* */

	/**
	 * Get product collections
	 *
	 * @return  array  collection IDs
	 */
	public function getCollections()
	{
		if (!isset($this->data->collections))
		{
			if ($this->getId())
			{
				$db = \App::get('db');
				$sql = "SELECT `cId` FROM `#__storefront_product_collections` WHERE `pId` = " . $db->quote($this->getId());
				$db->setQuery($sql);
				$collections = $db->loadColumn();
				$this->setCollections($collections);
			}
			else
			{
				return array();
			}
		}
		return $this->data->collections;
	}

	/**
	 * Set product collections
	 *
	 * @param   array  $collections  collection IDs
	 * @return  void
	 */
	public function setCollections($collections)
	{
		$this->data->collections = array_unique($collections);
	}

	/**
	 * Add product to collection
	 *
	 * @param   int   $cId  collection ID
	 * @return  bool
	 */
	public function addToCollection($cId)
	{
		$collections = $this->getCollections();
		$collections[] = $cId;
		$this->setCollections($collections);
		return true;
	}

	/* ****************************** SKUs ******************************* */

	/**
	 * Get product skus
	 *
	 * @return  array  product SKUs
	 */
	public function getSkus()
	{
		if (!isset($this->skus))
		{
			if ($this->getId())
			{
				$db = \App::get('db');
				$sql = "SELECT `sId` FROM `#__storefront_skus` WHERE `pId` = " . $db->quote($this->getId());
				$db->setQuery($sql);
				$db->execute();

				$skuIds = $db->loadColumn();
				$skus = array();

				// Find out product type to instantiate the correct object
				// software
				if ($this->getTypeInfo() && $this->getTypeInfo()->name == 'Software Download')
				{
					require_once __DIR__ . DS . 'SoftwareSku.php';
					$instanceName = '\Components\Storefront\Models\SoftwareSku';
				}
				else
				{
					require_once __DIR__ . DS . 'Sku.php';
					$instanceName = '\Components\Storefront\Models\Sku';
				}

				foreach ($skuIds as $sId)
				{
					$sku = new $instanceName($sId);
					$skus[] = $sku;
				}
				$this->setSkus($skus, false);
			}
			else
			{
				return array();
			}
		}
		return $this->skus;
	}

	/**
	 * Removes all old SKUs sets given SKUs
	 *
	 * @param   array  $skus
	 * @param   bool   $deleteOld
	 * @return  void
	 */
	private function setSkus($skus, $deleteOld = true)
	{
		// Delete all skus that are not part of the new SKUs
		if ($deleteOld)
		{
			$newSkuIds = array();
			foreach ($skus as $sku)
			{
				$newSkuIds[] = $sku->getId();
				$sku->setProductId($this->getId());
			}

			$oldSkus = $this->getSkus();
			foreach ($oldSkus as $oldSku)
			{
				if (!in_array($oldSku->getId(), $newSkuIds))
				{
					$oldSku->delete();
				}
			}
		}
		$this->skus = $skus;
	}

	/**
	 * Sets a new SKU for the product, used by single SKU products, removes all other SKUs from the product
	 *
	 * @param   object  $sku  StorefrontModelSku
	 * @return  void
	 * @throws  Exception
	 */
	protected function setSku($sku)
	{
		if (!($sku instanceof Sku))
		{
			throw new \Exception(Lang::txt('Bad SKU. Unable to add.'));
		}

		$this->setSkus(array($sku));
	}

	/**
	 * Add a SKU for the product
	 *
	 * @param   object  $sku  StorefrontModelSku
	 * @return  void
	 * @throws  Exception
	 */
	public function addSku($sku)
	{
		if (!($sku instanceof Sku))
		{
			throw new \Exception(Lang::txt('Bad SKU. Unable to add.'));
		}

		// Set SKU product ID to this product
		$sku->setProductId($this->getId());
		$sku->save();

		$skus = $this->getSkus();
		$skus[] = $sku;
		$this->setSkus($skus);
	}

	/**
	 * Set product id
	 *
	 * @param   int   $pId  product ID
	 * @return  bool
	 */
	public function setId($pId)
	{
		$this->data->id = $pId;
		return true;
	}

	/**
	 * Get product id (if set)
	 *
	 * @return  mixed  product ID or false
	 */
	public function getId()
	{
		if (!empty($this->data->id))
		{
			return $this->data->id;
		}
		return false;
	}

	/**
	 * Set product name
	 *
	 * @param   string  $productName  Product name
	 * @return  bool
	 */
	public function setName($productName)
	{
		$this->data->name = $productName;
		return true;
	}

	/**
	 * Get product name
	 *
	 * @return  mixed  Product name or false
	 */
	public function getName()
	{
		if (empty($this->data->name))
		{
			return false;
		}
		return $this->data->name;
	}

	/**
	 * Set product alias
	 *
	 * @param   string  $pAlias  Product alias
	 * @return  bool
	 * @throws  Exception
	 */
	public function setAlias($pAlias)
	{
		// Check if the alias is valid
		$badAliasException = new \Exception('Bad product alias. Alias should be a non-empty non-numeric alphanumeric string.');
		if (preg_match("/^[0-9a-zA-Z]+[\-_0-9a-zA-Z]*$/i", $pAlias))
		{
			if (is_numeric($pAlias))
			{
				throw $badAliasException;
			}
			$this->data->alias = $pAlias;
			return true;
		}
		throw $badAliasException;
	}

	/**
	 * Get product alias
	 *
	 * @return  mixed  Product alias or false
	 */
	public function getAlias()
	{
		if (empty($this->data->alias))
		{
			return false;
		}
		return $this->data->alias;
	}

	/**
	 * Set product description
	 *
	 * @param  string  $productDescription  Product description
	 * @return  bool
	 */
	public function setDescription($productDescription)
	{
		$this->data->description = $productDescription;
		return true;
	}

	/**
	 * Get product description
	 *
	 * @return  mixed  Product description or false
	 */
	public function getDescription()
	{
		if (empty($this->data->description))
		{
			return false;
		}
		return $this->data->description;
	}

	/**
	 * Set product features
	 *
	 * @param   string  $productFeatures  Product Features
	 * @return  bool
	 */
	public function setFeatures($productFeatures)
	{
		$this->data->features = $productFeatures;
		return true;
	}

	/**
	 * Get product Features
	 *
	 * @return  mixed  Product Features or false
	 */
	public function getFeatures()
	{
		if (empty($this->data->features))
		{
			return false;
		}
		return $this->data->features;
	}

	/* ****************************** Images ******************************* */

	/**
	 * Get product images
	 *
	 * @param   bool   $forceReload
	 * @return  array  Product images
	 */
	public function getImages($forceReload = false)
	{
		if (!isset($this->data->images) || $forceReload)
		{
			if ($this->getId())
			{
				// Get product image(s)
				$db = \App::get('db');
				$sql = "SELECT imgId, imgName FROM `#__storefront_images`
				WHERE `imgObject` = 'product'
				AND `imgObjectId` = " . $db->quote($this->getId()) . "
				ORDER BY `imgPrimary` DESC";
				$db->setQuery($sql);
				$images = $db->loadObjectList();
				$this->setImages($images);
			}
			else
			{
				return array();
			}
		}
		return $this->data->images;
	}

	/**
	 * Get primary image
	 *
	 * @param	void
	 * @return	obj		image info
	 */
	public function getImage()
	{
		$images = $this->getImages();

		return (empty($images) ? null : $images[0]);
	}

	/**
	 * Set product images
	 *
	 * @param   array  $img  Product images
	 * @return  bool
	 */
	public function setImages($img)
	{
		$this->data->images = $img;
		return true;
	}

	/**
	 * Set product image
	 *
	 * @param   string  $img  Product image
	 * @return  bool
	 */
	public function setImage($img)
	{
		$image = new \stdClass();
		$image->imgName = $img;
		$this->setImages(array($image));
		return true;
	}

	/**
	 * Add product images
	 *
	 * @param   array  $img  Product images
	 * @return  bool
	 */
	public function addImages($img)
	{
		$this->setImages(array_merge($this->getImages(), $img));
		return true;
	}

	/**
	 * Add primary image (the first image in the array)
	 *
	 * @param   string  $img  Product image name
	 * @return  bool
	 */
	public function addImage($img)
	{
		$images = $this->getImages();
		if (!empty($images[0]))
		{
			// Move the current primary image to the end
			$images[] = $images[0];
		}
		$primaryImage = new \stdClass();
		$primaryImage->imgName = $img;
		// Set the new image as primary
		$images[0] = $primaryImage;
		return $this->setImages($images);
	}

	/**
	 * Remove image
	 *
	 * @param   int   $imgId  Image ID
	 * @return  bool  Succes of Failure
	 */
	public function removeImage($imgId)
	{
		$images = $this->getImages();
		if (empty($images))
		{
			return false;
		}

		foreach ($images as $key => $img)
		{
			if ($imgId == $img->imgId)
			{
				unset($this->data->images[$key]);

				// Remove the actual file
				$config = Component::params('com_storefront');
				$imgWebPath = trim($config->get('imagesFolder', '/site/storefront/products'), DS);
				$path = PATH_APP . DS . $imgWebPath . DS . $this->getId();

				Filesystem::delete($path . DS . $img->imgName);
				return true;
			}
		}
	}

	/**
	 * Set product tagline
	 *
	 * @param   string  $productTagline  Product tagline
	 * @return  bool
	 */
	public function setTagline($productTagline)
	{
		$this->data->tagline = $productTagline;
		return true;
	}

	/**
	 * Get product tagline
	 *
	 * @return  mixed  Product tagline
	 */
	public function getTagline()
	{
		if (empty($this->data->tagline))
		{
			return '';
		}
		return $this->data->tagline;
	}

	/**
	 * Set product multiple flag
	 *
	 * @param   int   $productAllowMiltiple  Allow multiple
	 * @return  bool
	 */
	public function setAllowMultiple($productAllowMiltiple)
	{
		$this->data->allowMiltiple = $productAllowMiltiple;
		return true;
	}

	/**
	 * Get product multiple flag
	 *
	 * @return  mixed
	 */
	public function getAllowMultiple()
	{
		if (empty($this->data->allowMiltiple))
		{
			return null;
		}
		return $this->data->allowMiltiple;
	}

	/**
	 * Set publishing times
	 *
	 * @param   string  $publishUp      publish up time
	 * @param   string  $publishDown    publish down time
	 * @param   bool    $pPublishedNow
	 * @return  bool
	 */
	public function setPublishTime($publishUp = '', $publishDown = '', $pPublishedNow = false)
	{
		$this->data->publishTime = new \stdClass();
		if (empty($publishUp))
		{
			$publishUp = '0000-00-00 00:00:00';
		}
		$this->data->publishTime->publish_up = $publishUp;
		if (empty($publishDown))
		{
			$publishDown = '0000-00-00 00:00:00';
		}
		$this->data->publishTime->publish_down = $publishDown;

		$this->data->publishTime->publishedNow = $pPublishedNow;

		return true;
	}

	/**
	 * Get publishing times
	 *
	 * @return  string
	 */
	public function getPublishTime()
	{
		if (empty($this->data->publishTime))
		{
			$this->setPublishTime();
		}
		return $this->data->publishTime;
	}

	/**
	 * Set product access level
	 *
	 * @param   int   $accessLevel  Product access level
	 * @return  bool
	 */
	public function setAccessLevel($accessLevel)
	{
		$this->data->accessLevel = $accessLevel;
		return true;
	}

	/**
	 * Get product access level
	 *
	 * @return  int  Product access level
	 */
	public function getAccessLevel()
	{
		if (empty($this->data->accessLevel))
		{
			return 0;
		}
		return $this->data->accessLevel;
	}

	/**
	 * Set product active status
	 *
	 * @param   int   $activeStatus  Product status
	 * @return  bool
	 */
	public function setActiveStatus($activeStatus)
	{
		// This is to accommodate admin's 'trashed' value
		// TODO redo it properly to allow trashing
		if ($activeStatus == 2)
		{
			$this->data->activeStatus = 0;
		}
		if ($activeStatus)
		{
			$this->data->activeStatus = 1;
		}
		else
		{
			$this->data->activeStatus = 0;
		}
		return true;
	}

	/* ****************************** Option groups ******************************* */

	/**
	 * Get option groups
	 *
	 * @return  array
	 */
	public function getOptionGroups()
	{
		if (!isset($this->data->optionGroups))
		{
			if ($this->getId())
			{
				$db = \App::get('db');

				$sql = "SELECT ogId
						FROM `#__storefront_product_option_groups` pog
						WHERE pId = " . $this->getId();

				$db->setQuery($sql);
				$db->execute();
				$res = $db->loadColumn();
				$this->setOptionGroups($res);
			}
			else
			{
				return array();
			}
		}
		return $this->data->optionGroups;
	}

	/**
	 * Set option groups
	 *
	 * @param   array  $ogIds
	 * @return  bool
	 */
	public function setOptionGroups($ogIds)
	{
		$this->data->optionGroups = array_unique($ogIds);
		return true;
	}

	/**
	 * Add option group
	 *
	 * @param   int   $ogId  option group ID
	 * @return  bool
	 */
	public function addOptionGroup($ogId)
	{
		$optionGroups = $this->getOptionGroups();
		$optionGroups[] = $ogId;
		$this->setOptionGroups($optionGroups);
		return true;
	}

	/**
	 * Get product active status
	 *
	 * @return  string  Product status
	 */
	public function getActiveStatus()
	{
		if (!isset($this->data->activeStatus))
		{
			return 'DEFAULT';
		}
		return $this->data->activeStatus;
	}

	/**
	 * Check if everything checks out and the product is ready to go
	 *
	 * @throws  Exception
	 * @return  bool
	 */
	public function verify()
	{
		if (empty($this->data->name))
		{
			throw new \Exception(Lang::txt('No product name set'));
		}

		// Check if the alias is used by another product (only if gets published, allow duplicates for unpublished items)
		$warehouse = new Warehouse();
		if ($this->getAlias() && $this->getActiveStatus())
		{
			$otherProduct = $warehouse->checkProduct($this->getAlias(), true);
			if ($otherProduct->status > 0 && $otherProduct->pId != $this->getId())
			{
				throw new \Exception(Lang::txt('There is another product with the same alias. Alias cannot be set.'));
			}
		}

		return true;
	}

	/**
	 * Save product
	 *
	 * @return  bool
	 */
	public function save()
	{
		$this->verify();
		$pId = $this->getId();

		if ($pId)
		{
			$sql = "UPDATE `#__storefront_products` SET ";
		}
		else
		{
			$sql = "INSERT INTO `#__storefront_products` SET ";
		}

		$db = \App::get('db');

		$sql .= "
				`ptId` = " . $db->quote($this->getType()) . ",
				`pName` = " . $db->quote($this->getName()) . ",
				`pAlias` = " . $db->quote($this->getAlias()) . ",
				`pTagline` = " . $db->quote($this->getTagline()) . ",
				`pDescription` = " . $db->quote($this->getDescription()) . ",
				`pFeatures` = " . $db->quote($this->getFeatures()) . ",
				`pAllowMultiple` = " . $db->quote($this->getAllowMultiple()) . ",
				`pActive` = " . $db->quote($this->getActiveStatus()) . ",
				`publish_up` = " . $db->quote($this->getPublishTime()->publish_up) . ",
				`publish_down` = " . $db->quote($this->getPublishTime()->publish_down) . ",
				`access` = " . $db->quote($this->getAccessLevel());

		// Set pId if needed if adding new product
		if (!$pId)
		{
			$sql .= ",
				`pId` = " . $db->quote($pId);
		}
		else
		{
			$sql .= " WHERE `pId` = " . $db->quote($pId);
		}

		$db->setQuery($sql);
		$db->query();
		if (!$pId)
		{
			$pId = $db->insertid();
			$this->setId($pId);
		}

		// ### Do collections
		$collections = $this->getCollections();

		$affectedCollectionIds = array();
		if (!empty($collections))
		{
			foreach ($collections as $cId)
			{
				$sql = "SET @collectionId := 0";
				$db->setQuery($sql);
				$db->query();

				$sql = "INSERT INTO `#__storefront_product_collections` SET
						`cId` = " . $db->quote($cId) . ",
						`pId` = " . $db->quote($pId) . "
						ON DUPLICATE KEY UPDATE
						`pcId` = (@collectionId := `pcId`),
						`cId` = " . $db->quote($cId) . ",
						`pId` = " . $db->quote($pId);

				$db->setQuery($sql);
				$db->query();

				$sql = "SELECT IF(@collectionId = 0, LAST_INSERT_ID(), @collectionId)";
				$db->setQuery($sql);
				$db->query();

				$affectedCollectionIds[] = $db->loadResult();
			}
		}

		// Delete unused collections
		$deleteSql = '(0';
		foreach ($affectedCollectionIds as $activeCllId)
		{
			$deleteSql .= ", " . $db->quote($activeCllId);
		}
		$deleteSql .= ')';
		$sql = "DELETE FROM `#__storefront_product_collections` WHERE `pId` = " . $db->quote($pId) . " AND `pcId` NOT IN {$deleteSql}";
		$db->setQuery($sql);
		$db->query();

		// ### Do images
		$images = $this->getImages();

		// First delete all old references
		$sql = "DELETE FROM `#__storefront_images` WHERE `imgObject` = 'product' AND `imgObjectId` = " . $db->quote($pId);
		$db->setQuery($sql);
		$db->query();

		if (!empty($images))
		{
			$firstImage = true;
			foreach ($images as $key => $img)
			{
				$primary = 0;
				if ($firstImage)
				{
					$primary = 1;
					$firstImage = false;
				}
				$sql = "INSERT INTO `#__storefront_images` SET
						`imgName` = " . $db->quote($img->imgName) . ",
						`imgObject` = 'product',
						`imgObjectId` = " . $db->quote($pId) . ",
						`imgPrimary` = " . $primary;
				$db->setQuery($sql);
				$db->query();
			}

			// Refresh object's images info to get the latest image IDs
			$this->getImages(true);
		}

		// ### Do option groups
		$optionGroups = $this->getOptionGroups();

		// First erase all old option groups
		$sql = "DELETE FROM `#__storefront_product_option_groups` WHERE `pId` = " . $db->quote($pId);
		$db->setQuery($sql);
		$db->query();

		foreach ($optionGroups as $ogId)
		{
			$sql = "INSERT INTO `#__storefront_product_option_groups` (pId, ogId)
					VALUES (" . $db->quote($pId) . ", " . $db->quote($ogId) . ")";
			$db->setQuery($sql);
			$db->execute();
		}

		// Finally, since the product updates can potentially affect other elements of the storefront, update dependencies
		$this->updateDependencies();

		return true;
	}

	/**
	 * Delete the product
	 *
	 * @return  void
	 */
	public function delete()
	{
		$db = \App::get('db');

		// First get all SKUs to delete later
		$skus = $this->getSkus();

		// Delete product record
		$sql = 'DELETE FROM `#__storefront_products` WHERE `pId` = ' . $db->quote($this->getId());
		$db->setQuery($sql);
		$db->query();

		// Delete product-related files (product image)
		$config = Component::params('com_storefront');
		$imgWebPath = trim($config->get('imagesFolder', '/site/storefront/products'), DS);
		$dir = PATH_APP . DS . $imgWebPath . DS . $this->getId();

		if (file_exists($dir))
		{
			$it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($files as $file)
			{
				if ($file->isDir())
				{
					rmdir($file->getRealPath());
				}
				else
				{
					unlink($file->getRealPath());
				}
			}
			rmdir($dir);
		}

		// Delete images from database
		$sql = "DELETE FROM `#__storefront_images` WHERE `imgObject` = 'product' AND `imgObjectId` = " . $db->quote($this->getId());
		$db->setQuery($sql);
		$db->query();

		// Delete all SKUs
		foreach ($skus as $sku)
		{
			$sku->delete();
		}

		// Delete product-collection relations
		$sql = 'DELETE FROM `#__storefront_product_collections` WHERE `pId` = ' . $db->quote($this->getId());
		$db->setQuery($sql);
		$db->query();

		// Delete product meta
		$sql = 'DELETE FROM `#__storefront_product_meta` WHERE `pId` = ' . $db->quote($this->getId());
		$db->setQuery($sql);
		$db->query();

		// Delete prduct-option groups relations
		$sql = 'DELETE FROM `#__storefront_product_option_groups` WHERE `pId` = ' . $db->quote($this->getId());
		$db->setQuery($sql);
		$db->query();

		// Delete product access groups relations
		$sql = "DELETE FROM `#__storefront_product_access_groups` WHERE `pId` = " . $db->quote($this->getId());
		$db->setQuery($sql);
		$db->query();
	}

	/**
	 * Update dependencies
	 *
	 * @return  void
	 */
	private function updateDependencies()
	{
		// Update SKUs' references for this product first
		Sku::updateReferences($this->getId());

		// Check all active product SKUs and disable those that do not verify anymore
		$skus = $this->getSkus();
		$skusDisabled = false;
		foreach ($skus as $sku)
		{
			if ($sku->getActiveStatus())
			{
				try
				{
					$sku->verify();
				}
				catch (\Exception $e)
				{
					$sku->unPublish();
					$skusDisabled = true;
				}
			}
		}

		if ($skusDisabled)
		{
			$this->addMessage('Some SKUs were unpublished because of the recent update. Check each SKU to fix the issues.');
		}
	}

	/**
	 * Add a message
	 *
	 * @param   string  $msg
	 * @return  void
	 */
	private function addMessage($msg)
	{
		$this->data->messages[] = $msg;
	}

	/**
	 * Get messages
	 *
	 * @return  mixed
	 */
	public function getMessages()
	{
		if (empty($this->data->messages))
		{
			return false;
		}
		return $this->data->messages;
	}

	/**
	 * Set product meta
	 *
	 * @param   array  $meta
	 * @return  void
	 */
	public function setMeta($meta)
	{
		$db = \App::get('db');

		foreach ($meta as $key => $val)
		{
			$sql  = "	INSERT INTO `#__storefront_product_meta` (`pmKey`, `pmValue`, `pId`)
						VALUES (" . $db->quote($key) . ", " . $db->quote($val) . ", " . $db->quote($this->getId()) . ")
						ON DUPLICATE KEY UPDATE `pmValue` = " . $db->quote($val);
			$db->setQuery($sql);
			$db->query();
		}
	}

	/**
	 * Get product meta
	 *
	 * @return  object
	 */
	public function getMeta()
	{
		$db = \App::get('db');

		$sql  = 'SELECT `pmKey`, `pmValue` FROM `#__storefront_product_meta` WHERE `pId` = ' . $db->quote($this->getId());
		$db->setQuery($sql);
		$meta = $db->loadObjectList('pmKey');

		$metaObj = new \stdClass();
		foreach ($meta as $key => $m)
		{
			$metaObj->$key = $m->pmValue;
		}
		return $metaObj;
	}

	/* ************************************* Static functions ***************************************************/

	/**
	 * Get product meta value by name
	 *
	 * @param   integer  $pId      Product ID
	 * @param   string   $metaKey  Meta key to get a certain value
	 * @return  mixed    Meta value
	 */
	public static function getMetaValue($pId, $metaKey)
	{
		$db = \App::get('db');

		$sql  = 'SELECT ';
		if (!$metaKey)
		{
			$sql .= '`pmKey`, ';
		}
		$sql .= '`pmValue` FROM `#__storefront_product_meta` WHERE `pId` = ' . $db->quote($pId);
		$sql .= ' AND `pmKey` = ' . $db->quote($metaKey);
		$db->setQuery($sql);
		$meta = $db->loadResult();
		return $meta;
	}

	/**
	 * Get product instance
	 *
	 * @param   integer  $pId  Product ID
	 * @return  mixed    Object if Product is found, false if not
	 */
	public static function getInstance($pId)
	{
		$db = \App::get('db');

		// Get product type first
		$sql = "SELECT pt.ptName, pt.ptId FROM `#__storefront_products` p
				LEFT JOIN `#__storefront_product_types` pt ON pt.ptId = p.ptId
				WHERE p.`pId` = " . $db->quote($pId);

		$db->setQuery($sql);
		$db->execute();
		$productTypeInfo = $db->loadObject();

		// No product found
		if (!$productTypeInfo)
		{
			return false;
		}

		if ($productTypeInfo->ptName == 'Course')
		{
			$product = new Course($pId);
		}
		else
		{
			$product = new Product($pId);
		}

		return $product;
	}
}
