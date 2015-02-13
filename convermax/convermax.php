<?php
/**
 * 2015 CONVERMAX CORP
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@convermax.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    CONVERMAX CORP <info@convermax.com>
 *  @copyright 2015 CONVERMAX CORP
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of CONVERMAX CORP
 */

if (!defined('_PS_VERSION_'))
	exit;

class Convermax extends Module
{
	public function __construct()
	{
		$this->name = 'convermax';
		$this->tab = 'search_filter';
		$this->version = '1.0.0';
		$this->author = 'Author';

		parent::__construct();

		$this->displayName = $this->l('Convermax');
		$this->description = $this->l('Convermax search module');

		$this->confirmUninstall = $this->l('Uninstall?');
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('leftColumn') || !$this->registerHook('header') || !$this->registerHook('backOfficeHeader')
	|| !function_exists('curl_init'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (parent::uninstall())
			return true;
		else
			return false;
	}

	public function getContent()
	{
		$this->context->smarty->assign(array(
			'url' => Configuration::get('CONVERMAX_URL'),
			'module_dir' => $this->_path,
		));
		return $this->postProcess().$this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/configuration.tpl');
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitModule'))
		{
			if (isset($_FILES['cert'])
				&& isset($_FILES['cert']['tmp_name'])
				&& !empty($_FILES['cert']['tmp_name']))
			{
				if (!stristr(Tools::substr($_FILES['cert']['name'], -4), '.pem'))
					return $this->displayError($this->l('Invalid file'));
				else
				{
					$file_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'key'.DIRECTORY_SEPARATOR.'convermax.pem';
					if (!move_uploaded_file($_FILES['cert']['tmp_name'], $file_path))
						return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
					else
						Configuration::updateValue('CONVERMAX_CERT', $file_path);
				}
				Configuration::updateValue('CONVERMAX_URL', Tools::getvalue('url'));
				return $this->displayConfirmation($this->l('Configuration updated'));
			}
			return $this->displayError($this->l('Choose file'));
		}
	}

	public function hookLeftColumn($params)
	{
		unset($params);
		if (get_class($this->context->controller) == 'SearchController')
		{
			$this->context->smarty->assign(array(
				'pagesize' => abs((int)Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE')))
			));
			return $this->display(__FILE__, 'views/templates/hook/facets.tpl');
		}
		return '';
	}

	public function hookHeader($params)
	{
		unset($params);
		$this->context->controller->addJS($this->_path.'/views/js/convermax.js');
		$this->context->controller->addCSS($this->_path.'/views/css/convermax.css');
		$this->context->controller->addJQueryUI('ui.slider');
	}

	public function hookBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path.'/views/css/backoffice.css');
		$this->context->controller->addJS($this->_path.'/views/js/backoffice.js');
		//$this->context->controller->addJqueryPlugin('fancybox');
	}

	public function ajaxCall()
	{
		$query = Tools::getValue('search_query');

		$original_query = $query;
		$query = Tools::replaceAccentedChars(urldecode($query));

		$srch_cntrl = Controller::getController('SearchController');
		$srch_cntrl->productSort();
		$srch_cntrl->n = abs((int)Tools::getValue('n', (isset($srch_cntrl->context->cookie->nb_item_per_page) ?
			(int)$srch_cntrl->context->cookie->nb_item_per_page : Configuration::get('PS_PRODUCTS_PER_PAGE'))));
		$srch_cntrl->p = abs((int)Tools::getValue('p', 1));

		$facets = Tools::getValue('cm_select');

		$search = Search::find($this->context->language->id, $query, $srch_cntrl->p, $srch_cntrl->n, $srch_cntrl->orderBy,
			$srch_cntrl->orderWay, false, true, null, $facets);

		Hook::exec('actionSearch', array('expr' => $query, 'total' => $search['total']));
		$nbProducts = $search['total'];
		$srch_cntrl->pagination($nbProducts);
		$srch_cntrl->addColorsToProductList($search['result']);

		if (stripos($search['cm_result']->State, 'nothing'))
			$cm_message = 'nothing found';
		elseif (!empty($search['cm_result']->Corrections) && $search['cm_result']->Corrections[0]->Apply)
		{
			$cm_message = 'your request has been corrected to '.$search['cm_result']->Corrections[0]->Replace;
			$original_query = $search['cm_result']->Corrections[0]->Replace;
		}
		else
			$cm_message = false;

		$this->context->smarty->assign(array(
			'products' => $search['result'], // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
			'search_products' => $search['result'],
			'nbProducts' => $search['total'],
			'search_query' => $original_query,
			'instant_search' => $srch_cntrl->instant_search,
			'cm_message' => $cm_message,
			'homeSize' => Image::getSize(ImageType::getFormatedName('home'))));

		$list = $this->context->smarty->fetch(_PS_MODULE_DIR_.'convermax/views/templates/hook/search.tpl');

		$this->context->smarty->assign(array(
			'facets' => $search['cm_result']->Facets,
			'query' => $search['cm_result']->Query,
			'pagenumber' => $srch_cntrl->p,
			'pagesize' => $srch_cntrl->n,
		));

		$facets = $this->context->smarty->fetch(_PS_MODULE_DIR_.'convermax/views/templates/hook/facet.tpl');

		$vars = array(
			'productList' => utf8_encode($list),
			'facets' => $facets,
			'redirect_url' => isset($search['cm_result']->Actions[0]->RedirectUrl) ? $search['cm_result']->Actions[0]->RedirectUrl : false
		);

		return Tools::jsonEncode($vars);
	}
}