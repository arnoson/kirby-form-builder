<?php

use Kirby\Filesystem\F;

return function (\Kirby\Cms\App $kirby) {
  $templates = array_map(
    // Use `F::name()` two times, as email templates might have a double file
    // extensions like `email.text.php`
    fn($file) => F::name(F::name($file)),
    glob($kirby->root('templates') . '/emails/*.php')
  );

  return [
    'label' => t('arnoson.kirby-form-builder.template', 'template'),
    'type' => 'select',
    'options' => $templates,
  ];
};
