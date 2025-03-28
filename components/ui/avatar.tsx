import Image from "next/image"
import { cn } from "@/lib/utils"
import { User } from "lucide-react"

interface AvatarProps {
  src?: string
  alt?: string
  size?: "sm" | "md" | "lg" | "xl"
  className?: string
}

export function Avatar({ src, alt = "Avatar", size = "md", className }: AvatarProps) {
  const sizes = {
    sm: "h-8 w-8",
    md: "h-10 w-10",
    lg: "h-12 w-12",
    xl: "h-16 w-16",
  }

  return (
    <div className={cn("relative rounded-full overflow-hidden bg-gray-200", sizes[size], className)}>
      {src ? (
        <Image src={src || "/placeholder.svg"} alt={alt} fill className="object-cover" />
      ) : (
        <div className="flex items-center justify-center h-full w-full bg-gray-200 text-gray-500">
          <User
            className={cn(
              size === "sm" && "h-4 w-4",
              size === "md" && "h-5 w-5",
              size === "lg" && "h-6 w-6",
              size === "xl" && "h-8 w-8",
            )}
          />
        </div>
      )}
    </div>
  )
}

