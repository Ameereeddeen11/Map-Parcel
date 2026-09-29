import { useState } from 'react'
import type { ParcelaProperties } from './api/types'
import Header from './components/Header'
import MapView from './components/MapView'
import './App.css'

function App() {
  const [selected, setSelected] = useState<ParcelaProperties | null>(null)

  return (
    <div className="app">
      <Header selectedKu={selected?.katastralniUzemi ?? null} />
      <MapView selected={selected} onSelectedChange={setSelected} />
    </div>
  )
}

export default App
