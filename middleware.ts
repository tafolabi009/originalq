import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"

export function middleware(request: NextRequest) {
  const token = request.cookies.get("token")?.value
  const isAuthPage = request.nextUrl.pathname.startsWith("/auth")
  const isRegisterPage = request.nextUrl.pathname.startsWith("/register")
  const isDashboardPage = request.nextUrl.pathname.startsWith("/dashboard")
  const isProfilePage = request.nextUrl.pathname.startsWith("/profile")

  // If trying to access protected page without token, redirect to login
  if ((isDashboardPage || isProfilePage) && !token) {
    return NextResponse.redirect(new URL("/auth/login", request.url))
  }

  // If trying to access auth pages with token, redirect to dashboard
  if ((isAuthPage || isRegisterPage) && token) {
    return NextResponse.redirect(new URL("/dashboard", request.url))
  }

  return NextResponse.next()
}

export const config = {
  matcher: ["/dashboard/:path*", "/profile/:path*", "/auth/:path*", "/register/:path*"],
}

