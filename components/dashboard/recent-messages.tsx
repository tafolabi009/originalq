"use client"

import { useState } from "react"
import Image from "next/image"
import Link from "next/link"
import { MoreHorizontal } from "lucide-react"

interface Message {
  id: number
  sender: {
    id: number
    name: string
    image: string
  }
  message: string
  time: string
  isRead: boolean
}

export function RecentMessages() {
  const [messages, setMessages] = useState<Message[]>([
    {
      id: 1,
      sender: {
        id: 101,
        name: "Ahmed Khalid",
        image: "/placeholder.svg?height=100&width=100",
      },
      message: "Assalamu alaikum, are you available for tomorrow's session?",
      time: "12:30",
      isRead: false,
    },
    {
      id: 2,
      sender: {
        id: 102,
        name: "Fatima Noor",
        image: "/placeholder.svg?height=100&width=100",
      },
      message: "Thank you for the lesson! I really enjoyed it.",
      time: "Yesterday",
      isRead: true,
    },
  ])

  const [openMenuId, setOpenMenuId] = useState<number | null>(null)

  const toggleMenu = (id: number) => {
    setOpenMenuId(openMenuId === id ? null : id)
  }

  const markAsRead = (id: number) => {
    setMessages(messages.map((message) => (message.id === id ? { ...message, isRead: true } : message)))
  }

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-100">
      <div className="p-4 border-b border-gray-100 flex justify-between items-center">
        <h3 className="font-medium flex items-center">
          <svg className="h-5 w-5 mr-2 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
            />
          </svg>
          Recent Messages
        </h3>
        <button className="text-gray-500 hover:text-gray-700">
          <MoreHorizontal className="h-5 w-5" />
        </button>
      </div>

      <div className="divide-y divide-gray-100">
        {messages.map((message) => (
          <div
            key={message.id}
            className={`p-4 ${!message.isRead ? "bg-blue-50" : ""}`}
            onClick={() => markAsRead(message.id)}
          >
            <div className="flex items-center mb-2">
              <div className="relative h-10 w-10 rounded-full overflow-hidden mr-3">
                <Image
                  src={message.sender.image || "/placeholder.svg"}
                  alt={message.sender.name}
                  fill
                  className="object-cover"
                />
              </div>
              <div className="flex-1">
                <div className="flex justify-between items-center">
                  <h4 className="font-medium">{message.sender.name}</h4>
                  <span className="text-xs text-gray-500">{message.time}</span>
                </div>
                <p className="text-sm text-gray-600 truncate">{message.message}</p>
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="p-3 border-t border-gray-100 text-center">
        <Link href="/messages" className="text-sm text-teal-600 hover:underline">
          View All Messages
        </Link>
      </div>
    </div>
  )
}

