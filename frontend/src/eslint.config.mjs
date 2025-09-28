// @ts-check
import prettier from 'eslint-config-prettier'
import prettierPlugin from 'eslint-plugin-prettier'
import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt(
  // Prettier integration for flat config
  {
    plugins: {
      prettier: prettierPlugin,
    },
    rules: {
      'prettier/prettier': 'error',
    },
  },
  prettier
)
