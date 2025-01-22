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
        <img class="vcard-profile" src="/img/_global/icon_myaccount.svg" alt="personal profile photo">
        <h1><?= $customer->first_name . ' ' . $customer->last_name ?></h1>
        <div class="about-us">
            <h2>About our company</h2>
            <p><?= $customer->organization()->name ?></p>
        </div>
        <a class="vcard-button" href="/business_card?vcard=show">Add to Contacts</a>
        <ul class="vcard-contact">
            <?php foreach ($contacts as $contact) : ?>
                <?php if ($contact->public) : // Check if the contact is public ?>
                    <li>
                      <a href="<?= $contact->type === 'phone' ? 'tel:' . $contact->value : ($contact->type === 'sms' ? 'sms:' . $contact->value : ($contact->type === 'email' ? 'mailto:' . $contact->value : '#')) ?>">
                        <img class="vcard-icon" src="/img/vcard/icon_vcard-<?= $contact->type ?>.png" alt="<?= $contact->type ?>">
                            <!-- <?= htmlspecialchars($contact->value) ?> -->
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </section>
<?php    } ?>