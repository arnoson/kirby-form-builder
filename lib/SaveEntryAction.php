<?php

namespace Uniform\Actions;

use arnoson\KirbyFormBuilder\KirbyFormBuilder;
use DateTime;
use Error;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Uuid\Uuid;

class SaveEntryAction extends Action {
  public function perform() {
    try {
      $data = $this->form->data();
      /** @var \Kirby\Cms\Page */
      $page = $this->option('page');

      // We don't need `form_name` (used for email subject) and `form_id` (used
      // to differentiate multiple forms on a single page) anymore.
      unset($data['form_name']);
      unset($data['form_id']);

      // Some fields like checkboxes need to be stored in a different format.
      $fields = KirbyFormBuilder()->formFields($page);
      foreach ($data as $name => $value) {
        $field = $fields[$name] ?? null;
        if (!$field) {
          continue;
        }
        if ($field['type'] === 'checkboxes' && is_array($value)) {
          $data[$name] = implode(',', $value);
        }
      }

      $uuid = Uuid::generate();
      kirby()->impersonate('kirby', function () use ($page, $uuid, $data) {
        $entry = $page->createChild([
          'slug' => $uuid,
          'template' => 'form-entry',
          'content' => array_merge($data, [
            'uuid' => $uuid,
            'form_submitted' => date('c'),
          ]),
        ]);
        $entry = $entry->changeStatus(
          option('arnoson.kirby-form-builder.defaultEntryStatus'),
        );
      });
    } catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * @param Exception|Error $e
   */
  protected function handleException($e) {
    $error = $e->getMessage();
    $message = I18n::translate('arnoson.kirby-form-builder.save-error');
    $message = option('debug') ? "$message: $error" : "$message.";
    $this->fail($message);
  }
}
