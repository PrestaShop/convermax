{*
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
*  @author CONVERMAX CORP <info@convermax.com>
*  @copyright  2015 CONVERMAX CORP
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of CONVERMAX CORP
*}
<div id="cm_search_block_top" class="col-sm-4 clearfix">
    <form id="cm_searchbox" method="get" action="{$cm_search_url|escape:'html':'UTF-8'}" >
        <input type="hidden" name="fc" value="module" />
        <input type="hidden" name="module" value="convermax" />
        <input type="hidden" name="controller" value="search" />
        <input type="hidden" name="orderby" value="position" />
        <input type="hidden" name="orderway" value="desc" />
        <input class="search_query form-control" type="text" id="cm_search_query_top" name="search_query" placeholder="{l s='Search' mod='convermax'}" value="{$search_query_block|escape:'htmlall':'UTF-8'|stripslashes}" />
        <button type="submit" name="cm_submit_search" class="btn btn-default button-search" id="cm_search_button">
            <span>{l s='Search' mod='convermax'}</span>
        </button>
    </form>
</div>