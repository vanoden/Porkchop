<div>
   <form name="product_form" action="/_engineering/product" method="post">
      <input type="hidden" name="product_id" value="<?=$product->id?>" />
      <div class="breadcrumbs">
         <a href="/_engineering/home">Engineering</a>
         <a href="/_engineering/products">Products</a> > Product Details
      </div>
      <h2>Engineering Product</h2>
      <?	if ($page->error) { ?>
      <div class="form_error"><?=$page->error?></div>
      <?	}
         if ($page->success) { ?>
      <div class="form_success"><?=$page->success?> [<a href="/_engineering/products">Finished</a>] | [<a href="/_engineering/product">Create Another</a>] </div>
      <?	} ?>
      <!--	START First Table -->
      <div class="tableBody half min-tablet marginTop_20">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 50%;">Code</div>
            <div class="tableCell" style="width: 50%;">Title</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <input type="text" name="code" class="value wide_100per" value="<?=$form['code']?>" />
            </div>
            <div class="tableCell">
               <input type="text" name="title" class="value wide_100per" value="<?=$form['title']?>" />
            </div>
         </div>
      </div>
      <!--	END First Table -->
      <!--	START First Table -->
      <div class="tableBody half min-tablet marginTop_20">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 100%;">Description</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <textarea name="description" style="width: 700px; height: 300px;"><?=$form['description']?></textarea>
            </div>
         </div>
      </div>
      <!--	END First Table -->
      <?php
         if (!$page->success) {
         ?>
      <div class="button-bar">
         <input type="submit" name="btn_submit" class="button" value="Submit">
      </div>
      <?php
         }
         ?>
   </form>
</div>