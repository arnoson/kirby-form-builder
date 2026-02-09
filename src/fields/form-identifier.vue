<template>
  <k-field
    v-bind="$props"
    :input="_uid"
    :help="preview"
    class="k-slug-field kf-field-identifier"
  >
    <template v-if="wizard && wizard.text" #options>
      <k-button :text="$t(wizard.text)" icon="wand" @click="onWizard" />
    </template>

    <k-input
      v-bind="$props"
      :id="_uid"
      ref="input"
      :value="slug"
      theme="field"
      type="form-identifier"
      v-on="$listeners"
      @focus="inputIsFocused = true"
      @blur="inputIsFocused = false"
    />

    <k-box v-if="value === 'website'" theme="negative" style="margin-top: 1rem"
      >{{ $t('arnoson.kirby-form-builder.reserved-identifier') }}
    </k-box>
  </k-field>
</template>

<script>
export default {
  extends: 'k-slug-field',

  data() {
    return { inputIsFocused: false }
  },

  computed: {
    wizardText() {
      const { text } = this.wizard ?? {}
      return text.includes('.') ? this.$t(text) : text
    },
  },

  methods: {
    onWizard() {
      const field = this.wizard?.field
      if (!field) return

      let value = this.formData[field.toLowerCase()]
      if (value.toLowerCase() === 'website') this.slug = 'website_url'
      else if (value) this.slug = value
    },
  },

  watch: {
    value(value) {
      const isHoneypot = value.toLowerCase() === 'website'
      if (isHoneypot && !this.inputIsFocused) value = 'website_url'
      this.slug = value
    },
  },
}
</script>

<style>
.kf-field-identifier {
  button {
    /* The wizard buttons takes to much space and breaks the field layout. Not
    sure why this is happening. */
    height: auto;
  }
}
</style>
