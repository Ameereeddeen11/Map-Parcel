import './LoadingIndicator.css'

interface LoadingIndicatorProps {
  loading: boolean
  error: string | null
}

function LoadingIndicator({ loading, error }: LoadingIndicatorProps) {
  if (!loading && !error) return null

  return (
    <div className={`status-pill${error ? ' status-pill-error' : ''}`}>
      {error ? (
        <span>{error}</span>
      ) : (
        <>
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2.75"
            strokeLinecap="round"
            className="status-pill-spinner"
            aria-hidden="true"
          >
            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
          </svg>
          <span>Načítám parcely…</span>
        </>
      )}
    </div>
  )
}

export default LoadingIndicator
