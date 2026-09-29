import { useMap } from 'react-leaflet'
import './ZoomControls.css'

function ZoomControls() {
  const map = useMap()

  return (
    <div className="zoom-controls">
      <button
        type="button"
        className="btn btn-ghost btn-icon"
        aria-label="Přiblížit"
        onClick={() => map.zoomIn()}
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.75" strokeLinecap="round">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
      </button>
      <button
        type="button"
        className="btn btn-ghost btn-icon"
        aria-label="Oddálit"
        onClick={() => map.zoomOut()}
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.75" strokeLinecap="round">
          <path d="M5 12h14" />
        </svg>
      </button>
    </div>
  )
}

export default ZoomControls
