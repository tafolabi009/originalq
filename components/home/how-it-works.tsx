"use client"

import { useRef } from "react"
import { useInView } from "framer-motion"

export function HowItWorksSection() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, amount: 0.2 })

  return (
    <section ref={ref} className="py-16 px-6 bg-[#FFF8E7]">
      <div className="max-w-7xl mx-auto">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold text-[#0D6A61] mb-4">How It Works</h2>
          <p className="text-lg text-gray-600 max-w-3xl mx-auto">
            Finding the perfect Quran tutor has never been easier. Our platform is designed to match students with
            certified and experienced teachers, ensuring a personalized and effective learning experience. Whether
            you're a beginner, memorizing the Quran, or improving your Tajweed, our step-by-step process makes it simple
            to start your journey.
          </p>
        </div>

        <div className="relative mt-20">
          {/* Connection lines */}
          <div className="absolute top-1/4 left-0 right-0 h-0.5 bg-teal-200 hidden md:block"></div>

          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            {/* Step 1 */}
            <div className="flex flex-col items-center text-center relative">
              <div className="w-24 h-24 rounded-full bg-gradient-to-br from-teal-500 to-teal-300 flex items-center justify-center text-[#0D6A61] text-3xl font-bold mb-6 relative z-10">
                01
              </div>
              <div className="flex flex-col items-center">
                <div className="flex items-center mb-2">
                  <svg
                    className="w-5 h-5 text-[#0D6A61] mr-2"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                    <path
                      d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                  <h3 className="text-xl font-bold text-[#0D6A61]">Sign Up</h3>
                </div>
                <p className="text-gray-600">Create your free account in minutes.</p>
              </div>
            </div>

            {/* Step 2 */}
            <div className="flex flex-col items-center text-center relative">
              <div className="w-24 h-24 rounded-full bg-gradient-to-br from-teal-500 to-teal-300 flex items-center justify-center text-[#0D6A61] text-3xl font-bold mb-6 relative z-10">
                02
              </div>
              <div className="flex flex-col items-center">
                <div className="flex items-center mb-2">
                  <svg
                    className="w-5 h-5 text-[#0D6A61] mr-2"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                  <h3 className="text-xl font-bold text-[#0D6A61]">Find a Teacher</h3>
                </div>
                <p className="text-gray-600">Browse our certified tutors & choose the best fit.</p>
              </div>
            </div>

            {/* Step 3 */}
            <div className="flex flex-col items-center text-center relative">
              <div className="w-24 h-24 rounded-full bg-gradient-to-br from-teal-500 to-teal-300 flex items-center justify-center text-[#0D6A61] text-3xl font-bold mb-6 relative z-10">
                03
              </div>
              <div className="flex flex-col items-center">
                <div className="flex items-center mb-2">
                  <svg
                    className="w-5 h-5 text-[#0D6A61] mr-2"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                  <h3 className="text-xl font-bold text-[#0D6A61]">Book a Class</h3>
                </div>
                <p className="text-gray-600">Select a time that suits you.</p>
              </div>
            </div>

            {/* Step 4 */}
            <div className="flex flex-col items-center text-center relative">
              <div className="w-24 h-24 rounded-full bg-gradient-to-br from-teal-500 to-teal-300 flex items-center justify-center text-[#0D6A61] text-3xl font-bold mb-6 relative z-10">
                04
              </div>
              <div className="flex flex-col items-center">
                <div className="flex items-center mb-2">
                  <svg
                    className="w-5 h-5 text-[#0D6A61] mr-2"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                  <h3 className="text-xl font-bold text-[#0D6A61]">Start Learning</h3>
                </div>
                <p className="text-gray-600">Enjoy interactive Quran lessons online.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}

