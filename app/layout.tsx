import type React from "react"
import type { Metadata } from "next"
import { Inter } from "next/font/google"
import "./globals.css"
import { AuthProvider } from "@/contexts/AuthContext"
import { NetworkStatusBanner } from "@/components/ui/network-status-banner"

const inter = Inter({ subsets: ["latin"] })

export const metadata: Metadata = {
  title: "IqraPath - Connect with Expert Quran Teachers",
  description: "Find expert Quran tutors for kids and adults. Learn at your own pace, anytime, anywhere.",
    generator: 'v0.dev'
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="en">
      <body className={inter.className}>
        <AuthProvider>
          <NetworkStatusBanner />
          {children}
        </AuthProvider>
      </body>
    </html>
  )
}



import './globals.css'