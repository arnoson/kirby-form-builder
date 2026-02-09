<?php $attributes = arnoson\KirbyFormBuilder\formFieldAttributes(
  $id,
  $block,
  $form,
); ?>

<label>
  <input id="<?= $id ?>" type="checkbox" <?= attr($attributes) ?> />
  <?= $label ?>
  <?php if ($block->required()->toBool()): ?>
  <span aria-hidden="true">*</span>
  <?php endif; ?>
</label>