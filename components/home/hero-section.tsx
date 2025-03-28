"use client"

import { useEffect, useRef } from "react"
import Image from "next/image"
import Link from "next/link"
import { useAnimation, useInView } from "framer-motion"

export function HeroSection() {
  const controls = useAnimation()
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true })

  useEffect(() => {
    if (isInView) {
      controls.start("visible")
    }
  }, [controls, isInView])

  return (
    <section ref={ref} className="relative w-full py-16 px-6 bg-[#FFF8E7] overflow-hidden">
      {/* Background pattern */}
      <div className="absolute inset-0 z-0">
        <Image
          src="/images/background-pattern.jpeg"
          alt="Background Pattern"
          fill
          className="object-cover opacity-30"
        />
      </div>

      <div className="max-w-7xl mx-auto relative z-10">
        <div className="flex flex-col items-center text-center mb-12">
          <h1 className="text-4xl md:text-6xl font-bold mb-6">
            Connect with <span className="bg-[#E3F4E7] px-2 rounded-md">Expert Quran Teachers</span>
            <br />
            Anytime, Anywhere!
          </h1>
          <p className="text-lg text-gray-600 max-w-3xl mx-auto mb-8">
            Find expert Quran tutors for kids and adults. Learn at your own pace, anytime, anywhere.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              href="/find-teacher"
              className="bg-[#0D9488] text-white px-6 py-3 rounded-full text-lg font-medium hover:bg-teal-700 transition-colors"
            >
              Find a Teacher
            </Link>
            <Link
              href="/register/teacher"
              className="border border-[#0D9488] text-[#0D9488] px-6 py-3 rounded-full text-lg font-medium hover:bg-teal-50 transition-colors"
            >
              Become a Teacher
            </Link>
          </div>
        </div>
      </div>

      {/* Teacher Image */}
      <div className="relative max-w-7xl mx-auto" style={{ marginBottom: "-40px" }}>
        <div className="flex justify-center">
          <div className="relative w-full max-w-2xl h-[500px]">
            <Image src="/images/teacher-with-laptop.png" alt="Quran Teacher" fill className="object-contain" priority />
          </div>
        </div>
      </div>

      {/* Features section */}
      <div className="bg-[#0D6A61] rounded-3xl py-8 px-4 md:px-10 max-w-6xl mx-auto relative z-10">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 text-white">
          <div className="flex items-start gap-4 md:border-r md:border-white/20 px-4">
            <div className="bg-white/10 p-3 rounded-full">
              <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
            </div>
            <div>
              <h3 className="text-lg font-semibold">Verified Tutors</h3>
              <p className="text-sm text-white/80">Learn from certified and experienced Quran teachers.</p>
            </div>
          </div>

          <div className="flex items-start gap-4 md:border-r md:border-white/20 px-4">
            <div className="bg-white/10 p-3 rounded-full">
              <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
            </div>
            <div>
              <h3 className="text-lg font-semibold">24/7 Availability</h3>
              <p className="text-sm text-white/80">Schedule lessons at your convenience, anytime, anywhere.</p>
            </div>
          </div>

          <div className="flex items-start gap-4 px-4">
            <div className="bg-white/10 p-3 rounded-full">
              <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
            </div>
            <div>
              <h3 className="text-lg font-semibold">Tajweed, Hifz & More</h3>
              <p className="text-sm text-white/80">Master Quran recitation, memorization, and Islamic studies.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}

