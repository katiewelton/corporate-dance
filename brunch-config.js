module.exports = {
  files: {
    javascripts: {
      joinTo: {
        'js/vendor.js': /^(vendor\/javascripts)/,
        'js/app.js': /^(app\/javascripts)/
      }
    },
    stylesheets: {
      joinTo: {
        'css/vendor.css': /^(vendor\/stylesheets)/,
        'css/app.css': 'app/stylesheets/styles.scss'
      }
    }
  },
  plugins: {
    postcss: {
      processors: [
        require('autoprefixer')(['last 5 versions']),
      ]
    },
    sass: {
      debug: 'comments',
      mode: 'native'
    }
  },
  modules: {
    wrapper: false,
    definition: false
  },
  npm: {
    enabled: false
  }
};
