<p align="center">
  <picture>
      <source media="(prefers-color-scheme: dark)" srcset="./.github/logo-dark.svg?2">
      <img src="./.github/logo-light.svg?2" alt="" />
  </picture>
</p>

<h1 align="center">Kirby Form Builder</h1>

A flexible form builder and handler based on Kirby's layout and block fields and [Kirby Uniform](https://github.com/mzur/kirby-uniform/).

## Features

- 🎨 Form builder using Kirby's built-in layout and blocks fields
- 🔒 Form handling using [Kirby Uniform](https://github.com/mzur/kirby-uniform/)
- ✔️ Client-side and server-side validation
- 📋 View entries directly in the panel
- 📬 Send confirmation and notification emails
- 🔗 Use Post/Redirect/Get pattern to avoid multiple submission

## Demo

https://github.com/user-attachments/assets/4c02de74-31c4-4b89-8444-39b5c0e5d6bb

## Installation

Make sure you have [Kirby Uniform](https://github.com/mzur/kirby-uniform/) installed, then install the plugin using Composer:

```shell
composer require arnoson/kirby-form-builder
```

## Getting started

The quickest way to get started is creating a new page with the `form` blueprint provided by the plugin. Now you can build your form in the panel. To render the `form`, use the form snippet inside your template:

```php
snippet('form');
```

## Options

Most options (success message, confirmation emails, ...) can be configured per form direcly in the panel in the form's `Settings` tab.

These global options can only be set in the plugin's config:

```php
'arnoson.kirby-form-builder' => [
  // A list of email addresses that can be selected in the panel as the sender
  // of confirmation and notification emails.
  'fromEmails' => [],

  // Wether or not to use client validation (in addition to server-side
  // validation done by Kirby Uniform).
  'clientValidation' => true,

  // How many columns to use for the grid that determines the layout of the
  // form. see: https://getkirby.com/docs/reference/panel/fields/layout#calculate-the-column-span-value
  'gridColumns' => 12,

  // Wether or not to use the `autocomplete` attribute for the form element.
  'autoComplete' => false,

  // Automatically add an empty placeholder to fields without a defined
  // placeholder in the panel. This is useful for css styling relying on
  // `input:placeholder-shown`.
  'addEmptyPlaceholder' => false,

  // When using the brevo action.
  // Note: do not hardcode your API key, use an .env file instead.
  'brevoApiKey' => '1234',
]
```

## Entries

Form entries will be saved directly in the form page and can be viewed/deleted/edited in the panel:

![screely-1728499707485](https://github.com/user-attachments/assets/36a44b02-10ec-4806-870d-2de324604e90)

## Using Different Layouts

By creating your own `fields/form-fields` blueprint, you can overwrite the possible layouts in the form builder. Use this [file](https://github.com/arnoson/kirby-form-builder/blob/master/blueprints/fields/form-fields.yml) as a starting point.

## Run Additional Actions

You can run additional actions on a per form basis. Just return an array of actions from the form's page model. See [form-workshop.php](https://github.com/arnoson/kirby-form-builder/blob/main/example/site/models/form-workshop.php) for a complete example.

```php
// site/models/form.php

// Define your own action or use any Kirby Uniform action instead.
class MyAction extends Uniform\Actions\Action {
  public function perform() {
    $page = $this->option('page');
    $form = $this->form;
    // Do something with the form or form page ...
  }
}

class FormPage extends Kirby\Cms\Page {
  public function actions() {
    return [MyAction::class];
  }
}
```

## Adding Custom Fields

The easiest way to add your own custom fields is to start with the [example](https://github.com/arnoson/kirby-form-builder/tree/master/example/site/plugins/custom-form-fields).

Custom fields consist of

- A block blueprint (defining the field settings like `required`, `min`, `max`, ...)
- A block snippet (to render the fields on the form)

The block name must follow the naming convention of `form-field-<panel-field-name>`, where `<panel-field-name>` is a [panel field](https://getkirby.com/docs/reference/panel/fields), used to render the field's value in the [Entries](#entries) view.

Ideally, a custom field should also have a [block preview](https://getkirby.com/docs/cookbook/panel/custom-block-type#simple-index-js).

To show your custom field as an option in the Kirby Form Builder form builder, you have to override the `fields/form-fields` blueprint, see the [example](https://github.com/arnoson/kirby-form-builder/tree/master/example/site/blueprints/fields/form-fields.yml).

## Contribute

Contributions to the plugin and it's documentation are welcome :~) Please make sure you:

- use prettier to format your code (should happen automatically if you work on this project in VSCode)
- use [conventional commits](https://www.conventionalcommits.org) (these are used to automatically generate release messages, including credits for your contributions)

To get started have a look at the `package.json`.
