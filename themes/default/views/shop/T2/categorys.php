<?php include('header.php'); ?>
<style>
    .category_box{
            padding: 10px;
           /*padding-top:  34px;*/
    height: 176px;
    background: #FFF;
    margin: 1em;
    border: 1px solid #ccc;
    box-shadow: inset 0px 0px 9px 0px #84c639;
    text-decoration: none;
    color:#000;
        font-weight: 500;
    }
   .pageTitle{margin: 1em 0em;}
</style>

<div class="container">
    <h3 class="pageTitle"><u> Categories</u></h3>
    
    <div class="row">
        <?php foreach ($category as $val_category){ ?>
            
        <a class=" category_box col-sm-2 text-center" href="<?= base_url('shop/subcategory/'.$val_category['id']) ?>" >
            <?php
                $fielname = (file_exists("assets/mdata/$Customer_assets/uploads/thumbs/".$val_category['image'])) ?  $val_category['image'] :  'no_image.png';
            ?>
            <img src="<?= $thumbs.$fielname?>" alt="<?= $val_category['code']?>" class="img-responsive img-rounded img-thumbnail" style="width: auto; height:90px;" />
                                              
            <p><br/><?= $val_category['name'] ?> </p>
               </a>
             
        <?php } ?>
    </div>
</div>


<?php include('footer.php'); ?>