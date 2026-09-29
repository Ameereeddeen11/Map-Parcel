import { MapContainer, TileLayer } from 'react-leaflet'
import { INITIAL_ZOOM, JICIN_CENTER, MAX_ZOOM, MIN_ZOOM } from '../constants'
import ZoomControls from './ZoomControls'
import './MapView.css'

function MapView() {
  return (
    <div className="map-view">
      <MapContainer
        center={JICIN_CENTER}
        zoom={INITIAL_ZOOM}
        minZoom={MIN_ZOOM}
        maxZoom={MAX_ZOOM}
        zoomControl={false}
        preferCanvas
        className="map-view-container"
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> přispěvatelé'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        <ZoomControls />
      </MapContainer>
    </div>
  )
}

export default MapView
