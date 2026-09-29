import { useState } from 'react'
import type { ParcelaProperties } from '../api/types'
import './DetailPanel.css'

interface DetailPanelProps {
  parcela: ParcelaProperties | null
  onClose: () => void
}

const plocha = new Intl.NumberFormat('cs-CZ')

function druhParcely(cislo: string): string {
  return cislo.startsWith('st.') ? 'Stavební parcela' : 'Pozemková parcela'
}

// key={parcela.id} v MapView zajistí remount při přepnutí parcely, takže
// se lokální stav (copied) sám vynuluje bez efektu.
function DetailPanel({ parcela, onClose }: DetailPanelProps) {
  const [copied, setCopied] = useState(false)

  if (!parcela) return null

  const handleCopy = () => {
    navigator.clipboard?.writeText(parcela.cislo).catch(() => {})
    setCopied(true)
    window.setTimeout(() => setCopied(false), 1500)
  }

  return (
    <aside className="detail-panel">
      <div className="detail-panel-sheet-handle" aria-hidden="true" />
      <div className="detail-panel-top">
        <span className="tag tag-accent-2">{druhParcely(parcela.cislo)}</span>
        <button type="button" className="btn btn-ghost btn-icon" aria-label="Zavřít" onClick={onClose}>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.75" strokeLinecap="round">
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
          </svg>
        </button>
      </div>

      <div className="detail-panel-heading">
        <span className="detail-panel-label">Parcelní číslo</span>
        <span className="detail-panel-number">{parcela.cislo}</span>
      </div>

      <dl className="detail-panel-facts">
        <dt>Katastrální území</dt>
        <dd>{parcela.katastralniUzemi}</dd>
        <dt>Výměra</dt>
        <dd>{plocha.format(Math.round(parcela.vymeraM2))} m²</dd>
      </dl>

      <div className="detail-panel-actions">
        <a
          className="btn btn-primary detail-panel-link"
          href="https://nahlizenidokn.cuzk.cz/"
          target="_blank"
          rel="noopener"
        >
          Nahlížení do KN
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.75" strokeLinecap="round" strokeLinejoin="round">
            <path d="M15 3h6v6" />
            <path d="M10 14 21 3" />
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
          </svg>
        </a>
        <button
          type="button"
          className="btn btn-secondary btn-icon"
          aria-label="Kopírovat parcelní číslo"
          title={copied ? 'Zkopírováno' : 'Kopírovat parcelní číslo'}
          onClick={handleCopy}
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.75" strokeLinecap="round" strokeLinejoin="round">
            <rect width="14" height="14" x="8" y="8" rx="2" />
            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
          </svg>
        </button>
      </div>
    </aside>
  )
}

export default DetailPanel
