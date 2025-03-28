"use client"

import { useRef } from "react"
import Link from "next/link"
import Image from "next/image"
import { useInView } from "framer-motion"

export function CTASection() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, amount: 0.3 })

  return (
    <section ref={ref} className="py-16 px-6 bg-white relative">
      <div className="absolute inset-0 z-0">
        <Image
          src="/images/background-pattern.jpeg"
          alt="Background Pattern"
          fill
          className="object-cover opacity-10"
        />
      </div>

      <div className="max-w-7xl mx-auto relative z-10">
        <div className="bg-gradient-to-r from-[#0D6A61] to-[#0D9488] rounded-3xl overflow-hidden">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="p-12 flex flex-col justify-center">
              <h2 className="text-3xl md:text-4xl font-bold text-white mb-6">
                Start Your Quran Learning Journey Today!
              </h2>
              <Link
                href="/find-teacher"
                className="bg-white text-[#0D6A61] px-6 py-3 rounded-full text-lg font-medium inline-block hover:bg-gray-100 transition-colors w-fit"
              >
                Find A Teacher
              </Link>
            </div>

            <div className="relative h-80 md:h-auto">
              <Image src="/images/female-teacher.png" alt="Quran Student" fill className="object-cover object-center" />
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}

