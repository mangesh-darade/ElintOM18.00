<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CheckStock_model extends CI_Model {
    
    public function getStock($warehouse, $product){
        
        $response = array();
        if($warehouse){
            
            $warehouseDetails =  $this->getWarehouseDetails($warehouse);
             foreach($product as $key => $pv){
               $Expro = explode("_", $key);
               $productD = $this->getProductDetails($Expro[0]);
                if(isset($Expro[1])){
                  $getPStock =  $this->db->select('SUM(quantity) as QTY')->where(['product_id'=>$productD->id,'option_id' => $Expro[1],'warehouse_id'=>$warehouseDetails->id])->get('warehouses_products_variants')->row();
                  $variant =  $this->product_variant($productD->id, $Expro[1], '1');
                  $color =  $this->product_variant($productD->id, $Expro[2], '2');
                }else{
                  $getPStock =  $this->db->select('SUM(quantity) as QTY')->where(['product_id'=>$productD->id,'warehouse_id'=>$warehouseDetails->id])->get('sma_warehouses_products')->row();
                  $variant =  NULL;
                  $color =  NULL ;     
                  
                }
                
                $response[] =[
                    'warehouse' => $warehouseDetails->name,
                    'product_barcode' => $key,
                    'product_name'    => ($productD)?$productD->name.(isset($variant)?' - '.$variant:'').(isset($color)?' ('.$color.')' :' '):'-- Product dose not belongs to the system --',
                    'style_code'      => (isset($productD->style_code)?$productD->style_code :''),
                    'color'           => (isset($color)?$color:'---'),
                    'size'            => (isset($variant)?$variant:'---'),
                    'product_scan'    =>  $pv,     
                    'Stock'           =>  $getPStock->QTY, 
                    'product_id'      =>  $productD->id,
                    'variant_id'      =>  (isset($Expro[1])?$Expro[1] :''),
                ];
               
             }    
                
        }else{
            foreach($product as $key => $pv){
               $Expro = explode("_", $key);
               $productD = $this->getProductDetails($Expro[0]);
                if(isset($Expro[1])){
                  $getPStock =  $this->db->select('SUM(quantity) as QTY')->where(['product_id'=>$productD->id,'option_id' => $Expro[1]])->get('warehouses_products_variants')->row();
                  $variant =  $this->product_variant($productD->id, $Expro[1], '1');
                  $color =  $this->product_variant($productD->id, $Expro[2], '2');
                }else{
                  $getPStock =  $this->db->select('SUM(quantity) as QTY')->where(['product_id'=>$productD->id])->get('sma_warehouses_products')->row();
                  $variant =  NULL;
                  $color =  NULL ;       
                }
               
                $response[] =[
                    'product_barcode' => $key,
                    'product_name'    => ($productD)?$productD->name.(isset($variant)?' - '.$variant:'').(isset($color)?' ('.$color.')' :' '):'-- Product dose not belongs to the system --',
                    'style_code'      => (isset($productD->style_code)?$productD->style_code :''),
                    'color'           => (isset($color)?$color:'---'),
                    'size'            => (isset($variant)?$variant:'---'),
                    'product_scan'    =>  $pv, 
                    'variant_id'      =>  (isset($Expro[1])?$Expro[1] :''),
                    'Stock'           =>  $getPStock->QTY,
                    'product_id'      =>  $productD->id,
                ];
             
                
            }
        }
        return $response;
    }
    
    public function getProductDetails($barcode){
       $product_details =  $this->db->select('id,name,category_id, article_code as style_code')->where(['code'=> $barcode])->get('products')->row();
       return $product_details;
    }
    
    public function product_variant($productId, $variantID, $groupId){
        
       $getvariant =  $this->db->select('name')->where(['product_id'=>$productId,'id'=>$variantID, 'group_id'=> $groupId])->get('product_variants')->row();
       return $getvariant->name;
       
    }
    
    public function getWarehouseDetails($warehouseid){
        $warehouseDetails = $this->db->select('id,name')->where(['id'=>$warehouseid])->get('warehouses')->row();
        return $warehouseDetails;
        
    }
    
    public function getCategoryName($categoryId){
        $getCategory = $this->db->select('id,name')->where(['id'=>$categoryId])->get('categories')->row();
        return $getCategory;
    }
    
    public function getCategoryProduct($warehouse, $product, $categorys){
       
         $response = array();
        if($warehouse){ 
             $warehouseDetails =  $this->getWarehouseDetails($warehouse);
             foreach($product as $key => $pv){
               $Expro = explode("_", $key);
               $productD = $this->getProductDetails($Expro[0]);
               //if(in_array($productD->category_id,$categorys)){
                  $category_name = $this->getCategoryName($productD->category_id); 
                if (isset($Expro[1])) {
                        $getPStock = $this->db->select('SUM(quantity) as QTY')->where(['product_id' => $productD->id, 'option_id' => $Expro[1], 'warehouse_id' => $warehouseDetails->id])->get('warehouses_products_variants')->row();
                        $variant = $this->product_variant($productD->id, $Expro[1], '1');
                        $color = $this->product_variant($productD->id, $Expro[2], '2');
                    } else {
                        $getPStock = $this->db->select('SUM(quantity) as QTY')->where(['product_id' => $productD->id, 'warehouse_id' => $warehouseDetails->id])->get('sma_warehouses_products')->row();
                        $variant = NULL;
                        $color = NULL;
                    }
                 
                    $response[] = [
                        'warehouse' => $warehouseDetails->name,
                        'product_barcode' => $key,
                        'product_name' => ($productD) ? $productD->name . (isset($variant) ? ' - ' . $variant : '') . (isset($color) ? ' (' . $color . ')' : ' ') : '-- Product dose not belongs to the system --',
                        'style_code'      => (isset($productD->style_code)?$productD->style_code :''),
                        'color'           => (isset($color)?$color:'---'),
                        'size'            => (isset($variant)?$variant:'---'),
                        'category_name' => (in_array($productD->category_id,$categorys))?$category_name->name:$category_name->name.'<br/><span style="font-size:10px;">(Note: Product dose not belongs the selected category)</span>',
                        'hidebutton'   => (in_array($productD->category_id,$categorys)?'show':'hide'),
                        'product_scan' => $pv,
                        'Stock' => $getPStock->QTY,
                        'product_id'      =>  $productD->id,
                        'variant_id'      =>  (isset($Expro[1])? $Expro[1] :''),
                    ];
                  
               // }
            }
        }else{
            foreach($product as $key => $pv){
               $Expro = explode("_", $key);
               $productD = $this->getProductDetails($Expro[0]);
              // if(in_array($productD->category_id,$categorys)){
                   $category_name = $this->getCategoryName($productD->category_id);
                   if(isset($Expro[1])){
                      $getPStock =  $this->db->select('SUM(quantity) as QTY')->where(['product_id'=>$productD->id,'option_id' => $Expro[1]])->get('warehouses_products_variants')->row();
                      $variant =  $this->product_variant($productD->id, $Expro[1], '1');
                      $color =  $this->product_variant($productD->id, $Expro[2], '2');
                    }else{
                      $getPStock =  $this->db->select('SUM(quantity) as QTY')->where(['product_id'=>$productD->id])->get('sma_warehouses_products')->row();
                      $variant =  NULL;
                      $color =  NULL ;       
                    }
                  
                    $response[] =[
                        'product_barcode' => $key,
                        'product_name'    => ($productD)?$productD->name.(isset($variant)?' - '.$variant:'').(isset($color)?' ('.$color.')' :' '):'-- Product dose not belongs to the system --',
                        'style_code'      => (isset($productD->style_code)?$productD->style_code :''),
                        'color'           => (isset($color)?$color:'---'),
                        'size'            => (isset($variant)?$variant:'---'),
                        'category_name'   => (in_array($productD->category_id,$categorys))?$category_name->name:$category_name->name.'<br/><span style="font-size:10px;">(Note: Product dose not belongs the selected category)</span>',
                        'hidebutton'   => (in_array($productD->category_id,$categorys)?'show':'hide'), 
                        'product_scan'    =>  $pv,  
                        'Stock'           =>  $getPStock->QTY,
                        'product_id'      =>  $productD->id,
                        'variant_id'      =>  (isset($Expro[1])?$Expro[1] :''),
                    ];
                  

               // }
            }
        }  
        return $response;
    }


   public function getUserWarehouse(){
       $warehosue =  $this->db->select('id,name')->where_in('id',$this->session->userdata('warehouse_id'))->get('sma_warehouses')->result();
       return $warehosue;
    }
}