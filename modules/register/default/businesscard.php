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
            <p><?= $organization->notes ?></p>
            <a href="<?= $organization->website_url ?>"><?= $organization->website_url ?></a>
        </div>
         <a class="vcard-button" href="/_register/businessvcard/<?= $customer->code ?>">Add to Contacts</a>
        <ul class="vcard-contact">
            <?php foreach ($contacts as $contact) : ?>
                <?php if ($contact->public) : // Check if the contact is public ?>
                    <li>
                      <a href="<?= $contact->type === 'phone' ? 'tel:' . $contact->value : ($contact->type === 'sms' ? 'sms:' . $contact->value : ($contact->type === 'email' ? 'mailto:' . $contact->value : '#')) ?>">
                        <img class="vcard-icon" src="/img/vcard/icon_vcard-<?= $contact->type ?>.png" alt="<?= $contact->type ?>">
                        </a>
                        <h3><?= htmlspecialchars($contact->description) ?></h3>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </section>
<?php    } ?>
