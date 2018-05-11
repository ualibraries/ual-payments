var Encore = require('@symfony/webpack-encore')

Encore
  // the project directory where all compiled assets will be stored
  .setOutputPath('public/build/')

  // the public path used by the web server to access the previous directory
  .setPublicPath('/build')

  // will create public/build/app.js and public/build/app.css
  .addEntry('app', './assets/app.js')
  .addEntry('polyfill', './assets/polyfill.js')
  .addEntry('total-selected-charges', './assets/total-selected-charges.js')

  .configureBabel(babelConfig => {
    babelConfig.presets.push('stage-0')
    babelConfig.presets.push('react')
  })

  .enablePostCssLoader()

  // enable source maps during development
  .enableSourceMaps(!Encore.isProduction())

  // empty the outputPath dir before each build
  .cleanupOutputBeforeBuild()

  // show OS notifications when builds finish/fail
  .enableBuildNotifications()

// export the final configuration
module.exports = Encore.getWebpackConfig()
