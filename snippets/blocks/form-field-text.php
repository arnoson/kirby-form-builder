<?php $attributes = arnoson\KirbyFormBuilder\formFieldAttributes(
  $id,
  $block,
  $form
); ?>

<?php snippet('form-label', [
  'id' => $id,
  'label' => $label,
  'required' => $block->required()->toBool(),
]); ?>
<input id="<?= $id ?>" type="text" <?= attr($attributes) ?> />