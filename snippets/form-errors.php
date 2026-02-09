<?php
/** @var Uniform\Form $form */
/** @var Kirby\Cms\Page $formPage */

use Kirby\Toolkit\A;

$errors = [];
$hasFieldError = false;
$fields = KirbyFormBuilder()->formFields($formPage);
$fieldNames = array_keys($fields);

// Individual field errors are handled directly in the corresponding field
// snippets. We only display non-field errors (like an email action error) and
// the generic invalid fields error.
foreach ($form->errors() as $name => $error) {
  $isFieldError = in_array($name, $fieldNames);
  if ($isFieldError) {
    $hasFieldError = true;
    continue;
  }
  // $error can be a single error, or an array of errors.
  array_push($errors, ...A::wrap($error));
}
if ($hasFieldError && $formPage->error_invalidFields()->isNotEmpty()) {
  array_unshift($errors, $formPage->error_invalidFields()->value());
}
?>

<?php if (count($errors)): ?>
<div class="form-errors">
  <ul>
    <?php foreach ($errors as $error): ?>
    <li><?= $error ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>
