<link href="/css/vcard.css" rel="stylesheet">

<?php if ($page->errorCount() > 0) { ?>
    <section id="form-message">
        <ul class="connectBorder errorText">
            <li><?= $page->errorString() ?></li>
        </ul>
    </section>
<?php    } else {    ?>
    <section class="vcard">
        <img class="vcard-logo" src="/img/vcard/logo_spectros.png" alt="Spectros Instruments logo">
        <img class="vcard-profile" src="<?= $profileImage['src'] ?>" alt="<?= $profileImage['alt'] ?>">
        <div>
          <h1><?= $customer->first_name . ' ' . $customer->last_name ?></h1>
          <h2 class="name-title"><?= htmlentities($customer->getMetadata('job_title')) ?></h2>
          <p class="name-description"><?= htmlentities($customer->getMetadata('job_description')) ?></p>
        </div>
        <div class="about-us">
            <h2><?= $organization->name ?></h2>
            <p><?= stripslashes($organization->notes) ?></p>
            <a href="<?= $organization->website_url ?>"><?= $organization->website_url ?></a>
        </div>
         <a id="btn_save_contact" class="vcard-button" href="/_register/businessvcard/<?= $customer->code ?>">Add to Contacts</a>
        <ul class="vcard-contact">
            <?php foreach ($contacts as $contact) : ?>
                <?php if ($contact->public) : // Check if the contact is public ?>
                    <li>
                      <?php
                      // Determine the appropriate href for each contact type
                      $href = '#';
                      switch($contact->type) {
                          case 'phone':
                              $href = 'tel:' . $contact->value;
                              break;
                          case 'email':
                              $href = 'mailto:' . $contact->value;
                              break;
                          case 'sms':
                              $href = 'sms:' . $contact->value;
                              break;
                          case 'facebook':
                              $href = $contact->value; // Direct link to Facebook profile
                              break;
                          case 'insite':
                              $href = '#'; // Website message handled via site functionality
                              break;
                          default:
                              $href = $contact->value; // Default to the value itself for other types
                      }
                      ?>
                      <a href="<?= $href ?>">
                        <img class="vcard-icon" src="/img/vcard/icon_vcard-<?= $contact->type ?>.png" alt="<?= $contact->type ?>">
                        </a>
                        <h3><?= htmlspecialchars($contact->description) ?></h3>
                        <p><?= htmlspecialchars($contact->value) ?></p>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </section>
<?php    } ?>
