const cache = new Map<string, string>()

/** Čte hodnotu CSS proměnné z :root, aby styly mimo CSS (např. Leaflet path options) používaly stejné tokeny jako zbytek designu. */
export function cssVar(name: string): string {
  const cached = cache.get(name)
  if (cached) return cached

  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
  cache.set(name, value)
  return value
}
