<?= $page->showAdminPageInfo() ?>

<?php $activeTab = 'metadata'; ?>
<?php
    $__defImg = $item->getDefaultStorageImage();
    if ($__defImg && $__defImg->id) {
        $__thumb = "/api/media/downloadMediaImage?height=50&width=50&code=".$__defImg->code;
        $__title = htmlspecialchars($item->getMetadata('name') ?: $item->name ?: $item->code);
        echo '<div class="product-container">'
            . '<img src="'. $__thumb .'" alt="Default" class="product-thumb" />'
            . '<div class="product-title">'. $__title .'</div>'
            . '</div>';
    }
?>
<div class="tabs">
    <a href="/_spectros/admin_product/<?= $item->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_product/admin_product_prices/<?= $item->code ?>" class="tab <?= $activeTab==='prices'?'active':'' ?>">Prices</a>
    <a href="/_product/admin_product_vendors/<?= $item->code ?>" class="tab <?= $activeTab==='vendors'?'active':'' ?>">Vendors</a>
    <a href="/_product/admin_images/<?= $item->code ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">Images</a>
    <a href="/_product/admin_product_tags/<?= $item->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_product/admin_product_parts/<?= $item->code ?>" class="tab <?= $activeTab==='parts'?'active':'' ?>">Parts</a>
    <a href="/_spectros/admin_asset_sensors/<?= $item->code ?>" class="tab <?= $activeTab==='sensors'?'active':'' ?>">Sensors</a>
    <a href="/_product/admin_product_metadata/<?= $item->code ?>" class="tab <?= $activeTab==='metadata'?'active':'' ?>">Metadata</a>
    <a href="/_product/audit_log/<?= $item->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
</div>

<form id="metadataForm" name="metadataForm" method="post" action="/_product/admin_product_metadata/<?= $item->code ?>">
    <input type="hidden" name="id" id="id" value="<?= $item->id ?>" />
    <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">

    <h3>Product Metadata</h3>
    <p>Configure additional metadata fields for this product.</p>

    <div class="metadata-section">
        <h4>Basic Information</h4>
        <div class="input-horiz">
            <span class="label">Name</span>
            <input type="text" class="value input width-300px" name="name" id="name" value="<?= htmlspecialchars($item->getMetadata('name')) ?>" />
        </div>
        <div class="input-horiz">
            <span class="label">Short Description</span>
            <input type="text" class="value input width-500px" name="short_description" id="short_description" value="<?= htmlspecialchars($item->getMetadata('short_description')) ?>" />
        </div>
        <div class="input-horiz">
            <span class="label">Description</span>
            <textarea class="value input width-500px textarea-height-100" name="description" id="description"><?= htmlspecialchars($item->getMetadata('description')) ?></textarea>
        </div>
    </div>

    <div class="metadata-section">
        <h4>Product Configuration</h4>
        <div class="input-horiz">
            <span class="label">Default Dashboard</span>
            <select class="value input width-300px" name="default_dashboard_id" id="default_dashboard_id">
                <option value="">Select Dashboard</option>
                <?php $default_dashboard_id = $item->getMetadata('default_dashboard_id');
                foreach ($dashboards as $dashboard) { ?>
                    <option value="<?= $dashboard->id ?>" <?php if ($default_dashboard_id == $dashboard->id) { print " selected"; } ?>><?= htmlspecialchars($dashboard->name) ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="input-horiz">
            <span class="label">Manual</span>
            <select class="value input width-300px" name="manual_id" id="manual_id">
                <option value="">Select Manual</option>
                <?php foreach ($manuals as $manual) { ?>
                    <option value="<?= $manual->id ?>" <?php if ($item->manual_id == $manual->id) { print " selected"; } ?>><?= htmlspecialchars($manual->name) ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="input-horiz">
            <span class="label">Spec Table</span>
            <select class="value input width-300px" name="spec_table_image" id="spec_table_image">
                <option value="">Select Spec Table</option>
                <?php foreach ($tables as $table) { ?>
                    <option value="<?= $table->id ?>" <?php if ($item->spec_table_image == $table->id) { print " selected"; } ?>><?= htmlspecialchars($table->name) ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="metadata-section">
        <h4>Additional Metadata</h4>
        <?php foreach ($metadataKeys as $key) {
            if (in_array($key, ['default_dashboard_id', 'manual_id', 'spec_table_image', 'name', 'description', 'short_description'])) continue;
            $label = $key;
            $label = ucwords(str_replace("_", " ", $label));
        ?>
            <div class="input-horiz" id="item<?= $key ?>">
                <span class="label"><?= $label ?></span>
                <input type="text" class="value input width-300px" name="<?= $key ?>" id="<?= $key ?>" value="<?= htmlspecialchars($item->getMetadata($key)) ?>" />
                <button type="button" class="button delete-metadata-btn" data-key="<?= htmlspecialchars($key) ?>" title="Delete this metadata field">×</button>
            </div>
        <?php } ?>
    </div>

    <div class="metadata-section new-metadata-section">
        <h4>Add New Metadata</h4>
        <p>Add a new key/value pair to this product's metadata.</p>
        <div class="input-horiz">
            <span class="label">Key</span>
            <input type="text" class="value input width-300px" name="new_metadata_key" id="new_metadata_key" placeholder="e.g., technical_specs, features, etc." />
        </div>
        <div class="input-horiz">
            <span class="label">Value</span>
            <input type="text" class="value input width-500px" name="new_metadata_value" id="new_metadata_value" placeholder="Enter the value for this metadata key" />
        </div>
    </div>

    <div class="editSubmit button-bar floating">
        <input type="submit" class="button" value="Update Metadata" name="updateMetadata" id="updateMetadata" />
    </div>
</form>

<!-- Hidden form for deleting metadata -->
<form id="deleteMetadataForm" method="post" action="/_product/admin_product_metadata/<?= $item->code ?>" style="display: none;">
    <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
    <input type="hidden" name="deleteMetadata" value="1">
    <input type="hidden" name="delete_metadata_key" id="delete_metadata_key" value="">
</form>

<style>
.metadata-section {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.metadata-section h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
    border-bottom: 1px solid #ccc;
    padding-bottom: 5px;
}

.input-horiz {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.input-horiz .label {
    min-width: 150px;
    font-weight: bold;
    margin-right: 10px;
}

.input-horiz input,
.input-horiz select,
.input-horiz textarea {
    flex: 1;
    max-width: 500px;
}

.textarea-height-100 {
    height: 100px;
    resize: vertical;
}

.button-bar {
    margin-top: 20px;
    padding: 10px 0;
}

.button-bar .button {
    margin-right: 10px;
}

.new-metadata-section {
    border: 1px solid #ddd;
    background-color: #f9f9f9;
}

.new-metadata-section h4 {
    color: #333;
    border-bottom: 1px solid #ccc;
}

.delete-metadata-btn {
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 3px;
    padding: 5px 8px;
    margin-left: 10px;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    min-width: 30px;
}

.delete-metadata-btn:hover {
    background-color: #c82333;
}

.input-horiz {
    align-items: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear new metadata fields after successful submission
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'true') {
        const newKeyInput = document.getElementById('new_metadata_key');
        const newValueInput = document.getElementById('new_metadata_value');
        if (newKeyInput) newKeyInput.value = '';
        if (newValueInput) newValueInput.value = '';
    }
    
    // Handle delete metadata button clicks
    const deleteButtons = document.querySelectorAll('.delete-metadata-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const key = this.getAttribute('data-key');
            const label = this.closest('.input-horiz').querySelector('.label').textContent;
            
            if (confirm('Are you sure you want to delete the metadata field "' + label + '" (' + key + ')? This action cannot be undone.')) {
                // Set the key to delete and submit the form
                document.getElementById('delete_metadata_key').value = key;
                document.getElementById('deleteMetadataForm').submit();
            }
        });
    });
});
</script>
