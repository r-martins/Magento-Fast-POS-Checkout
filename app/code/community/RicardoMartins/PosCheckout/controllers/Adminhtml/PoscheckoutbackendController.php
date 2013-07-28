<?php
class RicardoMartins_PosCheckout_Adminhtml_PoscheckoutbackendController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction(){
		$this->loadLayout()->_setActiveMenu("poscheckout/poscheckoutbackend")->_addBreadcrumb(Mage::helper("adminhtml")->__("POS Fast Checkout"),Mage::helper("adminhtml")->__("POS Fast Checkout"));
		return $this;
	}
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("POS Fast Checkout"));
	   
	   $this->renderLayout();
    }
    public function checkoutAction(){
    	$query = $this->getRequest()->getParam('query');

	    $product_collection = Mage::getModel('catalog/product')->getCollection();
	    $product_collection->addAttributeToFilter(array(
                                    array(
                                        'attribute' => 'sku',
                                        'eq' => $query,
                                        ),
                                    array(
                                        'attribute' => 'name',
                                        'like' => '%'. $query . '%'
                                        )
                                    ));
        $product_collection->addAttributeToSelect('*');
        $total = $product_collection->count();
        $reduce = (int) $this->getRequest()->getParam('reduce');
        $reduced = false;


        // echo (string)$product_collection->getSelect();
        if($total == 0){
            die(json_encode(array('error'=>$this->__('Product not found.'))));
        }

        if($total == 1 && $reduce){
            $_product = $product_collection->getFirstItem();
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
            $stock->setQty($stock->getQty()-1);
            $reduced = ($stock->save() != false);
        }

        $_items = array();
        $_products = array();
        foreach($product_collection as $_item)
        {
            



            $_product = array(
                'name' => $_item->getName(),
                'final_price' => Mage::helper('core')->currency($_item->getFinalPrice(), true, false),
                'image' => (string)Mage::helper('catalog/image')->init($_item, 'small_image')->resize(150),
                'sku' => $_item->getSku(),
                'short_description' => $_item->getShortDescription(),
                );

            if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_item);
                $_product['stock_item'] = $stock->getQty();
                $_product['is_in_stock'] = ($stock->getIsInStock())?Mage::helper('cataloginventory')->__('In Stock'):Mage::helper('cataloginventory')->__('Out of Stock');
            }

            $_products[] = $_product;
    
        }

        $result = array(
            'products' => $_products,
            'reduced' => $reduced,
            );


        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }
}
