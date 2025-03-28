"use client"

import { useRef, useState } from "react"
import Image from "next/image"
import Link from "next/link"
import { useInView } from "framer-motion"
import { ChevronLeft, ChevronRight } from "lucide-react"

export function TeachersSection() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, amount: 0.2 })
  const [activeIndex, setActiveIndex] = useState(0)

  const teachers = [
    {
      name: "Ustadh Ahmad Ali",
      specialty: "Tajweed & Hifz",
      rating: 4.9,
      reviews: 120,
      experience: "7+ Years Exp.",
      image: "/images/teacher-with-laptop.png",
    },
    {
      name: "Ustadh Ahmad Ali",
      specialty: "Tajweed & Hifz",
      rating: 4.9,
      reviews: 120,
      experience: "7+ Years Exp.",
      image: "/placeholder.svg?height=300&width=300",
    },
    {
      name: "Ustadh Ahmad Ali",
      specialty: "Tajweed & Hifz",
      rating: 4.9,
      reviews: 120,
      experience: "7+ Years Exp.",
      image: "/placeholder.svg?height=300&width=300",
    },
    {
      name: "Ustadh Ahmad Ali",
      specialty: "Tajweed & Hifz",
      rating: 4.9,
      reviews: 120,
      experience: "7+ Years Exp.",
      image: "/placeholder.svg?height=300&width=300",
    },
  ]

  const nextTeacher = () => {
    setActiveIndex((prev) => (prev + 1) % teachers.length)
  }

  const prevTeacher = () => {
    setActiveIndex((prev) => (prev - 1 + teachers.length) % teachers.length)
  }

  return (
    <section ref={ref} className="py-16 px-6 bg-white relative overflow-hidden">
      {/* Background pattern */}
      <div className="absolute inset-0 z-0">
        <Image
          src="/images/background-pattern.jpeg"
          alt="Background Pattern"
          fill
          className="object-cover opacity-10"
        />
      </div>

      <div className="max-w-7xl mx-auto relative z-10">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold text-[#0D6A61] mb-4">Meet Our Certified Quran Teachers</h2>
        </div>

        <div className="flex justify-center">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
            {teachers.map((teacher, index) => (
              <div key={index} className="bg-white rounded-t-full shadow-sm border border-gray-100 overflow-hidden">
                <div className="bg-gray-100 rounded-t-full p-4 flex justify-center">
                  <div className="relative h-40 w-40 rounded-full overflow-hidden">
                    <Image src={teacher.image || "/placeholder.svg"} alt={teacher.name} fill className="object-cover" />
                  </div>
                </div>
                <div className="p-4 text-center">
                  <h3 className="text-xl font-bold mb-1">{teacher.name}</h3>
                  <p className="text-gray-600 mb-2">{teacher.specialty}</p>
                  <div className="flex items-center justify-center gap-1 mb-2">
                    <div className="flex">
                      {[...Array(5)].map((_, i) => (
                        <svg key={i} className="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 24 24">
                          <path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z" />
                        </svg>
                      ))}
                    </div>
                    <span className="text-sm text-gray-600 ml-1">
                      {teacher.rating} ({teacher.reviews} Reviews)
                    </span>
                  </div>
                  <p className="text-sm text-gray-500 mb-4">{teacher.experience}</p>

                  <div className="bg-[#FFF8E7] p-4 rounded-lg flex justify-between">
                    <Link
                      href={`/teacher/${index}`}
                      className="px-4 py-2 text-sm text-[#0D6A61] border border-[#0D6A61] rounded-md hover:bg-teal-50"
                    >
                      View Profile
                    </Link>
                    <Link
                      href={`/book/${index}`}
                      className="px-4 py-2 text-sm text-white bg-[#0D6A61] rounded-md hover:bg-teal-700"
                    >
                      Book Now
                    </Link>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="flex justify-center mt-8 gap-4">
          <button
            onClick={prevTeacher}
            className="p-2 rounded-full bg-[#0D6A61] text-white hover:bg-teal-700 transition-colors"
            aria-label="Previous teacher"
          >
            <ChevronLeft size={20} />
          </button>
          <button
            onClick={nextTeacher}
            className="p-2 rounded-full bg-[#0D6A61] text-white hover:bg-teal-700 transition-colors"
            aria-label="Next teacher"
          >
            <ChevronRight size={20} />
          </button>
        </div>
      </div>
    </section>
  )
}

