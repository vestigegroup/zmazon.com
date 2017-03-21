<?php

class Application_Model_Product extends Zend_Db_Table_Abstract
{
    protected $_name = "product";
    
    public function listAllProducts()
    {
        return $this->fetchAll()->toArray();
    }
    public function deleteProduct($id)
    {
        $this->delete("id=$id");
    }
    public function allProductDetails($id)
    {
        $sql = $this->select()
                ->from(array('p' => "product"), array('id', 'name' , 'description', 'price' , 'quantity', 'rate', 'photo', 'addDate', 'categoryId', 'moneyGained'))
                ->where("p.id = $id")
                ->joinLeft(array("s" => "sale"), "p.id = s.productId", array("percentage", "startDate", "endDate"))
                ->setIntegrityCheck(false);
        
        $query = $sql->query();
        
        $result = $query->fetchAll()[0];
        return $result;
    }
    public function allProductsDetails()
    {
        $sql = $this->select()
                ->from(array('p' => "product"), array('id', 'name' , 'description', 'price' , 'quantity', 'rate', 'photo', 'addDate', 'categoryId', 'moneyGained'))
                ->joinLeft(array("s" => "sale"), "p.id = s.productId", array("percentage", "startDate", "endDate"))
                ->joinLeft(array("w" => "wishList"),  "w.productId = p.id", array("userId as wishlist_user_id"))
                ->joinLeft(array("cp" => "cart_products"), "cp.productId = p.id", array("productId as cart_product_id"))
                ->joinLeft(array("sc" => "shoppingCart" ), "sc.id = cp.cartId" , array("id as cart_id", "userId as shopping_cart_user_id"))
                ->setIntegrityCheck(false);
        
        
        $query = $sql->query();
        $result= $query->fetchAll();
        return $result;
    }
    public function addProduct($productData)
    {
        $product=$this->createRow();
        $product->name =$productData['name'];
        $product->description=$productData['description'];
        $product->quantity=(int)$productData['quantity'];
        $product->price=(float)$productData['price'];
        $product->rate=0;
        $product->numOfSale=0;
        $product->photo=$productData['photo'];
        $product->addDate=new Zend_Db_Expr('NOW()');
        $product->categoryId=(int)$productData['categoryId'];
//        var_dump($product);
//        exit();
        $product->save();
        
    }
    public function editProduct($id,$newData)
    {
        $product['name']=$newData['name'];
        $product['description']=$newData['description'];
        $product['price']=$newData['price'];
        $product['quantity']=$newData['quantity'];
        $product['photo']=$newData['photo'];
        $product['categoryId']=$newData['categoryId'];
        $this->update($product, "id=$id");
    }
    public function updateRating($product_id){
        $sql = $this->select()
                ->from(array('sc' => "rate"), array('avg(rate) as average'))
                ->group("sc.productId")
                ->having("productId =$product_id")
                ->setIntegrityCheck(false);
        //echo $sql->__toString();
        
        $query = $sql->query();
        $result = $query->fetchAll()[0];
        $product['rate'] = $result['average'];
        $this->update($product, "id=$product_id");        
    }
    
    public function statisticsForCategory()
    {
        $sql = $this->select()
                ->from(array('p' => "product"), array('name as pName', 'numOfSale as max'))
                ->joinInner(array("c" => "category"), "c.id = p.categoryId", array("name as cName"))
                ->where('numOfSale IN(?)', $this->select()
                        ->from(array('p' => "product"), array('max(numOfSale)'))
                        ->group('categoryId'))
                ->setIntegrityCheck(false);
        
        $result = $sql->query()->fetchAll();
        return $result;
    }
    
    public function statisticsForProduct() 
    {
        $sql = $this->select()
                ->from(array('p' => "product"), array('name', 'numOfSale','price'))
                ->setIntegrityCheck(false);

        $result = $sql->query()->fetchAll();
        return $result;
        
    }
    public function hasOffer($product_id){
         $sql = $this->select()
                ->from(array('sc' => "sale"), array('id'))
                ->where("productId =$product_id")
                ->setIntegrityCheck(false);
        //echo $sql->__toString();
        
        $query = $sql->query();
        $result = $query->fetchAll()[0];
        $id = $result['id'];
        if ($id){
            return true;
        }
        else {
            return false;
        }
    }
    
    public function getCurrentPrice($product_id){
        $mainPrice  = $this->getMainPrice($product_id);
        if ($this->hasOffer($product_id)){
            $sql = $this->select()
                ->from(array('sc' => "sale"), array('percentage'))
                ->where("productId =$product_id")
                ->setIntegrityCheck(false);
        
            $query = $sql->query();
            $result = $query->fetchAll()[0];
            $percentage = $result['percentage'];
            return (((100-$percentage) * $mainPrice)/100);
        }
        else {
           return $mainPrice;
        }
        
    }
    public function getMainPrice($product_id){
        $sql = $this->select()
            ->from(array('sc' => "product"), array('price'))
            ->where("productId =$product_id")
            ->setIntegrityCheck(false);

        $query = $sql->query();
        $result = $query->fetchAll()[0];
        $price = $result['price'];
        return $price;
        
    }
        
    

}

