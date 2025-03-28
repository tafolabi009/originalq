"use client"
import Link from "next/link"
import { usePathname } from "next/navigation"
import {
  LayoutDashboard,
  Calendar,
  MessageSquare,
  Users,
  DollarSign,
  UserCircle,
  Star,
  Settings,
  Bell,
  LogOut,
  X,
} from "lucide-react"
import { cn } from "@/lib/utils"
import { useAuth } from "@/contexts/AuthContext"
import { motion } from "framer-motion"

export function Sidebar({ onClose }) {
  const pathname = usePathname()
  const { logout } = useAuth()

  const menuItems = [
    {
      title: "MAIN",
      items: [
        {
          name: "Dashboard",
          href: "/dashboard",
          icon: <LayoutDashboard className="h-5 w-5" />,
        },
        {
          name: "Schedule",
          href: "/schedule",
          icon: <Calendar className="h-5 w-5" />,
        },
        {
          name: "Requests",
          href: "/requests",
          icon: <Users className="h-5 w-5" />,
        },
        {
          name: "Earnings",
          href: "/earnings",
          icon: <DollarSign className="h-5 w-5" />,
        },
        {
          name: "Messages",
          href: "/messages",
          icon: <MessageSquare className="h-5 w-5" />,
        },
      ],
    },
    {
      title: "ACCOUNT",
      items: [
        {
          name: "Profile",
          href: "/profile",
          icon: <UserCircle className="h-5 w-5" />,
        },
        {
          name: "Rating & Feedback",
          href: "/feedback",
          icon: <Star className="h-5 w-5" />,
        },
        {
          name: "Settings",
          href: "/settings",
          icon: <Settings className="h-5 w-5" />,
        },
        {
          name: "Notification",
          href: "/notifications",
          icon: <Bell className="h-5 w-5" />,
        },
      ],
    },
  ]

  const handleLogout = async () => {
    await logout()
    window.location.href = "/"
  }

  return (
    <div className="w-64 bg-[#0D6A61] text-white flex flex-col h-full relative">
      {/* Close button for mobile */}
      <button
        onClick={onClose}
        className="md:hidden absolute top-4 right-4 text-white hover:text-gray-200 transition-colors"
        aria-label="Close sidebar"
      >
        <X className="h-5 w-5" />
      </button>

      <div className="p-4 border-b border-teal-700">
        <h2 className="text-lg font-bold">Teacher Dashboard</h2>
      </div>

      <div className="flex-1 overflow-y-auto py-4">
        {menuItems.map((section, i) => (
          <div key={i} className="mb-6">
            <div className="px-4 mb-2">
              <p className="text-xs font-medium text-teal-300">{section.title}</p>
            </div>
            <ul>
              {section.items.map((item, j) => {
                const isActive = pathname === item.href

                return (
                  <motion.li key={j} whileHover={{ x: 5 }} transition={{ duration: 0.2 }}>
                    <Link
                      href={item.href}
                      className={cn(
                        "flex items-center px-4 py-2 text-sm transition-colors",
                        isActive ? "bg-teal-700 text-white font-medium" : "text-teal-100 hover:bg-teal-700/50",
                      )}
                      onClick={onClose}
                    >
                      <span className="mr-3">{item.icon}</span>
                      {item.name}
                      {item.name === "Dashboard" && (
                        <span className="ml-auto bg-yellow-500 text-xs rounded-full h-5 w-5 flex items-center justify-center">
                          1
                        </span>
                      )}
                    </Link>
                  </motion.li>
                )
              })}
            </ul>
          </div>
        ))}
      </div>

      <div className="p-4 border-t border-teal-700">
        <motion.button
          onClick={handleLogout}
          className="flex items-center text-teal-100 hover:text-white w-full px-4 py-2 text-sm transition-colors"
          whileHover={{ x: 5 }}
          transition={{ duration: 0.2 }}
        >
          <LogOut className="h-5 w-5 mr-3" />
          Log out
        </motion.button>
      </div>
    </div>
  )
}

