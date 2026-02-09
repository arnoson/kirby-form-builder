import { kirbyup } from 'kirbyup/plugin'
import FormIdentifierField from './fields/form-identifier.vue'
import FormIdentifierInput from './components/form-identifier-input.vue'
import FormExport from './components/form-export.vue'
import FormEmailSelect from './components/form-email-select.vue'

window.panel.plugin('arnoson/kirby-form-builder', {
  blocks: kirbyup.import('./previews/*.vue'),
  fields: {
    'form-identifier': FormIdentifierField,
    'form-export': FormExport,
    'form-email-select': FormEmailSelect,
  },
  components: { 'k-form-identifier-input': FormIdentifierInput },
})
