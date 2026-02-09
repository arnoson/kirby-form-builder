<?php

return function () {
  $emails = option('arnoson.kirby-form-builder.fromEmails');

  if (!count($emails)) {
    return [
      'type' => 'info',
      'theme' => 'negative',
      'text' => t('arnoson.kirby-form-builder.no-emails'),
    ];
  }

  return [
    'label' => 'arnoson.kirby-form-builder.email-content-template',
    'type' => 'select',
    'options' => option('arnoson.kirby-form-builder.fromEmails'),
  ];
};
