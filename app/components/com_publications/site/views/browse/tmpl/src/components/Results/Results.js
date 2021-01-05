import React, { useState, useEffect } from 'react'

import './Results.scss'
import Card from '../Card/Card'

const Results = (props) => {
    const resource = props.publications.map(item => <Card pubInfo={item} />)
    return (
        <div id='results-container' className='card-container'>
            {resource}
        </div>
    )
}

export default Results