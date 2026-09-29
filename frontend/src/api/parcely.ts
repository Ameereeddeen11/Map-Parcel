import type { BoundingBox, ChybaResponse, ParcelyFeatureCollection } from './types'

const API_BASE_URL = 'http://localhost:8000'

export class ParcelyApiError extends Error {
  readonly status: number

  constructor(message: string, status: number) {
    super(message)
    this.name = 'ParcelyApiError'
    this.status = status
  }
}

export async function fetchParcely(
  bbox: BoundingBox,
  signal?: AbortSignal,
): Promise<ParcelyFeatureCollection> {
  const params = new URLSearchParams({
    jih: String(bbox.jih),
    zapad: String(bbox.zapad),
    sever: String(bbox.sever),
    vychod: String(bbox.vychod),
  })

  const response = await fetch(`${API_BASE_URL}/api/parcely?${params.toString()}`, { signal })

  if (!response.ok) {
    let message = `Parcely se nepodařilo načíst (${response.status}).`
    try {
      const body = (await response.json()) as ChybaResponse
      if (body?.chyba) {
        message = body.chyba
      }
    } catch {
      // tělo odpovědi nebylo platné JSON – ponecháme výchozí zprávu
    }
    throw new ParcelyApiError(message, response.status)
  }

  return (await response.json()) as ParcelyFeatureCollection
}
