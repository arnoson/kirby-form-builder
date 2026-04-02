<?php

namespace arnoson\KirbyFormBuilder;

use Kirby\Cms\Page;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Uniform\Form;

class KirbyFormBuilder {
  protected static $instance = null;

  public static function getInstance(): self {
    return self::$instance ??= new self();
  }

  public static function getFormId($page) {
    return Str::slug($page->uuid());
  }

  function formFields(Page $formPage): array {
    $formFields = [];
    foreach ($formPage->form_fields()->toLayouts() as $layout) {
      foreach ($layout->columns() as $column) {
        foreach ($column->blocks() as $block) {
          if (!preg_match('/^form-field-([\w_-]+)/', $block->type(), $match)) {
            continue;
          }
          // Convert empty values to null to prevent issues (e.g., empty 'min'
          // crashing Kirby's number field)
          $field = A::map(
            $block->content()->toArray(),
            fn($value) => $value === '' ? null : $value,
          );
          $type = $match[1];

          $field['type'] = $field['field'] ?? $type;
          $field['required'] = $block->content()->required()->toBool();

          // Options are stored as an array of ['value' => ..., 'text' => ...]
          // which should be allowed but isn't handled correctly by Kirby.
          // Instead we have to restructure them as [value => text].
          if (isset($field['options'])) {
            $options = [];
            foreach ($field['options'] as $option) {
              $value = $option['value'];
              $text = $option['text'] ?: $value;
              $options[$value] = $text;
            }
            $field['options'] = $options;
          }

          $formFields[$field['name']] = $field;
        }
      }
    }
    return $formFields;
  }

  /**
   * Generate the form rules for the specified form page for use with
   * Kirby Uniform.
   */
  function formRules(Page $formPage): array {
    $formRules = [
      // `form_name` and `form_id` are hidden fields we send with every form.
      'form_name' => [],
      'form_id' => [],
    ];
    foreach ($formPage->form_fields()->toLayouts() as $layout) {
      foreach ($layout->columns() as $column) {
        foreach ($column->blocks() as $field) {
          $pattern = $field->pattern()->value();
          $rules = [
            'required' => $field->required()->toBool(),
            'min' => $field->min()->value(),
            'max' => $field->max()->value(),
            'minLength' => $field->min_length()->value(),
            'maxLength' => $field->max_length()->value(),
            // Html's `pattern` attribute doesn't want the regexp's enclosing `/`
            // while uniform needs it.
            'match' => $pattern ? "/$pattern/" : null,
          ];

          // In the hidden `validators` field additional validators can be
          // stored as json. Example: ["email", "min" => 3]
          if ($field->validators()->isNotEmpty()) {
            $rules = array_merge($rules, $field->validators()->toData('json'));
          }

          $formRules[$field->name()->value()] = [
            'rules' => array_filter($rules),
            'message' => $field->error()->value(),
          ];
        }
      }
    }
    return $formRules;
  }

  function processRequest(Page $formPage, Form $form) {
    $customActions = $formPage->actions();
    if (is_array($customActions)) {
      foreach ($customActions as $action) {
        $form->action($action, ['page' => $formPage]);
      }
    }

    if ($formPage->brevo_enabled()->toBool()) {
      $form->brevoAction(['page' => $formPage]);
    }

    if ($formPage->confirmationEmail_enabled()->toBool()) {
      $toEmailName = $formPage->confirmationEmail_to()->value();
      $toEmail = $form->data($toEmailName);
      if ($toEmail) {
        $form->action(EmailAction::class, [
          'to' => $toEmail,
          'from' => $formPage->confirmationEmail_from()->value(),
          'subject' => $formPage->confirmationEmail_subject()->value(),
          'template' => $formPage
            ->confirmationEmail_template()
            ->or(null)
            ->value(),
          'body' => $formPage->confirmationEmail_body()->or(null)->value(),
        ]);
      }
    }

    if ($formPage->notificationEmail_enabled()->toBool()) {
      $form->action(EmailAction::class, [
        'to' => $formPage->notificationEmail_to()->value(),
        'from' => $formPage->notificationEmail_from()->value(),
        'subject' => $formPage->notificationEmail_subject()->value(),
        'template' => $formPage
          ->notificationEmail_template()
          ->or(null)
          ->value(),
        'body' => $formPage->notificationEmail_body()->or(null)->value(),
      ]);
    }

    if ($formPage->save_entries()->or(true)->toBool()) {
      $form->saveEntryAction(['page' => $formPage]);
    }

    if ($formPage->sessionStore()->toBool()) {
      $form->sessionStoreAction([
        'name' => KirbyFormBuilder::getFormId($formPage),
      ]);
    }

    if ($form->success()) {
      // If we use multiple forms on a single page, we have to be able to
      // distinguish which form was successful.
      flash('kirby-form-builder.success_form_id', get('form_id'));

      $successType = $formPage->success_type()->value();
      $successUrl = $formPage->success_page()->toPage()?->url();

      if ($successType === 'page' && $successUrl) {
        go($successUrl, 303);
      } else {
        $form->done();
      }
    }
  }
}
