"use client"

import { useState, useEffect } from "react"
import { AlertTriangle, X } from "lucide-react"

export function NetworkStatusBanner() {
  const [isApiAvailable, setIsApiAvailable] = useState(true)
  const [isVisible, setIsVisible] = useState(false)
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost/api"

  useEffect(() => {
    const checkApiStatus = async () => {
      try {
        const response = await fetch(`${apiUrl}/ping.`, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
          signal: AbortSignal.timeout(3000),
        })

        const data = await response.json()
        setIsApiAvailable(response.ok && data.status === "success")
        setIsVisible(!response.ok || data.status !== "success")
      } catch (error) {
        console.warn("API check failed:", error)
        setIsApiAvailable(false)
        setIsVisible(true)
      }
    }

    checkApiStatus()

    // Check API status periodically
    const intervalId = setInterval(checkApiStatus, 60000) // Check every minute

    return () => clearInterval(intervalId)
  }, [apiUrl])

  if (!isVisible) return null

  return (
    <div className="bg-amber-50 border-b border-amber-200 px-4 py-3">
      <div className="container mx-auto flex items-center justify-between">
        <div className="flex items-center">
          <AlertTriangle className="h-5 w-5 text-amber-500 mr-2" />
          <p className="text-sm text-amber-800">
            <strong>Note:</strong> The backend API is currently unavailable. The application is running in offline mode
            with limited functionality.
          </p>
        </div>
        <button
          onClick={() => setIsVisible(false)}
          className="text-amber-500 hover:text-amber-700"
          aria-label="Dismiss"
        >
          <X className="h-5 w-5" />
        </button>
      </div>
    </div>
  )
}

