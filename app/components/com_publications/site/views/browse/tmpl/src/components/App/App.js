import React, { useState, useEffect } from 'react'
import './App.scss'

import Filter from '../Filter/Filter'
import Results from '../Results/Results'

function App() {

  const [pubs, setPubs] = useState([]);

    useEffect(() => {
        fetch('/api/publications/list')
            .then(response => response.json())
            .then(data => {
                setPubs(data.publications)
            })
    }, [])
  
  
  return (
    <div className="browse-resources-wrapper">
      <Filter />
      <Results publications={pubs} />
    </div>
  );
}

export default App;
