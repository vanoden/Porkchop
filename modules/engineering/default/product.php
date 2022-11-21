<div>
  <div class="breadcrumbs">
     <a href="/_engineering/home">Engineering</a>
     <a href="/_engineering/products">Products</a> > Product Details
  </div>
  <?php include(MODULES.'/engineering/default/partials/search_bar.php'); ?>
   <form name="product_form" action="/_engineering/product" method="post">
      <input type="hidden" name="product_id" value="<?=$product->id?>" />      
      <h2>Engineering Product</h2>
      <?php	if ($page->error) { ?>
          <div class="form_error"><?=$page->errorString()?></div>
      <?php	}
         if ($page->success) { ?>
            <div class="form_success"><?=$page->success?> [<a href="/_engineering/products">Finished</a>] | [<a href="/_engineering/product">Create Another</a>] </div>
      <?php	} ?>
      <!--	START First Table -->
      <div class="tableBody half min-tablet marginTop_20">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 50%;">Code</div>
            <div class="tableCell" style="width: 50%;">Title</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <input type="text" name="code" class="value wide_100per" <?=isset($form['code']) ? 'readonly="readonly" style="color:#666;"' : ''?> value="<?=$form['code']?>" />
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
      <div class="button-bar">
         <input type="submit" name="btn_submit" class="button" value="Submit">
      </div>
   </form>
   <div style="width: 756px;">
        <br/><hr/><h2>Documents</h2><br/>
        <?php
        if ($filesUploaded) {
        ?>
            <table style="width: 100%; margin-bottom: 10px; border: 1px solid gray">
                <tr>
	                <th>File Name</th>
	                <th>User</th>
	                <th>Organization</th>
	                <th>Uploaded</th>
                </tr>
                <?php
                foreach ($filesUploaded as $fileUploaded) {
                ?>
                    <tr>
	                    <td><a href="/_storage/downloadfile?file_id=<?=$fileUploaded->id?>" target="_blank"><?=$fileUploaded->name?></a></td>
	                    <td><?=$fileUploaded->user->first_name?> <?=$fileUploaded->user->last_name?></td>
	                    <td><?=$fileUploaded->user->organization->name?></td>
	                    <td><?=date("M. j, Y, g:i a", strtotime($fileUploaded->date_created))?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        <?php
        }
        ?>
        <form name="repoUpload" action="/_engineering/product/<?=$form['code']?>" method="post" enctype="multipart/form-data">
            <div class="container">
	            <span class="label">Upload File</span>
                <input type="hidden" name="repository_name" value="<?=$repository?>" />
	            <input type="hidden" name="type" value="engineering product" />
	            <input type="file" name="uploadFile" />
	            <input type="submit" name="btn_submit" class="button" value="Upload" />
            </div>
        </form>
        <br/><br/>
    </div>
</div>
