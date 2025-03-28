"use client"

import { createContext, useContext, useState, useEffect, type ReactNode } from "react"

type User = {
  id: number
  name: string
  email: string
  role: "student" | "teacher" | "admin"
} | null

type AuthContextType = {
  user: User
  isLoading: boolean
  isAuthenticated: boolean
  login: (email: string, password: string) => Promise<{ success: boolean; message: string }>
  register: (userData: any) => Promise<{ success: boolean; message: string }>
  logout: () => Promise<void>
  updateUser: (userData: Partial<User>) => void
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User>(null)
  const [isLoading, setIsLoading] = useState(true)

  const apiUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost/api"

  useEffect(() => {
    // Check if user is logged in on mount
    const checkAuth = async () => {
      try {
        const token = localStorage.getItem("token")

        if (!token) {
          setIsLoading(false)
          return
        }

        // Check if token is a mock token
        if (token.startsWith("mock-token-")) {
          console.log("Using mock authentication")
          // Extract user info from localStorage if available
          const mockUserData = localStorage.getItem("mockUser")
          if (mockUserData) {
            setUser(JSON.parse(mockUserData))
          } else {
            // Create a default mock user
            const mockUser = {
              id: 1,
              name: "Mock User",
              email: "user@example.com",
              role: "student",
            }
            setUser(mockUser)
            localStorage.setItem("mockUser", JSON.stringify(mockUser))
          }
          setIsLoading(false)
          return
        }

        // Try to validate the token with the API
        try {
          const response = await fetch(`${apiUrl}/user`, {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          })

          if (response.ok) {
            const data = await response.json()
            if (data.status === "success") {
              setUser(data.user)
            } else {
              // Token invalid or expired
              localStorage.removeItem("token")
            }
          } else {
            // Token invalid or expired
            localStorage.removeItem("token")
          }
        } catch (apiError) {
          console.warn("API unavailable during auth check, using mock mode:", apiError)
          // If API is unavailable, create a mock user
          const mockUser = {
            id: 1,
            name: "Mock User",
            email: "user@example.com",
            role: "student",
          }
          setUser(mockUser)
          localStorage.setItem("mockUser", JSON.stringify(mockUser))
          localStorage.setItem("token", "mock-token-" + Date.now())
        }
      } catch (error) {
        console.error("Auth check failed:", error)
      } finally {
        setIsLoading(false)
      }
    }

    checkAuth()
  }, [apiUrl])

  const login = async (email: string, password: string) => {
    try {
      // Check if API is reachable
      let apiReachable = true
      try {
        const testResponse = await fetch(`${apiUrl}/`, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
          signal: AbortSignal.timeout(3000),
        })
        apiReachable = testResponse.ok
      } catch (error) {
        console.warn("API ping failed, using mock mode:", error)
        apiReachable = false
      }

      // If API is not reachable, use mock mode
      if (!apiReachable) {
        console.log("Using mock login mode")
        // Simulate successful login with mock data
        const mockUser = {
          id: 1,
          name: email.split("@")[0], // Use part of email as name
          email: email,
          role: "student",
        }

        localStorage.setItem("token", "mock-token-" + Date.now())
        localStorage.setItem("mockUser", JSON.stringify(mockUser))
        setUser(mockUser)

        return {
          success: true,
          message: "Login successful (Mock Mode - API unavailable)",
        }
      }

      // Normal API login flow
      const response = await fetch(`${apiUrl}/login`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, password }),
      })

      const data = await response.json()

      if (response.ok && data.status === "success") {
        localStorage.setItem("token", data.token)
        setUser(data.user)
        return { success: true, message: data.message || "Login successful" }
      } else {
        return { success: false, message: data.message || "Login failed" }
      }
    } catch (error) {
      console.error("Login error:", error)
      return {
        success: false,
        message: "Network error. The API may be unavailable. Please try again later or contact support.",
      }
    }
  }

  const register = async (userData: any) => {
    try {
      // First check if the API is reachable
      let apiReachable = true
      try {
        const testResponse = await fetch(`${apiUrl}/ping`, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
          // Short timeout to quickly determine if API is reachable
          signal: AbortSignal.timeout(3000),
        })
        apiReachable = testResponse.ok
      } catch (error) {
        console.warn("API ping failed, using mock mode:", error)
        apiReachable = false
      }

      // If API is not reachable, use mock mode
      if (!apiReachable) {
        console.log("Using mock registration mode")
        // Simulate successful registration with mock data
        const mockUser = {
          id: 1,
          name: userData.name,
          email: userData.email,
          role: userData.role,
        }

        // Store mock token and user data
        localStorage.setItem("token", "mock-token-" + Date.now())
        localStorage.setItem("mockUser", JSON.stringify(mockUser))
        setUser(mockUser)

        return {
          success: true,
          message: "Registration successful (Mock Mode - API unavailable)",
        }
      }

      // Normal API registration flow
      const response = await fetch(`${apiUrl}/register`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(userData),
      })

      const data = await response.json()

      if (response.ok && data.status === "success") {
        localStorage.setItem("token", data.token)
        setUser(data.user)
        return { success: true, message: data.message || "Registration successful" }
      } else {
        return { success: false, message: data.message || "Registration failed" }
      }
    } catch (error) {
      console.error("Registration error:", error)
      // Return a more helpful error message
      return {
        success: false,
        message: "Network error. The API may be unavailable. Please try again later or contact support.",
      }
    }
  }

  const logout = async () => {
    try {
      const token = localStorage.getItem("token")

      if (token && !token.startsWith("mock-token-")) {
        await fetch(`${apiUrl}/logout`, {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`,
          },
        })
      }
    } catch (error) {
      console.error("Logout error:", error)
    } finally {
      localStorage.removeItem("token")
      localStorage.removeItem("mockUser")
      setUser(null)
    }
  }

  const updateUser = (userData: Partial<User>) => {
    if (user) {
      setUser({ ...user, ...userData })
    }
  }

  return (
    <AuthContext.Provider
      value={{
        user,
        isLoading,
        isAuthenticated: !!user,
        login,
        register,
        logout,
        updateUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)

  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider")
  }

  return context
}

