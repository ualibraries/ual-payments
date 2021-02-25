import React from 'react'
import ReactDOM from 'react-dom'
import WebFont from 'webfontloader'
import Header from './components/Header'
import './styles/index.css'

// Load fonts
const WebFontConfig = {
  custom: {
    families: ['MiloWeb', 'MiloSerifWeb'],
    urls: [
      'https://cdn.uadigital.arizona.edu/lib/ua-brand-fonts/1.0.0/milo.min.css',
    ],
  },
}

document.addEventListener('DOMContentLoaded', () => {
  WebFont.load(WebFontConfig)
  ReactDOM.render(<Header />, document.getElementById('header'))
})
