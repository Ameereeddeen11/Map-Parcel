import { useMap } from 'react-leaflet'
import { MAX_ZOOM } from '../constants'
import './ZoomHint.css'

interface ZoomHintProps {
  visible: boolean
}

function ZoomHint({ visible }: ZoomHintProps) {
  const map = useMap()

  if (!visible) return null

  const handleZoomIn = () => {
    map.setZoom(Math.min(Math.max(map.getZoom() + 2, 16), MAX_ZOOM))
  }

  return (
    <div className="zoom-hint">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.75" strokeLinecap="round" strokeLinejoin="round" className="zoom-hint-icon" aria-hidden="true">
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.3-4.3" />
        <path d="M11 8v6" />
        <path d="M8 11h6" />
      </svg>
      <div className="zoom-hint-text">
        <span className="zoom-hint-title">Přibližte mapu pro zobrazení parcel</span>
        <span className="zoom-hint-subtitle">
          Oblast je příliš velká na jeden dotaz. Přibližte mapu, nebo klikněte na Přiblížit.
        </span>
      </div>
      <button type="button" className="btn btn-secondary zoom-hint-button" onClick={handleZoomIn}>
        Přiblížit
      </button>
    </div>
  )
}

export default ZoomHint
