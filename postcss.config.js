module.exports = {
  plugins: {
    stylelint: {
      configFile: 'stylelint.config.js',
      ignoreFiles: 'node_modules/**'
    },
    'postcss-import': {},
    'postcss-cssnext': {},
    'postcss-reporter': { clearMessages: true }
  }
}
