"use client"

import { useState, useEffect } from "react"
import Link from "next/link"
import Image from "next/image"
import { Menu, X } from "lucide-react"
import { cn } from "@/lib/utils"
import { usePathname } from "next/navigation"

export function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const pathname = usePathname()

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 10)
    }

    window.addEventListener("scroll", handleScroll)
    return () => window.removeEventListener("scroll", handleScroll)
  }, [])

  // Close mobile menu when route changes
  useEffect(() => {
    setIsMenuOpen(false)
  }, [pathname])

  return (
    <header
      className={cn(
        "sticky top-0 z-50 w-full transition-all duration-300",
        scrolled ? "bg-white shadow-md" : "bg-[#FFF8E7]",
      )}
    >
      <div className="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6">
        <div className="flex items-center gap-4 md:gap-8">
          <button
            className="md:hidden p-2 rounded-md hover:bg-teal-50 transition-colors"
            onClick={() => setIsMenuOpen(!isMenuOpen)}
            aria-label={isMenuOpen ? "Close menu" : "Open menu"}
            aria-expanded={isMenuOpen}
            aria-controls="mobile-menu"
          >
            {isMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
          </button>

          <Link href="/" className="flex items-center gap-2">
            <Image src="/logo.svg" alt="IqraPath" width={140} height={40} priority className="w-32 sm:w-36 md:w-40" />
          </Link>
        </div>

        <nav className="hidden md:flex items-center gap-4 lg:gap-8">
          <Link
            href="/"
            className={cn(
              "text-sm font-medium transition-colors hover:text-[#0D9488]",
              pathname === "/" ? "text-[#0D9488] border-b-2 border-[#0D9488]" : "text-gray-700",
            )}
          >
            Home
          </Link>
          <Link
            href="/find-teacher"
            className={cn(
              "text-sm font-medium transition-colors hover:text-[#0D9488]",
              pathname === "/find-teacher" ? "text-[#0D9488] border-b-2 border-[#0D9488]" : "text-gray-700",
            )}
          >
            Find a Teacher
          </Link>
          <Link
            href="/how-it-works"
            className={cn(
              "text-sm font-medium transition-colors hover:text-[#0D9488]",
              pathname === "/how-it-works" ? "text-[#0D9488] border-b-2 border-[#0D9488]" : "text-gray-700",
            )}
          >
            How It Works
          </Link>
          <Link
            href="/blog"
            className={cn(
              "text-sm font-medium transition-colors hover:text-[#0D9488]",
              pathname === "/blog" ? "text-[#0D9488] border-b-2 border-[#0D9488]" : "text-gray-700",
            )}
          >
            Blog
          </Link>
          <Link
            href="/about"
            className={cn(
              "text-sm font-medium transition-colors hover:text-[#0D9488]",
              pathname === "/about" ? "text-[#0D9488] border-b-2 border-[#0D9488]" : "text-gray-700",
            )}
          >
            About
          </Link>
        </nav>

        <div className="flex items-center gap-2 sm:gap-4">
          <Link
            href="/auth/login"
            className="hidden sm:inline-block border border-[#0D9488] text-[#0D9488] px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-sm font-medium hover:bg-teal-50 transition-colors"
          >
            Sign In
          </Link>
          <Link
            href="/auth/register"
            className="bg-[#0D9488] text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-sm font-medium hover:bg-teal-700 transition-colors"
          >
            Sign Up
          </Link>
        </div>
      </div>

      {/* Mobile menu with animation */}
      <div
        id="mobile-menu"
        className={cn(
          "md:hidden absolute w-full bg-white border-b border-gray-200 shadow-lg transition-all duration-300 ease-in-out",
          isMenuOpen ? "max-h-[500px] opacity-100 animate-slide-up" : "max-h-0 opacity-0 invisible",
        )}
      >
        <nav className="flex flex-col p-4 space-y-3">
          <Link
            href="/"
            className={cn(
              "px-3 py-2 text-sm font-medium rounded-md transition-colors",
              pathname === "/" ? "text-[#0D9488] bg-teal-50" : "text-gray-700 hover:bg-teal-50",
            )}
          >
            Home
          </Link>
          <Link
            href="/find-teacher"
            className={cn(
              "px-3 py-2 text-sm font-medium rounded-md transition-colors",
              pathname === "/find-teacher" ? "text-[#0D9488] bg-teal-50" : "text-gray-700 hover:bg-teal-50",
            )}
          >
            Find a Teacher
          </Link>
          <Link
            href="/how-it-works"
            className={cn(
              "px-3 py-2 text-sm font-medium rounded-md transition-colors",
              pathname === "/how-it-works" ? "text-[#0D9488] bg-teal-50" : "text-gray-700 hover:bg-teal-50",
            )}
          >
            How It Works
          </Link>
          <Link
            href="/blog"
            className={cn(
              "px-3 py-2 text-sm font-medium rounded-md transition-colors",
              pathname === "/blog" ? "text-[#0D9488] bg-teal-50" : "text-gray-700 hover:bg-teal-50",
            )}
          >
            Blog
          </Link>
          <Link
            href="/about"
            className={cn(
              "px-3 py-2 text-sm font-medium rounded-md transition-colors",
              pathname === "/about" ? "text-[#0D9488] bg-teal-50" : "text-gray-700 hover:bg-teal-50",
            )}
          >
            About
          </Link>
          <div className="border-t border-gray-200 my-2"></div>
          <Link
            href="/auth/login"
            className="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-teal-50 rounded-md transition-colors"
          >
            Sign In
          </Link>
          <Link
            href="/auth/register"
            className="px-3 py-2 text-sm font-medium bg-teal-600 text-white rounded-md transition-colors"
          >
            Sign Up
          </Link>
        </nav>
      </div>
    </header>
  )
}

