import { useState } from 'react'
import { MapContainer, TileLayer } from 'react-leaflet'
import type { ParcelaProperties, ParcelyFeatureCollection } from '../api/types'
import { INITIAL_ZOOM, JICIN_CENTER, MAX_ZOOM, MIN_ZOOM } from '../constants'
import DetailPanel from './DetailPanel'
import LoadingIndicator from './LoadingIndicator'
import MapDataController from './MapDataController'
import ParcelLayer from './ParcelLayer'
import ZoomControls from './ZoomControls'
import ZoomHint from './ZoomHint'
import './MapView.css'

interface MapViewProps {
  selected: ParcelaProperties | null
  onSelectedChange: (properties: ParcelaProperties | null) => void
}

function MapView({ selected, onSelectedChange }: MapViewProps) {
  const [data, setData] = useState<ParcelyFeatureCollection | null>(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [tooLarge, setTooLarge] = useState(false)

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
        <MapDataController
          onDataChange={setData}
          onLoadingChange={setLoading}
          onErrorChange={setError}
          onTooLargeChange={setTooLarge}
        />
        {data && (
          <ParcelLayer data={data} selectedId={selected?.id ?? null} onSelect={onSelectedChange} />
        )}
        <ZoomControls />
        <ZoomHint visible={tooLarge} />
      </MapContainer>
      <LoadingIndicator loading={loading} error={error} />
      <DetailPanel key={selected?.id ?? 'none'} parcela={selected} onClose={() => onSelectedChange(null)} />
    </div>
  )
}

export default MapView
