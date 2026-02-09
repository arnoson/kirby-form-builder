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
<select <?= attr($attributes) ?>>
  <?php if ($block->empty()->toBool() || $block->default()->isEmpty()): ?>
  <option value=""><?= $block->placeholder()->isEmpty()
    ? ' — '
    : $block->placeholder() ?></option>
  <?php endif; ?>
  <?php foreach ($block->options()->toStructure() as $option): ?>
  <option <?= attr([
    'value' => $option->value(),
    'selected' => $option->value()->value() == $block->default()->value(),
  ]) ?>><?= $option->text()->isEmpty()
  ? $option->value()
  : $option->text() ?></option>
  <?php endforeach; ?>
</select>