<?php

use Kirby\Toolkit\Str;

return function ($kirby) {
  $url = $kirby->urls()->current();

  // This blueprint gets called in the entries tab because of the form entries
  // and export sections. But at this point the url is still the form page, not the form
  // entry.
  // TODO: find a more robust way of handling this.
  if (
    Str::endsWith($url, '/sections/form_entries') ||
    Str::endsWith($url, '/sections/form_export')
  ) {
    return [];
  }

  $result = preg_match('/\/pages\/([a-zA-Z0-9+-]+)\/?/', $url, $matches);
  if (!$result) {
    return;
  }
  $slug = str_replace('+', '/', $matches[1]);
  /** @var Kirby\Cms\Page */
  $entryPage = page($slug) ?? site()->index()->drafts()->find($slug);
  $formPage = $entryPage->parent();

  $blueprint = [
    'options' => [
      'changeSlug' => false,
      'changeTitle' => false,
      'changeTemplate' => false,
      'duplicate' => false,
      'move' => false,
    ],
    'type' => 'fields',
    'fields' => [
      'form_export' => [
        'type' => 'form-export',
        'label' => ['*' => 'arnoson.kirby-form-builder.export'],
        'formId' => $formPage?->uuid()->id(),
        'entryId' => $entryPage?->uuid()->id(),
      ],
    ],
  ];

  if (!$formPage) {
    $blueprint['fields']['form_error'] = [
      'type' => 'info',
      'theme' => 'negative',
      'text' => tt(
        'arnoson.kirby-form-builder.form-not-found',
        replace: ['name' => $slug],
      ),
    ];
    return $blueprint;
  }

  if (!$entryPage) {
    $blueprint['fields']['form_entry_error'] = [
      'type' => 'info',
      'theme' => 'negative',
      'text' => tt(
        'arnoson.kirby-form-builder.form-entry-not-found',
        replace: ['name' => $slug],
      ),
    ];
    return $blueprint;
  }

  $formFields = KirbyFormBuilder()->formFields($formPage);
  if (!count($formFields)) {
    $blueprint['fields']['form_info'] = [
      'type' => 'info',
      'label' => 'Data',
      'text' => t('arnoson.kirby-form-builder.no-fields'),
    ];
    return $blueprint;
  }

  foreach ($formFields as $field) {
    $blueprint['fields'][$field['name']] = $field;
  }

  return $blueprint;
};
