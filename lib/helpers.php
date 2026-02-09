<?php

namespace arnoson\KirbyFormBuilder;

/**
 * Generate the html attributes for a form field.
 * @param Kirby\Cms\Block $field
 * @param Uniform\Form $form
 */
function formFieldAttributes($id, $field, $form): array {
  $name = $field->name()->value();
  $errorMessage = $form->error($name)[0] ?? null;
  $hasError = !!$form->error($name);
  $default = $field->default()->value();
  $value = $form->old($name) ?? $default;
  $defaultPlaceholder = option('arnoson.kirby-form-builder.addEmptyPlaceholder')
    ? '  ' // We need two spaces, otherwise kirby will show a warning.
    : null;

  $attributes = [
    'name' => $name,
    'value' => $value ? $value : null,
    'placeholder' => $field->placeholder()->or($defaultPlaceholder)->value(),
    'step' => $field->step()->or(null)->value(),
    'aria-invalid' => $hasError ? 'true' : null,
    'aria-describedby' => $errorMessage ? "$id/error" : null,
    'required' => $field->required()->toBool(),
    'min' => $field->min()->or(null)->value(),
    'max' => $field->max()->or(null)->value(),
    'minlength' => $field->min_length()->or(null)->value(),
    'maxlength' => $field->max_length()->or(null)->value(),
    'pattern' => $field->pattern()->or(null)->value(),
  ];

  return $attributes;
}
