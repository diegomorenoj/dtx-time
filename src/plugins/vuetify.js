// Vuetify Documentation https://vuetifyjs.com

import Vue from 'vue'
import Vuetify from 'vuetify/lib/framework'
import i18n from '@/i18n'
import ripple from 'vuetify/lib/directives/ripple'

Vue.use(Vuetify, { directives: { ripple } })

const theme = {
  primary: '#9C27b0',
  secondary: '#9C27b0',
  accent: '#9C27b0',
  info: '#00CAE3',
  success: '#4CAF50',
  warning: '#FB8C00',
  error: '#FF5252',
}

export default new Vuetify({
  breakpoint: { mobileBreakpoint: 960 },
  icons: {
    values: { expand: 'mdi-menu-down' },
  },
  lang: { t: (key, ...params) => i18n.t(key, params) },
  theme: {
    themes: {
      dark: theme,
      light: theme,
    },
  },
})
