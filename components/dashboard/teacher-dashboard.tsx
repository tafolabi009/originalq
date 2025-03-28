"use client"

import { useState, useEffect } from "react"
import { Sidebar } from "./sidebar"
import { StudentRequests } from "./student-requests"
import { RecentMessages } from "./recent-messages"
import { VerificationSuccessModal } from "./verification-success-modal"
import { Calendar, Users, Clock, Menu } from "lucide-react"
import Link from "next/link"
import Image from "next/image"
import { motion, AnimatePresence } from "framer-motion"

export function TeacherDashboard({ user }) {
  const [showVerificationModal, setShowVerificationModal] = useState(false)
  const [activeMonth, setActiveMonth] = useState("March")
  const [activeYear, setActiveYear] = useState(2025)
  const [showSidebar, setShowSidebar] = useState(false)

  useEffect(() => {
    // Show verification modal after a short delay
    const timer = setTimeout(() => {
      setShowVerificationModal(true)
    }, 1000)

    return () => clearTimeout(timer)
  }, [])

  // Close sidebar when window is resized to desktop
  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth >= 768) {
        setShowSidebar(false)
      }
    }

    window.addEventListener("resize", handleResize)
    return () => window.removeEventListener("resize", handleResize)
  }, [])

  const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ]

  const upcomingSessions = [
    {
      id: 1,
      studentName: "Ahmed Khalid",
      day: 5,
      time: "3:00 PM - 4:00 PM",
    },
    {
      id: 2,
      studentName: "Sara Malik",
      day: 6,
      time: "5:00 PM - 6:00 PM",
    },
    {
      id: 3,
      studentName: "Ibrahim Yusuf",
      day: 9,
      time: "4:00 PM - 5:00 PM",
    },
  ]

  const recommendedStudents = [
    {
      id: 1,
      name: "Muhammad Usman",
      image: "/placeholder.svg?height=100&width=100",
      subject: "Tajweed",
      day: "Mon & Wed",
      time: "11:00 AM - 12:00 PM",
      rate: "$25",
    },
    {
      id: 2,
      name: "Muhammad Usman",
      image: "/placeholder.svg?height=100&width=100",
      subject: "Hifz",
      day: "Tue & Thu",
      time: "3:00 PM - 4:00 PM",
      rate: "$30",
    },
    {
      id: 3,
      name: "Muhammad Usman",
      image: "/placeholder.svg?height=100&width=100",
      subject: "Tajweed",
      day: "Sat",
      time: "10:00 AM - 11:00 AM",
      rate: "$20",
    },
  ]

  return (
    <div className="flex h-screen bg-gray-50 relative">
      {/* Mobile menu button */}
      <button
        className="md:hidden fixed top-4 left-4 z-50 bg-teal-600 text-white p-2 rounded-md shadow-md"
        onClick={() => setShowSidebar(!showSidebar)}
      >
        <Menu className="h-5 w-5" />
      </button>

      {/* Sidebar with animation for mobile */}
      <AnimatePresence>
        {(showSidebar || window.innerWidth >= 768) && (
          <motion.div
            initial={{ x: -300 }}
            animate={{ x: 0 }}
            exit={{ x: -300 }}
            transition={{ duration: 0.3 }}
            className={`fixed md:relative z-40 h-full ${showSidebar ? "block" : "hidden md:block"}`}
          >
            <Sidebar onClose={() => setShowSidebar(false)} />
          </motion.div>
        )}
      </AnimatePresence>

      {/* Overlay for mobile sidebar */}
      {showSidebar && (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden" onClick={() => setShowSidebar(false)} />
      )}

      <main className="flex-1 p-4 sm:p-6 overflow-auto pt-16 md:pt-6">
        <div className="max-w-7xl mx-auto">
          <motion.div
            className="mb-8"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
          >
            <h1 className="text-2xl font-bold">Welcome {user?.name || "Abdullah"}</h1>
          </motion.div>

          <motion.div
            className="mb-8"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.1 }}
          >
            <h2 className="text-xl font-semibold mb-4">Your Stats</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
              <motion.div
                className="bg-white p-4 rounded-lg shadow-sm border border-gray-100"
                whileHover={{ y: -5, boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)" }}
                transition={{ duration: 0.2 }}
              >
                <div className="flex items-center">
                  <div className="bg-teal-100 p-3 rounded-full mr-4">
                    <Users className="h-6 w-6 text-teal-600" />
                  </div>
                  <div>
                    <p className="text-gray-500 text-sm">Active Students</p>
                    <p className="text-2xl font-bold">5</p>
                  </div>
                </div>
                <div className="mt-4 text-right">
                  <Link href="/students" className="text-teal-600 text-sm hover:underline">
                    View Details
                  </Link>
                </div>
              </motion.div>

              <motion.div
                className="bg-white p-4 rounded-lg shadow-sm border border-gray-100"
                whileHover={{ y: -5, boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)" }}
                transition={{ duration: 0.2 }}
              >
                <div className="flex items-center">
                  <div className="bg-blue-100 p-3 rounded-full mr-4">
                    <Calendar className="h-6 w-6 text-blue-600" />
                  </div>
                  <div>
                    <p className="text-gray-500 text-sm">Upcoming Sessions</p>
                    <p className="text-2xl font-bold">3</p>
                  </div>
                </div>
                <div className="mt-4 text-right">
                  <Link href="/schedule" className="text-teal-600 text-sm hover:underline">
                    View Details
                  </Link>
                </div>
              </motion.div>

              <motion.div
                className="bg-white p-4 rounded-lg shadow-sm border border-gray-100"
                whileHover={{ y: -5, boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)" }}
                transition={{ duration: 0.2 }}
              >
                <div className="flex items-center">
                  <div className="bg-amber-100 p-3 rounded-full mr-4">
                    <Clock className="h-6 w-6 text-amber-600" />
                  </div>
                  <div>
                    <p className="text-gray-500 text-sm">Pending Requests</p>
                    <p className="text-2xl font-bold">-</p>
                  </div>
                </div>
                <div className="mt-4 text-right">
                  <Link href="/requests" className="text-teal-600 text-sm hover:underline">
                    View Details
                  </Link>
                </div>
              </motion.div>
            </div>
          </motion.div>

          <motion.div
            className="mb-8"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
          >
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
              <h2 className="text-xl font-semibold mb-2 sm:mb-0">Your Upcoming Sessions</h2>
              <Link href="/schedule" className="text-teal-600 text-sm hover:underline">
                Manage Availability
              </Link>
            </div>

            <div className="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
              <div className="flex justify-between items-center mb-4 flex-wrap">
                <div className="flex space-x-2 sm:space-x-4 overflow-x-auto hide-scrollbar pb-2 w-full sm:w-auto">
                  {months.map((month, index) => (
                    <button
                      key={month}
                      className={`px-2 py-1 text-sm whitespace-nowrap ${activeMonth === month ? "text-teal-600 font-medium" : "text-gray-500"}`}
                      onClick={() => setActiveMonth(month)}
                    >
                      {month}
                    </button>
                  ))}
                </div>
                <div className="text-sm font-medium mt-2 sm:mt-0">
                  {activeMonth} {activeYear}
                </div>
              </div>

              <div className="grid grid-cols-7 gap-1 mb-4 text-center">
                <div className="text-xs sm:text-sm text-gray-500 py-1">Sun</div>
                <div className="text-xs sm:text-sm text-gray-500 py-1">Mon</div>
                <div className="text-xs sm:text-sm text-gray-500 py-1">Tue</div>
                <div className="text-xs sm:text-sm text-gray-500 py-1">Wed</div>
                <div className="text-xs sm:text-sm text-gray-500 py-1">Thu</div>
                <div className="text-xs sm:text-sm text-gray-500 py-1">Fri</div>
                <div className="text-xs sm:text-sm text-gray-500 py-1">Sat</div>
              </div>

              <div className="grid grid-cols-7 gap-1">
                {/* Empty cells for days before the 1st */}
                {[...Array(5)].map((_, i) => (
                  <div key={`empty-${i}`} className="h-16 sm:h-24 border border-gray-100 rounded-md p-1"></div>
                ))}

                {/* Calendar days */}
                {[...Array(31)].map((_, i) => {
                  const day = i + 1
                  const hasSession = upcomingSessions.some((session) => session.day === day)

                  return (
                    <div
                      key={`day-${day}`}
                      className={`h-16 sm:h-24 border border-gray-100 rounded-md p-1 ${hasSession ? "bg-teal-50" : ""}`}
                    >
                      <div className="text-right text-xs sm:text-sm font-medium mb-1">{day}</div>
                      {upcomingSessions
                        .filter((session) => session.day === day)
                        .map((session) => (
                          <div
                            key={session.id}
                            className="bg-white p-1 text-xs rounded border border-teal-200 mb-1 truncate"
                          >
                            <div className="font-medium truncate">{session.studentName}</div>
                            <div className="text-gray-500 hidden sm:block">{session.time}</div>
                          </div>
                        ))}
                    </div>
                  )
                })}
              </div>
            </div>
          </motion.div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <motion.div
              className="lg:col-span-2"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.3 }}
            >
              <h2 className="text-xl font-semibold mb-4">Recommended Students For You</h2>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {recommendedStudents.map((student, index) => (
                  <motion.div
                    key={student.id}
                    className="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.3, delay: 0.1 * index }}
                    whileHover={{ y: -5, boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)" }}
                  >
                    <div className="p-4">
                      <div className="flex items-center mb-4">
                        <div className="relative h-12 w-12 rounded-full overflow-hidden mr-3">
                          <Image
                            src={student.image || "/placeholder.svg"}
                            alt={student.name}
                            fill
                            className="object-cover"
                          />
                        </div>
                        <div>
                          <h3 className="font-medium">{student.name}</h3>
                          <p className="text-sm text-gray-500">Student of {student.subject}</p>
                        </div>
                      </div>

                      <div className="grid grid-cols-2 gap-2 mb-4">
                        <div>
                          <p className="text-xs text-gray-500">Day & Date</p>
                          <p className="text-sm font-medium">{student.day}</p>
                        </div>
                        <div>
                          <p className="text-xs text-gray-500">Timing</p>
                          <p className="text-sm font-medium">{student.time}</p>
                        </div>
                      </div>

                      <div className="flex justify-between items-center border-t border-gray-100 p-3">
                        <div>
                          <span className="text-sm font-bold text-teal-600">{student.rate}</span>
                          <span className="text-xs text-gray-500"> / hour</span>
                        </div>
                        <div className="flex space-x-2">
                          <Link
                            href={`/student/${student.id}`}
                            className="text-xs px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                          >
                            View Profile
                          </Link>
                          <Link
                            href={`/connect/${student.id}`}
                            className="text-xs px-3 py-1 bg-teal-600 text-white rounded-md hover:bg-teal-700 transition-colors"
                          >
                            Connect
                          </Link>
                        </div>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </div>
            </motion.div>

            <motion.div
              className="space-y-6"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.4 }}
            >
              <StudentRequests />
              <RecentMessages />
            </motion.div>
          </div>
        </div>
      </main>

      <AnimatePresence>
        {showVerificationModal && (
          <VerificationSuccessModal name={user?.name || "Abdullah"} onClose={() => setShowVerificationModal(false)} />
        )}
      </AnimatePresence>
    </div>
  )
}

