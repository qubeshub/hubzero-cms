<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Storefront\Site\Controllers;

use Components\Storefront\Models\Warehouse;
use Components\Storefront\Models\Product as P;
use Components\Cart\Models\CurrentCart;
use Components\Cart\Helpers\Audit;
use Exception;
use Request;
use Pathway;
use Lang;
use User;
use App;

require_once \Component::path('com_cart') . DS . 'models' . DS . 'CurrentCart.php';
require_once \Component::path('com_cart') . DS . 'helpers' . DS . 'Audit.php';


/**
 * Product viewing controller class
 */
class Product extends \Hubzero\Component\SiteController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->warehouse = new Warehouse();
		$this->warehouse->addAccessLevels(User::getAuthorisedViewLevels());
		$this->warehouse->addAccessGroups(User::getAuthorisedGroups());
		if (is_numeric(User::get('id')))
		{
			$this->warehouse->addUserScope(User::get('id'));
		}
		parent::execute();
	}

	/**
	 * Display product
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		$productIdentifier = Request::getString('product', '');
		$pInfo = $this->warehouse->checkProduct($productIdentifier);

		if (!$pInfo->status)
		{
			App::abort($pInfo->errorCode, Lang::txt($pInfo->message));
		}
		$pId = $pInfo->pId;

		$p = new P($pId);
		$meta = $p->getMeta();

		$this->view->pId = $pId;
		$this->view->meta = $meta;
		$this->view->css();
		$this->view->js('product_display.js');

		// A flag whether the item is available for purchase (for any reason, used by the auditors)
		$productAvailable = true;

		$pageMessages = array();

		// Get the cart
		$cart = new CurrentCart();

		// POST add to cart request
		$addToCartRequest = Request::getBool('addToCart', false, 'post');
		$options = Request::getArray('og', array(), 'post');
		$qty = Request::getInt('qty', 1, 'post');

		if ($addToCartRequest)
		{
			// Initialize errors array
			$errors = array();

			// Check if passed options/productID map to a SKU
			try
			{
				$sku = $this->warehouse->mapSku($pId, $options);
				$cart->add($sku, $qty);
			}
			catch (\Exception $e)
			{
				$errors[] = $e->getMessage();
				$pageMessages[] = array($e->getMessage(), 'error');
			}

			if (!empty($errors))
			{
				$this->view->setError($errors);
			}
			else
			{
				// prevent resubmitting by refresh
				// If not an ajax call, redirect to cart
				App::redirect(Route::url('index.php?option=com_cart'));
			}
		}

		// Get the product info
		$product = $this->warehouse->getProductInfo($pId);
		$this->view->product = $product;

		// Run the auditor
		$auditor = Audit::getAuditor($product, $cart->getCartInfo()->crtId);
		$auditorResponse = $auditor->audit();
		//print_r($auditorResponse); die;

		if (!empty($auditorResponse) && $auditorResponse->status != 'ok')
		{
			if ($auditorResponse->status == 'error')
			{
				// Product is not available for purchase
				$productAvailable = false;
				foreach ($auditorResponse->notices as $notice)
				{
					$pageMessages[] = array($notice, 'warning');
				}
			}
		}

		// Get option groups with options and SKUs
		$productOptions = $this->warehouse->getProductOptions($pId);

		$data = false;
		if ($productOptions->status == 'ok')
		{
			$data = $productOptions->options;
			$this->view->options = $data->options;
		}
		else
		{
			$this->view->statusMessage = $productOptions->msg;
		}

		// Find a price range for the product
		$priceRange = array('high' => 0, 'low' => false);

		/*
			Find if there is a need to display a product quantity dropdown on the initial view load. It will be only displayed for single SKU that allows multiple items.
			For multiple SKUs it will be generated by JS (no drop-down for non-JS users, sorry)
		*/
		$qtyDropDownMaxVal = 0;

		$inStock = true;
		if (!$data || !count($data->skus))
		{
			$inStock = false;
		}
		$this->view->inStock = $inStock;

		if ($data && count($data->skus) == 1)
		{
			// Set the max value for the dropdown QTY
			// TODO: add it to the SKU table to set on the per SKU level
			$qtyDropDownMaxValLimit = 20;

			// Get the first and the only value
			$arrayValues = array_values($data->skus);
			$sku = array_shift($arrayValues);

			// If no inventory tracking, there is no limit on how many can be purchased
			$qtyDropDownMaxVal = $qtyDropDownMaxValLimit;
			if ($sku['info']->sTrackInventory)
			{
				$qtyDropDownMaxVal = $sku['info']->sInventory;
			}

			if ($qtyDropDownMaxVal < 1)
			{
				$qtyDropDownMaxVal = 1;
			}
			// Limit to max number
			elseif ($qtyDropDownMaxVal > $qtyDropDownMaxValLimit)
			{
				$qtyDropDownMaxVal = $qtyDropDownMaxValLimit;
			}

			// If the SKU doesn't allow multiple items, set the dropdown to 1
			if (!$sku['info']->sAllowMultiple)
			{
				$qtyDropDownMaxVal = 1;
			}
		}

		$this->view->qtyDropDown = $qtyDropDownMaxVal;

		if ($data)
		{
			foreach ($data->skus as $sId => $info)
			{
				$info = $info['info'];

				if ($info->sPrice > $priceRange['high'])
				{
					$priceRange['high'] = $info->sPrice;
				}
				if (!$priceRange['low'] || $priceRange['low'] > $info->sPrice)
				{
					$priceRange['low'] = $info->sPrice;
				}
			}
		}
		$this->view->price = $priceRange;

		// Add custom page JS
		if ($data && (count($data->options) > 0 || count($data->skus) > 1))
		{
			$js = $this->getDisplayJs($data->options, $data->skus, $productIdentifier);
			Document::addScriptDeclaration($js);
		}

		$this->view->config = $this->config;

		$this->view->productAvailable = $productAvailable;

		//build pathway
		$this->_buildPathway($product->pName);

		// Set notifications
		$this->view->notifications = $pageMessages;

		$this->view->display();
	}

	/**
	 * Generate JS needed for displaying a product page
	 *
	 * @param   array   $ops
	 * @param   array   $skus
	 * @param   string  $productIdentifier
	 * @return  string
	 */
	private function getDisplayJs($ops, $skus, $productIdentifier)
	{
		$js = "\tSF.OPTIONS = {\n";

			// generate skus
			$js .= "\t\tskus: [\n";

			// generate pricing
			$skuPrices = "\t\tskuPrices: [";

			// generate inventory level for each SKU (for the number of products drop-down)
			$inventory = "\t\tskuInventory: [";

			$i = 0;
			foreach ($skus as $sId => $data)
			{
				$options = $data['options'];
				$info = $data['info'];
				$js .=  "\t\t\t[";

				if ($i)
				{
					$skuPrices .= ',';
				}
				// convert price to integer for precision
				$skuPrices .= '"' . $info->sPrice * 100 . '"';

				// inventory
				if ($i)
				{
					$inventory .= ',';
				}
				if (!$info->sAllowMultiple)
				{
					$inventory .= '1';
				}
				elseif (!$info->sTrackInventory)
				{
					$inventory .= '20';
				}
				elseif (empty($info->sInventory))
				{
					$inventory .= '1';
				}
				else {
					$inventory .= $info->sInventory > 20 ? 20 : $info->sInventory;
				}

				$sku = '';
				foreach ($options as $option)
				{
					if ($sku)
					{
						$sku .= ', ';
					}
					$sku .= '"' . $option . '"';
				}
				$js .= $sku;
				$js .= "]";
				$i++;

				if (count($skus) > $i)
				{
					$js .= ',';
				}
				$js .= "\n";
			}

			$js .= "\t\t],\n";

			$js .= "\n";

			/*
				skus: [
					["1", "4", "6"],
					["2", "4", "7"],
					["3", "5", "6"],
					["2", "5", "8"],
					["2", "5", "7"]
				],
			*/

			$skuPrices .= "],\n";
			$js .= $skuPrices;

			$js .= "\n";

			$inventory .= "],\n";
			$js .= $inventory;

			$js .= "\n";

			/*
				skuPrices: ["10", "5", "7"],
			*/

			// Generate ops
			$js .= "\t\tops: [\n";

			$i = 0;
			foreach ($ops as $oId => $data)
			{
				$options = $data['options'];
				$js .=  "\t\t\t[";

				$optionIds = '';
				foreach ($options as $option)
				{
					if ($optionIds)
					{
						$optionIds .= ', ';
					}
					$optionIds .= '"' . $option->oId . '"';
				}
				$js .= $optionIds;
				$js .= "]";
				$i++;

				if (count($ops) > $i)
				{
					$js .= ',';
				}
				$js .= "\n";
			}

			$js .= "\t\t],\n";
			$js .= "\n";

			/*
			ops: [
				["1", "2", "3"],
				["4", "5"],
				["6", "7", "8"]
			]
			*/

			// Product id reference
			$js .= "\t\tpId: " . '"' . $productIdentifier . '"' . "\n";

		$js .= "\t}";
		return $js;
	}

	/**
	 * Method to set the document path
	 *
	 * @param   string  $product
	 * @return  void
	 */
	public function _buildPathway($product)
	{
		if (Pathway::count() <= 0)
		{
			Pathway::append(
				Lang::txt(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}
		if ($this->_task)
		{
			Pathway::append(
				Lang::txt($product)
			);
		}
	}
}
