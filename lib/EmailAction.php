<?php

namespace arnoson\KirbyFormBuilder;

use Kirby\Cms\App;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\I18n;

/**
 * Extends Uniform's EmailAction to handle array field values (e.g. checkboxes).
 * Uniform's resolveTemplate() filters out non-scalar values, which means array
 * fields are silently dropped from {{ }} template strings. This override joins
 * array values with ", " so they appear correctly in the email body/subject.
 */
class EmailAction extends \Uniform\Actions\EmailAction {
  protected function resolveTemplate($string) {
    $templatableItems = array_map(
      function ($item) {
        if (is_array($item)) {
          return implode(', ', array_filter($item, fn($i) => $i !== ''));
        }
        return $item;
      },
      array_filter($this->form->data(), function ($item) {
        return is_scalar($item) || is_array($item);
      }),
    );

    $version = explode('.', App::version());
    $majorVersion = intval($version[0]);
    $minorVersion = intval($version[1]);
    $fallback = ['fallback' => ''];

    // The arguments to Str::template changed in Kirby 3.6.
    if ($majorVersion <= 3 && $minorVersion <= 5) {
      $fallback = '';
    }

    return Str::template($string, $templatableItems, $fallback);
  }
}
