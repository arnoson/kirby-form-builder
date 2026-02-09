<?php

use arnoson\KirbyFormBuilder\KirbyFormBuilder;

$formPage ??= $page;
$formId = KirbyFormBuilder::getFormId($formPage);
$form = new Uniform\Form(KirbyFormBuilder()->formRules($formPage), $formId);
$hasErrors = count($form->errors()) > 0;

// There might be multiple forms rendered on the page, so we only process the
// form if the form's id is matching.
if ($kirby->request()->is('POST') && get('form_id') === $formId) {
  KirbyFormBuilder()->processRequest($formPage, $form);
}
?>

<?php if ($form->success()): ?>
<?php snippet('form-success', [
  'success' => $formPage->success_text()->value(),
]); ?>
<?php else: ?>

<?php if ($hasErrors): ?>
<?php snippet('form-errors', ['form' => $form, 'formPage' => $formPage]); ?>
<?php endif; ?>

<form <?= attr([
  'action' => $page->url(),
  'method' => 'POST',
  'autocomplete' => option('arnoson.kirby-form-builder.autoComplete')
    ? 'on'
    : 'off',
  'novalidate' => option('arnoson.kirby-form-builder.clientValidation')
    ? null
    : true,
]) ?>>
  <?php snippet('form-fields', [
    'form' => $form,
    'formPage' => $formPage,
    'gridColumns' => option('arnoson.kirby-form-builder.gridColumns'),
  ]); ?>
  <input type="hidden" name="form_name" value="<?= $formPage->title() ?>" />
  <?= csrf_field() ?>
  <?= honeypot_field() ?>
  <button type="submit" name="form_id" value="<?= $formId ?>">
    <?= $formPage->label_submit()->value() ?>
  </button>
</form>

<?php endif; ?>
