<?php

use Kirby\Cms\Collection;
use Kirby\Cms\Layouts;
use Kirby\Http\Header;
use Kirby\Toolkit\I18n;

require_once __DIR__ . '/lib/KirbyFormBuilder.php';
require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/SaveEntryAction.php';
require_once __DIR__ . '/lib/BrevoAction.php';

function KirbyFormBuilder() {
  return arnoson\KirbyFormBuilder\KirbyFormBuilder::getInstance();
}

\Kirby\Cms\App::plugin('arnoson/kirby-form-builder', [
  'fields' => [
    'form-identifier' => ['extends' => 'slug'],
    'form-email-select' => [],
    'form-export' => [
      'props' => [
        'formId' => fn($formId) => $formId,
        'entryId' => fn($entryId = null) => $entryId,
      ],
    ],
  ],

  'blueprints' => require __DIR__ . '/blueprints/index.php',
  'pageModels' => require __DIR__ . '/models/index.php',
  'snippets' => require __DIR__ . '/snippets/index.php',
  'translations' => require __DIR__ . '/translations/index.php',
  'options' => [
    // A list of email addresses that can be selected in the panel as the sender
    // of confirmation and notification emails.
    'fromEmails' => [],

    'defaultEntryStatus' => 'unlisted',

    // Wether or not to use client validation (in addition to server-side
    // validation done by Kirby Uniform).
    'clientValidation' => true,

    // How many columns to use for the grid that determines the layout of the
    // form. see: https://getkirby.com/docs/reference/panel/fields/layout#calculate-the-column-span-value
    'gridColumns' => 12,

    // Wether or not to use the `autocomplete` attribute for the form element.
    'autoComplete' => false,

    // Wether or not to use uniform's session store action. If enabled,
    // submitted forms are available as `kirby()->session()->get(formId)` where
    // `formId` can be obtained with `KirbyFormBuilder::getFormId($yourFormPage)`
    // https://kirby-uniform.readthedocs.io/en/latest/actions/session-store/
    'sessionStore' => false,

    // Automatically add an empty placeholder to fields without a defined
    // placeholder in the panel. This is useful for css styling relying on
    // `input:placeholder-shown`.
    'addEmptyPlaceholder' => false,

    // When using the brevo action.
    'brevoApiKey' => '',
  ],

  'hooks' => [
    'page.update:before' => function ($values) {
      // Right now, we only check for the existence of a `form_fields` field to
      // check wether ot not the page is form page. This could crash if the user
      // uses a field with the same name in a non-form page.
      // TODO: find better way detect form pages (e.g. the page is a descendant of the forms page).
      if ($values['form_fields'] ?? false) {
        $formFieldsValue = $values['form_fields'];
        $formFieldsArray = is_string($formFieldsValue)
          ? json_decode($formFieldsValue, true)
          : $formFieldsValue;
        $layouts = Layouts::factory($formFieldsArray);
        $keys = [];
        foreach ($layouts as $layout) {
          foreach ($layout->columns() as $column) {
            foreach ($column->blocks() as $block) {
              if (!str_starts_with($block->type(), 'form-field-')) {
                continue;
              }
              $name = $block->name()->value();
              if ($keys[$name] ?? false) {
                throw new Exception(
                  I18n::template(
                    'arnoson.kirby-form-builder.duplicate-name-error',
                    null,
                    ['name' => $name],
                  ),
                );
              } else {
                $keys[$name] = true;
              }
            }
          }
        }
      }
    },
  ],

  'routes' => [
    [
      'pattern' => 'kirby-form-builder/export/(:any)/(:any?)',
      'action' => function ($formId, $entryId = null) {
        if (!kirby()->user()?->isLoggedIn()) {
          throw new Error('You need to be logged in to export form entries.');
        }

        $formPage = page("page://$formId");
        if (!$formPage) {
          throw new Error("Form page with id '$formId' not found");
        }

        if ($entryId && !($entryPage = page("page://$entryId"))) {
          throw new Error("Form entry with id '$entryId' not found");
        }

        $entries = $entryId
          ? new Collection([$entryPage])
          : $formPage->childrenAndDrafts()->sort('form_submitted', 'desc');

        $fields = KirbyFormBuilder()->formFields($formPage);
        $columns = array_column($fields, 'name');
        array_unshift($columns, 'form_submitted');

        Header::download([
          'mime' => 'application/csv',
          'name' => $entryId
            ? $formPage->slug() . "-$entryId.csv"
            : $formPage->slug() . '.csv',
        ]);

        $handle = fopen('php://output', 'w');
        fputcsv($handle, $columns);
        foreach ($entries as $entry) {
          $data = array_map(
            fn($column) => $entry->content()->get($column)->value(),
            $columns,
          );
          fputcsv($handle, $data);
        }
        fclose($handle);

        exit();
      },
    ],
  ],
]);
