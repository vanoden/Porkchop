<div>
   <div class="breadcrumbs">
     <a class="breadcrumb" href="/_engineering/home">Engineering</a>
     <a class="breadcrumb" href="/_engineering/releases">Releases</a>
   </div>
   <?php include(MODULES.'/engineering/partials/search_bar.php'); ?>
   <form name="release_form" action="/_engineering/release" method="post">
      <input type="hidden" name="release_id" value="<?=$release->id?>" />
      <h2>Engineering Release</h2>
      <!--	Error Checking -->
      <?php	if ($page->errorCount()) { ?>
      <div class="form_error"><?=$page->errorString()?></div>
      <?php	}
         if ($page->success) { ?>
                <div class="form_success"><?=$page->success?> [<a href="/_engineering/releases">Finished</a>] | [<a href="/_engineering/release">Create Another</a>] </div>
      <?php	} ?>
      <!--	END Error Checking -->	
      <!--	START First Table -->
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 20%;">Code</div>
            <div class="tableCell" style="width: 40%;">Title</div>
            <div class="tableCell" style="width: 20%;">Status</div>
            <div class="tableCell" style="width: 20%;"></div>
         </div>
         <!-- end row header -->
         <div class="tableRow">
            <div class="tableCell">
               <input type="text" name="code" class="value wide_100per" value="<?=$form['code']?>" />
            </div>
            <div class="tableCell">
               <input type="text" name="title" class="value wide_100per" value="<?=$form['title']?>" />
            </div>
            <div class="tableCell">
               <select name="status" class="value wide_100per">
                  <option value="new" <?php if ($form['status'] == "NEW") print "selected"; ?>>New</option>
                  <option value="testing" <?php if ($form['status'] == "TESTING") print "selected"; ?>>Testing</option>
                  <option value="released" <?php if ($form['status'] == "RELEASED") print "selected"; ?>>Released</option>
               </select>
            </div>
            <div class="tableCell">
               <!-- empty cell -->
            </div>
         </div>
      </div>
      <!--	END First Table -->
      <!--	START Second Table -->
      <div class="tableBody half marginTop_20">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 50%;">Date Scheduled</div>
            <div class="tableCell" style="width: 50%;">Date Released</div>
         </div>
         <!-- end row header -->
         <div class="tableRow">
            <div class="tableCell">
               <input type="text" name="date_scheduled" class="value wide_100per" value="<?=$form['date_scheduled']?>" />
            </div>
            <div class="tableCell">
               <input type="text" name="date_released" class="value wide_100per" value="<?=$form['date_released']?>" />
            </div>
         </div>
      </div>
      <div class="tableBody clean min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 100%;">Description</div>
         </div>
         <!-- end row header -->
         <div class="tableRow">
            <div class="tableCell">
               <textarea name="description" class="wide_100per"><?=$form['description']?></textarea>
            </div>
         </div>
      </div>
      <!--	END Second Table -->
      <div class="container">
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
        <form name="repoUpload" action="/_engineering/release/<?=$form['code']?>" method="post" enctype="multipart/form-data">
        <div class="container">
            <span class="label">Upload File</span>
            <input type="hidden" name="repository_name" value="<?=$repository?>" />
            <input type="hidden" name="type" value="engineering release" />
            <input type="file" name="uploadFile" />
            <input type="submit" name="btn_submit" class="button" value="Upload" />
        </div>
        </form>
        <br/><br/>
    </div>
</div>
<!--	START First Table -->
<?php	if ($release->id) { ?>
    <div class="tableBody min-tablet marginTop_20">
        <form name="release_form" action="/_engineering/release" method="post">
           <div class="tableRowHeader">
              <div class="tableCell" style="width: 30%;">Title</div>
              <div class="tableCell" style="width: 30%;">Project</div>
              <div class="tableCell" style="width: 20%;">Product</div>
              <div class="tableCell" style="width: 20%;">Status</div>
              <div class="tableCell" style="width: 20%;">&nbsp;</div>
           </div>
           <!-- end row header -->
           <?php	foreach ($tasks as $task) { 
              $project = $task->project();
              $product = $task->product();
              ?>
           <div class="tableRow">
              <div class="tableCell">
                 <a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a>
              </div>
              <div class="tableCell">
                 <?=$project->title?>
              </div>
              <div class="tableCell">
                 <?=$product->title?>
              </div>
              <div class="tableCell">
                 <?=$task->status?>
              </div>
              <div class="tableCell">
                <?php
                 // can't postpone a complete tasks pre requirements
                 if ($task->status !== 'COMPLETE') {
                ?>
                    <input type="button" name="btn_postpone" onclick="location.replace('/_engineering/release/?code=<?=$form['code']?>&postpone=<?=$task->code?>')" class="button" value="Postpone"/>
                <?php
                 }
                ?>
              </div>
           </div>
           <?php		
              if (! $greenbar) $greenbar = 'greenbar'; else $greenbar = '';
            }
           ?>
        </form>
    </div>
    <!--	END First Table -->
    </div>
<?php	} ?>

