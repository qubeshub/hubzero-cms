import React from 'react'
import ReactDOM from 'react-dom'

import App from './components/App/App'

$(document).ready(function () {
    ReactDOM.render(<App />, document.getElementById('live-update-wrapper'))
})

