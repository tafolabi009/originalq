"use client"

import { useRef } from "react"
import Link from "next/link"
import Image from "next/image"
import { useInView } from "framer-motion"

export function BecomeTeacherSection() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, amount: 0.2 })

  return (
    <section ref={ref} className="py-16 px-6 bg-[#0D6A61] text-white">
      <div className="max-w-7xl mx-auto">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div className="relative">
            <Image
              src="/images/teacher-with-laptop.png"
              alt="Quran Teacher"
              width={600}
              height={500}
              className="z-10 rounded-lg"
            />
          </div>

          <div>
            <h2 className="text-3xl md:text-4xl font-bold mb-6">
              Become a <br />
              <span className="text-white">IqraPath Teacher</span>
            </h2>
            <p className="text-lg mb-8">
              Earn money by sharing your expertise with students. Sign up today and start teaching online with IqraPath!
            </p>

            <div className="space-y-4 mb-8">
              <div className="flex items-center gap-2 bg-white/10 p-3 rounded-lg">
                <div className="bg-white/20 p-2 rounded-full">
                  <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M17 20H5C3.89543 20 3 19.1046 3 18V6C3 4.89543 3.89543 4 5 4H19C20.1046 4 21 4.89543 21 6V14"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                    <path
                      d="M3 8H21M16 19L18 21L22 17"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                </div>
                <span>Discover new students</span>
              </div>

              <div className="flex items-center gap-2 bg-white/10 p-3 rounded-lg">
                <div className="bg-white/20 p-2 rounded-full">
                  <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M16 8V16M12 11V16M8 14V16M6 20H18C19.1046 20 20 19.1046 20 18V6C20 4.89543 19.1046 4 18 4H6C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20Z"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                </div>
                <span>Expand your Business</span>
              </div>

              <div className="flex items-center gap-2 bg-white/10 p-3 rounded-lg">
                <div className="bg-white/20 p-2 rounded-full">
                  <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M9 12H15M9 16H15M17 21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3H12.5858C12.851 3 13.1054 3.10536 13.2929 3.29289L18.7071 8.70711C18.8946 8.89464 19 9.149 19 9.41421V19C19 20.1046 18.1046 21 17 21Z"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                </div>
                <span>Receive payments securely</span>
              </div>
            </div>

            <Link
              href="/register/teacher"
              className="bg-white text-[#0D6A61] px-6 py-3 rounded-full text-lg font-medium inline-block hover:bg-gray-100 transition-colors"
            >
              Become a Teacher
            </Link>
          </div>
        </div>
      </div>
    </section>
  )
}

