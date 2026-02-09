<?php
$attributes = arnoson\KirbyFormBuilder\formFieldAttributes($id, $block, $form);

$value = $attributes['value'];
unset($attributes['value']);
?>

<?php snippet('form-label', [
  'id' => $id,
  'label' => $label,
  'required' => $block->required()->toBool(),
]); ?>
<textarea id="<?= $id ?>" <?= attr($attributes) ?>><?= $value ?></textarea>