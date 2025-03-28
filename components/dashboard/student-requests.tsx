"use client"

import { useState } from "react"
import Image from "next/image"
import { MoreHorizontal } from "lucide-react"

interface StudentRequest {
  id: number
  name: string
  image: string
  subject: string
  day: string
  time: string
}

export function StudentRequests() {
  const [requests, setRequests] = useState<StudentRequest[]>([
    {
      id: 1,
      name: "Zainab Ali",
      image: "/placeholder.svg?height=100&width=100",
      subject: "Tajweed",
      day: "Mon",
      time: "3:00 PM - 4:00 PM",
    },
    {
      id: 2,
      name: "Zainab Ali",
      image: "/placeholder.svg?height=100&width=100",
      subject: "Tajweed",
      day: "Tue",
      time: "3:00 PM - 4:00 PM",
    },
  ])

  const [openMenuId, setOpenMenuId] = useState<number | null>(null)

  const handleAccept = (id: number) => {
    // In a real implementation, you would call your API
    console.log(`Accepted request ${id}`)
    // Remove the request from the list
    setRequests(requests.filter((request) => request.id !== id))
  }

  const handleDecline = (id: number) => {
    // In a real implementation, you would call your API
    console.log(`Declined request ${id}`)
    // Remove the request from the list
    setRequests(requests.filter((request) => request.id !== id))
  }

  const toggleMenu = (id: number) => {
    setOpenMenuId(openMenuId === id ? null : id)
  }

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-100">
      <div className="p-4 border-b border-gray-100">
        <h3 className="font-medium flex items-center">
          <svg className="h-5 w-5 mr-2 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            />
          </svg>
          New Request
        </h3>
      </div>

      <div className="divide-y divide-gray-100">
        {requests.length > 0 ? (
          requests.map((request) => (
            <div key={request.id} className="p-4">
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center">
                  <div className="relative h-10 w-10 rounded-full overflow-hidden mr-3">
                    <Image src={request.image || "/placeholder.svg"} alt={request.name} fill className="object-cover" />
                  </div>
                  <div>
                    <h4 className="font-medium">{request.name}</h4>
                    <p className="text-xs text-gray-500">Looking for a {request.subject} teacher</p>
                  </div>
                </div>

                <div className="relative">
                  <button onClick={() => toggleMenu(request.id)} className="text-gray-500 hover:text-gray-700">
                    <MoreHorizontal className="h-5 w-5" />
                  </button>

                  {openMenuId === request.id && (
                    <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-100">
                      <div className="py-1">
                        <button
                          onClick={() => handleAccept(request.id)}
                          className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                          View Details
                        </button>
                        <button
                          onClick={() => handleDecline(request.id)}
                          className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                          Block Student
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              </div>

              <div className="grid grid-cols-2 gap-2 mb-3">
                <div>
                  <p className="text-xs text-gray-500">Subject</p>
                  <p className="text-sm">{request.subject}</p>
                </div>
                <div>
                  <p className="text-xs text-gray-500">Preferred Day</p>
                  <p className="text-sm">{request.day}</p>
                </div>
                <div>
                  <p className="text-xs text-gray-500">Preferred Time</p>
                  <p className="text-sm">{request.time}</p>
                </div>
              </div>

              <div className="flex space-x-2">
                <button
                  onClick={() => handleAccept(request.id)}
                  className="flex-1 px-3 py-1 bg-teal-600 text-white text-sm rounded-md hover:bg-teal-700"
                >
                  Accept
                </button>
                <button
                  onClick={() => handleDecline(request.id)}
                  className="flex-1 px-3 py-1 border border-gray-300 text-sm rounded-md hover:bg-gray-50"
                >
                  Decline
                </button>
              </div>
            </div>
          ))
        ) : (
          <div className="p-4 text-center text-gray-500">No new requests at this time.</div>
        )}
      </div>
    </div>
  )
}

