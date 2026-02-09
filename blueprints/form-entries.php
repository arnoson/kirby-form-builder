<?php

return function ($kirby) {
  $url = $kirby->urls()->current();
  preg_match('/\/pages\/([a-zA-Z0-9+-]+)\/?/', $url, $result);
  $slug = str_replace('+', '/', $result[1]);
  $formPage = page($slug) ?? site()->index()->drafts()->find($slug);

  if (null === $formPage) {
    return [];
  }

  $fields = KirbyFormBuilder()->formFields($formPage);
  $columns = array_reduce(
    array_slice($fields, 0, 4),
    function ($result, $field) {
      $result[$field['name']] = ['label' => $field['label']];
      return $result;
    },
    ['title' => ['label' => 'arnoson.kirby-form-builder.submitted']]
  );

  $blueprint = [
    'icon' => 'table',
    'label' => ['*' => 'arnoson.kirby-form-builder.entries'],
    'sections' => [
      'form_export_fields' => [
        'type' => 'fields',
        'fields' => [
          'form_export' => [
            'type' => 'form-export',
            'label' => ['*' => 'arnoson.kirby-form-builder.export'],
            'formId' => $formPage?->uuid()->id(),
          ],
        ],
      ],
      'form_entries' => [
        'type' => 'pages',
        'label' => ['*' => 'arnoson.kirby-form-builder.entries'],
        'empty' => ['*' => 'arnoson.kirby-form-builder.no-entries'],
        'layout' => 'table',
        'search' => true,
        'image' => false,
        'columns' => $columns,
        'sortBy' => 'form_submitted desc',
      ],
    ],
  ];

  return $blueprint;
};
