<section id="hero" class="bnr-products">
  <div class="product-title">
    <img class="orb" src="/img/products/title-orbs/orb-sm_sulfuryl-fluoride.png" alt="Sulfuryl Fluoride Gas"><h1><?=$product->name()?></h1>
  <p>Sulfuryl Fluoride Monitors</p>
  </div>
  <div class="product-image">
    <img class="monitor" src="/img/products/monitors/sf400_lg.png" alt="Image of a fumigation monitor">
  </div>
</section>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>
</section>

<section>
<?php  if ($product->code) { ?>
      <h2><?=$product->short_description()?></h2> 
      <p><?=$product->description()?></p>
      <p><?=$product->accuracy()?></p>
<?php	} ?>
</section>

<section>
	<div class="colSpan_3 pad_vert-sm">
    <h1>Multi-Zone Fumigation Monitoring</h1>
    <p>The rugged, modular design of the SF400 delivers unmatched sulfuryl fluoride fumigation performance. The user may access multiple sampling points onsite or remotely via a secure encrypted web portal. Sulfuryl Fluoride concentration values for each zone are viewed in real-time then automatically
      archived for later review. This actionable intelligence transforms raw data into informed decision making.</p>
    <h1>Online Monitor Portal</h1>
    <p>A diversified sensor platform for fumigation that allows users to track gas concentrations, temperature, relative humidity and more from any remote location. Our monitors continuously transmit accumulated data securely, via Amazon Web Services (AWS), using 256-bit SSL encryption to prevent security risk to your firewall configuration.</p>
    <p>The portal visualizes:
      <ul>
        <li>Real-time concentration readings from multiple sampling points</li>
        <li>Graphing of concentration over time for all points</li>
        <li>Details about each sensor</li>
        <li>Job specifics: location, time zone, fumigant, commodity, environment and scheduling</li>
        <li>Status conditions and alerts</li>
      </ul>
      This actionable intelligence transforms raw data into informed decision making.
    </p>
    <img src="/img/aboutus/portal-sample.png" class="content_image" alt="Screen image of an online portal showing a gas fumigation graph">
    <div class="data-coll pm400"></div>
    <div class="button-group">
      <button href="contact_us.html">Request More Info</a>
      <button class="iconButton iconDownload" href="" target="_blank">View Data Sheet</button>
      </div>
    </div>
  </div>

  <div class="colSpan_3 pad_vert-sm">
    <h1>Product Specifications</h1>
    <div class="tableBody bandedRows">
      <div class="tableRowHeader">
        <div class="tableCell">Property</div><div class="tableCell">Specification</div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Product Type</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Product Type')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Coverage</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Coverage')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Measuring Range</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('measure_range')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Monitoring Distance</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Monitoring Distance')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Detector Type</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Detector Type')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Accuracy</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Accuracy')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Front Panel</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Front Panel')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Data Logging &amp; Web Portal Upload</div><div class="tableCell"><ul><li>Advanced microprocessor command and conrol</li><li>Data Storage with continuous data transfer to Spectros Instruments Web Portal and archival option of 1 million record storage</li></ul></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Displays</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Displays')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Sampling Frequency</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Sampling Frequency')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Response Time/Flow Rate</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Response Time')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Power Safety Mode</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Power Safety Mode')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">AC Power</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('AC Power')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Power Consumption</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Power Consumption')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">System Noise</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('System Noise')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Operating Temp</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Operating Temperature')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Ambient Humidity</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Ambient Humidity')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Temperature Drift</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Temperature Drift')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Altitude Limit</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Altitude Limit')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Size/Weight</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Dimensions')) ?></div>
      </div>
      <div class="tableRow">
        <div class="tableCell">Warranty</div><div class="tableCell"><?= htmlspecialchars($product->getMetadata('Warranty')) ?></div>
      </div>
    </div>
  </div>
</section>
