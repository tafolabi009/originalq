import Link from "next/link"
import Image from "next/image"

interface LogoProps {
  variant?: "default" | "white"
  className?: string
}

export function Logo({ variant = "default", className = "" }: LogoProps) {
  const src = variant === "white" ? "/logo-white.svg" : "/logo.svg"

  return (
    <Link href="/" className={`flex items-center ${className}`}>
      <Image src={src || "/placeholder.svg"} alt="IqraPath" width={140} height={40} priority />
    </Link>
  )
}

