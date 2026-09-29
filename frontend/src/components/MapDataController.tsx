import { useCallback, useEffect, useRef } from 'react'
import { useMap, useMapEvents } from 'react-leaflet'
import { jeOblastPrilisVelka } from '../api/bboxLimit'
import { fetchParcely, ParcelyApiError } from '../api/parcely'
import type { ParcelyFeatureCollection } from '../api/types'
import { boundsToBbox } from '../utils/mapBounds'

const DEBOUNCE_MS = 350

function isAbortError(err: unknown): boolean {
  return typeof err === 'object' && err !== null && (err as { name?: string }).name === 'AbortError'
}

interface MapDataControllerProps {
  onDataChange: (data: ParcelyFeatureCollection | null) => void
  onLoadingChange: (loading: boolean) => void
  onErrorChange: (error: string | null) => void
  onTooLargeChange: (tooLarge: boolean) => void
}

function MapDataController({
  onDataChange,
  onLoadingChange,
  onErrorChange,
  onTooLargeChange,
}: MapDataControllerProps) {
  const map = useMap()
  const abortRef = useRef<AbortController | null>(null)
  const debounceRef = useRef<number | null>(null)

  const load = useCallback(() => {
    const bbox = boundsToBbox(map.getBounds())

    abortRef.current?.abort()

    // Oblast je odhadem příliš velká na limit dlaždic backendu – nevolat
    // API vůbec, jen ukázat "přibliž se" stav.
    if (jeOblastPrilisVelka(bbox)) {
      onTooLargeChange(true)
      onErrorChange(null)
      onLoadingChange(false)
      onDataChange(null)
      return
    }

    const controller = new AbortController()
    abortRef.current = controller

    onTooLargeChange(false)
    onErrorChange(null)
    onLoadingChange(true)

    fetchParcely(bbox, controller.signal)
      .then((result) => {
        onDataChange(result)
      })
      .catch((err: unknown) => {
        if (isAbortError(err)) return
        if (err instanceof ParcelyApiError && err.status === 422) {
          // Odhad byl u hranice a backend přesto dotaz odmítl – stejná
          // reakce jako preventivní kontrola výše.
          onTooLargeChange(true)
          onDataChange(null)
          return
        }
        const message = err instanceof ParcelyApiError ? err.message : 'Parcely se nepodařilo načíst.'
        onErrorChange(message)
      })
      .finally(() => {
        if (abortRef.current === controller) {
          onLoadingChange(false)
        }
      })
  }, [map, onDataChange, onErrorChange, onLoadingChange, onTooLargeChange])

  useEffect(() => {
    load()
    return () => {
      abortRef.current?.abort()
      if (debounceRef.current) window.clearTimeout(debounceRef.current)
    }
    // Pouze počáteční načtení – další volání řídí moveend níže.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useMapEvents({
    moveend: () => {
      if (debounceRef.current) window.clearTimeout(debounceRef.current)
      debounceRef.current = window.setTimeout(load, DEBOUNCE_MS)
    },
  })

  return null
}

export default MapDataController
