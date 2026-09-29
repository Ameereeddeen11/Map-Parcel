import Header from './components/Header'
import MapView from './components/MapView'
import './App.css'

function App() {
  return (
    <div className="app">
      <Header selectedKu={null} />
      <MapView />
    </div>
  )
}

export default App
