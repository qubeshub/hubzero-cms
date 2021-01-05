import React from 'react'

import './Filter.scss'

const Filter = () => {

    return (
        <div className='page-filter-wrapper'>
            <div className='page-filter'>
                <input type='text' placeholder='Enter keyword or phrase' />
                <h5>Heading 1</h5>
                <ul>
                    <li>Filter 1</li>
                    <li>Filter 2</li>
                </ul>
                <h5>Heading 2</h5>
                <ul>
                    <li>Filter 1</li>
                    <li>Filter 2</li>
                </ul>
            </div>
        </div>
    )
}

export default Filter