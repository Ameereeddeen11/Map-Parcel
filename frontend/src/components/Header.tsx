import './Header.css'

interface HeaderProps {
  selectedKu: string | null
}

function Header({ selectedKu }: HeaderProps) {
  return (
    <header className="header">
      <div className="header-brand">
        <span className="header-brand-name">Katastr</span>
        <span className="header-brand-sub">okres Jičín</span>
      </div>

      <div className="header-search">
        <svg
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2.75"
          strokeLinecap="round"
          strokeLinejoin="round"
          className="header-search-icon"
          aria-hidden="true"
        >
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.3-4.3" />
        </svg>
        <input
          className="input header-search-input"
          type="text"
          placeholder="Hledat katastrální území"
          disabled
          title="Vyhledávání zatím není dostupné"
        />
      </div>

      <div className="header-breadcrumb">
        <span>Královéhradecký kraj</span>
        <span>/</span>
        <span>okres Jičín</span>
        <span>/</span>
        <span className="header-breadcrumb-current">
          {selectedKu ? `k.ú. ${selectedKu}` : 'mapa parcel'}
        </span>
      </div>
    </header>
  )
}

export default Header
